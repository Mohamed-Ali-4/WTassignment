<?php
/**
 * Helper functions: sanitising, validation and rendering of the event pass.
 * Requires includes/config.php.
 */

declare(strict_types=1);

/** Escape a value for safe output in HTML. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Read a trimmed single-line string from POST (internal whitespace collapsed). */
function input(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? (string) preg_replace('/\s+/', ' ', trim($value)) : '';
}

function str_len(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s);
}

const NAME_PATTERN = "/^[A-Za-z][A-Za-z .'-]*$/";

/**
 * Validate a registration.
 *
 * @return array{0: array<string,mixed>, 1: array<string,string>} [clean data, errors]
 */
function validate_registration(): array
{
    $d = [
        'first_name'    => input('first_name'),
        'last_name'     => input('last_name'),
        'email'         => strtolower(input('email')),
        'phone'         => (string) preg_replace('/[\s-]/', '', input('phone')),
        'dob'           => input('dob'),
        'gender'        => input('gender'),
        'college'       => input('college'),
        'roll_no'       => strtoupper(input('roll_no')),
        'department'    => input('department'),
        'year'          => input('year'),
        'city'          => input('city'),
        'state'         => input('state'),
        'participation' => input('participation'),
        'team_name'     => input('team_name'),
        'tshirt'        => input('tshirt'),
        'food'          => input('food'),
        'accommodation' => input('accommodation'),
        'declaration'   => isset($_POST['declaration']),
        'events'        => [],
        'team_members'  => [],
        'photo'         => null,
    ];
    $errors = [];

    // ---- Participant ----
    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $key => $label) {
        if ($d[$key] === '') {
            $errors[$key] = "$label is required.";
        } elseif (str_len($d[$key]) > 50 || !preg_match(NAME_PATTERN, $d[$key])) {
            $errors[$key] = "$label may contain only letters, spaces, apostrophes and hyphens.";
        }
    }
    if (!isset($errors['first_name']) && str_len($d['first_name']) < 2) {
        $errors['first_name'] = 'Please enter at least 2 characters.';
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || str_len($d['email']) > 100) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (!preg_match('/^\+?[0-9]{10,14}$/', $d['phone'])) {
        $errors['phone'] = 'Enter a valid 10-14 digit mobile number.';
    }

    $dob = DateTime::createFromFormat('!Y-m-d', $d['dob']);
    if (!$dob || $dob->format('Y-m-d') !== $d['dob'] || $dob > new DateTime('today')) {
        $errors['dob'] = 'Please enter a valid date of birth.';
    } else {
        $age = $dob->diff(new DateTime('today'))->y;
        if ($age < 15) {
            $errors['dob'] = 'Participants must be at least 15 years old.';
        } elseif ($age > 60) {
            $errors['dob'] = 'Please enter a valid date of birth.';
        }
    }

    // ---- College ----
    if (str_len($d['college']) < 3 || str_len($d['college']) > 120) {
        $errors['college'] = 'Please enter your college name.';
    }
    if (!preg_match('/^[A-Z0-9\/-]{3,20}$/', $d['roll_no'])) {
        $errors['roll_no'] = 'Use 3-20 letters, digits, "/" or "-".';
    }
    foreach (['city' => 'City', 'state' => 'State'] as $key => $label) {
        if ($d[$key] === '' || str_len($d[$key]) > 50) {
            $errors[$key] = "$label is required.";
        }
    }

    // ---- Choice lists (only whitelisted values are accepted) ----
    $choices = [
        'gender'        => [ALLOWED_GENDERS, 'Please select your gender.'],
        'department'    => [ALLOWED_DEPARTMENTS, 'Please select your department.'],
        'year'          => [ALLOWED_YEARS, 'Please select your year of study.'],
        'participation' => [ALLOWED_PARTICIPATION, 'Please choose solo or team.'],
        'tshirt'        => [ALLOWED_TSHIRTS, 'Please choose a T-shirt size.'],
        'food'          => [ALLOWED_FOOD, 'Please choose a food preference.'],
        'accommodation' => [ALLOWED_ACCOMMODATION, 'Please choose an option.'],
    ];
    foreach ($choices as $key => [$allowed, $msg]) {
        if (!in_array($d[$key], $allowed, true)) {
            $errors[$key] = $msg;
        }
    }

    // ---- Events ----
    $events = $_POST['events'] ?? [];
    if (is_array($events)) {
        $d['events'] = array_values(array_intersect(array_keys(fest_events()), $events));
    }
    if (!$d['events']) {
        $errors['events'] = 'Please choose at least one event.';
    } elseif (count($d['events']) > MAX_EVENTS) {
        $errors['events'] = 'You can register for at most ' . MAX_EVENTS . ' events.';
    }

    // ---- Team ----
    if ($d['participation'] === 'Team') {
        if (str_len($d['team_name']) < 2 || str_len($d['team_name']) > 50 || !preg_match("/^[\\p{L}0-9 .'&_-]+$/u", $d['team_name'])) {
            $errors['team_name'] = 'Please enter a team name (2-50 characters).';
        }
        $members = $_POST['team_members'] ?? [];
        foreach (is_array($members) ? $members : [] as $m) {
            $m = is_string($m) ? (string) preg_replace('/\s+/', ' ', trim($m)) : '';
            if ($m === '') {
                continue;
            }
            if (str_len($m) > 60 || !preg_match(NAME_PATTERN, $m)) {
                $errors['team_members'] = 'Member names may contain only letters, spaces, apostrophes and hyphens.';
            }
            $d['team_members'][] = $m;
        }
        if (!$d['team_members']) {
            $errors['team_members'] = 'Add at least one team member.';
        } elseif (count($d['team_members']) > MAX_TEAM_MEMBERS) {
            $errors['team_members'] = 'A team can have at most ' . MAX_TEAM_MEMBERS . ' members besides you.';
        }
    } else {
        $d['team_name'] = '';
    }

    if (!$d['declaration']) {
        $errors['declaration'] = 'Please accept the declaration to continue.';
    }

    // ---- Optional photo: checked by real MIME type, embedded as a data URI (never written to disk) ----
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['photo'];
        if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            $errors['photo'] = 'Photo upload failed. Please try again.';
        } elseif ($f['size'] > MAX_PHOTO_BYTES) {
            $errors['photo'] = 'Photo must be smaller than 2 MB.';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                $errors['photo'] = 'Only JPG, PNG or WEBP images are allowed.';
            } else {
                $d['photo'] = 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($f['tmp_name']));
            }
        }
    }

    // The fee is always worked out here, never trusted from the browser.
    $d['fee'] = calculate_fee($d['events'], $d['accommodation'] === 'Yes');

    return [$d, $errors];
}

/** Build a readable registration number, e.g. TV27-4F9A21. */
function registration_id(): string
{
    return REG_PREFIX . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/**
 * Render the event pass as an HTML fragment.
 * $admin = true swaps the applicant-facing heading/buttons (used on the admin "view" page).
 */
function render_pass(array $d, bool $admin = false): string
{
    $d['submitted'] = (new DateTime($d['created_at'] ?? 'now'))->format('d M Y, h:i A');
    $d['full_name'] = $d['first_name'] . ' ' . $d['last_name'];
    $d['initials']  = strtoupper(substr($d['first_name'], 0, 1) . substr($d['last_name'], 0, 1));
    $d['dob_fmt']   = DateTime::createFromFormat('!Y-m-d', $d['dob'])->format('d M Y');
    $d['qr']        = FEST_NAME . ' | ' . $d['reg_id'] . ' | ' . $d['full_name'];
    $allEvents      = fest_events();

    ob_start();
    include __DIR__ . '/pass-template.php';
    return (string) ob_get_clean();
}
