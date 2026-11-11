<?php
/**
 * Helper functions: sanitising, validation and rendering of the acknowledgement.
 */

declare(strict_types=1);

const ALLOWED_GENDERS        = ['Male', 'Female', 'Other'];
const ALLOWED_MODES          = ['Full-time', 'Part-time', 'Online'];
const ALLOWED_CATEGORIES     = ['General', 'OBC', 'SC', 'ST', 'EWS'];
const ALLOWED_NATIONALITIES  = ['Indian', 'American', 'British', 'Canadian', 'Australian', 'Other'];
const ALLOWED_QUALIFICATIONS = ['12th / Higher Secondary', 'Diploma', "Bachelor's Degree", "Master's Degree"];
const ALLOWED_PROGRAMS = [
    'B.Tech Computer Science & Engineering',
    'B.Tech Electronics & Communication',
    'B.Tech Mechanical Engineering',
    'BCA',
    'B.Sc Data Science',
    'M.Tech Computer Science',
    'MCA',
    'MBA',
];
const ALLOWED_INTERESTS = [
    'Web Development', 'Artificial Intelligence', 'Cyber Security', 'Cloud Computing',
    'Data Science', 'Robotics', 'Entrepreneurship',
];
const MAX_PHOTO_BYTES = 2 * 1024 * 1024;

/** Escape a value for safe output in HTML. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Read a trimmed string from POST, collapsing internal whitespace runs on single-line fields. */
function input(string $key, bool $multiline = false): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }
    $value = trim($value);
    return $multiline ? preg_replace("/\r\n?/", "\n", $value) : preg_replace('/\s+/', ' ', $value);
}

function str_len(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s);
}

/**
 * Validate the submission.
 *
 * @return array{0: array<string,mixed>, 1: array<string,string>} [clean data, errors]
 */
function validate_application(): array
{
    $d = [
        'first_name'    => input('first_name'),
        'last_name'     => input('last_name'),
        'dob'           => input('dob'),
        'gender'        => input('gender'),
        'nationality'   => input('nationality'),
        'category'      => input('category') ?: 'General',
        'program'       => input('program'),
        'mode'          => input('mode'),
        'qualification' => input('qualification'),
        'percentage'    => input('percentage'),
        'passing_year'  => input('passing_year'),
        'institution'   => input('institution'),
        'statement'     => input('statement', true),
        'email'         => input('email'),
        'phone'         => preg_replace('/[\s-]/', '', input('phone')),
        'address'       => input('address', true),
        'city'          => input('city'),
        'state'         => input('state'),
        'pincode'       => input('pincode'),
        'declaration'   => isset($_POST['declaration']),
        'interests'     => [],
        'photo'         => null,
    ];
    $errors = [];
    $namePattern = "/^[A-Za-z][A-Za-z .'-]*$/";

    // Names
    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $key => $label) {
        if ($d[$key] === '') {
            $errors[$key] = "$label is required.";
        } elseif (str_len($d[$key]) > 50 || !preg_match($namePattern, $d[$key])) {
            $errors[$key] = "$label may contain only letters, spaces, apostrophes and hyphens.";
        }
    }
    if (!isset($errors['first_name']) && str_len($d['first_name']) < 2) {
        $errors['first_name'] = 'Please enter at least 2 characters.';
    }

    // Date of birth
    $dob = DateTime::createFromFormat('!Y-m-d', $d['dob']);
    if (!$dob || $dob->format('Y-m-d') !== $d['dob']) {
        $errors['dob'] = 'Please enter a valid date of birth.';
    } else {
        $today = new DateTime('today');
        if ($dob > $today) {
            $errors['dob'] = 'Date of birth cannot be in the future.';
        } else {
            $d['age'] = $dob->diff($today)->y;
            if ($d['age'] < 15) {
                $errors['dob'] = 'Applicants must be at least 15 years old.';
            } elseif ($d['age'] > 100) {
                $errors['dob'] = 'Please enter a valid date of birth.';
            }
        }
    }

    // Choice lists (whitelisted)
    $choices = [
        'gender'        => [ALLOWED_GENDERS, 'Please select your gender.'],
        'nationality'   => [ALLOWED_NATIONALITIES, 'Please select your nationality.'],
        'category'      => [ALLOWED_CATEGORIES, 'Please select a valid category.'],
        'program'       => [ALLOWED_PROGRAMS, 'Please choose a program.'],
        'mode'          => [ALLOWED_MODES, 'Please select a study mode.'],
        'qualification' => [ALLOWED_QUALIFICATIONS, 'Please select your qualification.'],
    ];
    foreach ($choices as $key => [$allowed, $msg]) {
        if (!in_array($d[$key], $allowed, true)) {
            $errors[$key] = $msg;
        }
    }

    // Academic
    if ($d['percentage'] === '' || !is_numeric($d['percentage'])
        || (float) $d['percentage'] < 0 || (float) $d['percentage'] > 100) {
        $errors['percentage'] = 'Enter a percentage between 0 and 100.';
    } else {
        $d['percentage'] = rtrim(rtrim(number_format((float) $d['percentage'], 2, '.', ''), '0'), '.');
    }

    $year = (int) $d['passing_year'];
    $thisYear = (int) date('Y');
    if (!ctype_digit($d['passing_year']) || $year < $thisYear - 30 || $year > $thisYear) {
        $errors['passing_year'] = 'Please select a valid year.';
    }

    if (str_len($d['institution']) < 3 || str_len($d['institution']) > 120) {
        $errors['institution'] = 'Please enter your institution name (3-120 characters).';
    }

    if (str_len($d['statement']) > 500) {
        $errors['statement'] = 'Statement must be 500 characters or fewer.';
    }

    // Interests
    $interests = $_POST['interests'] ?? [];
    if (is_array($interests)) {
        $d['interests'] = array_values(array_intersect(ALLOWED_INTERESTS, $interests));
    }

    // Contact
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || str_len($d['email']) > 100) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (!preg_match('/^\+?[0-9]{10,14}$/', $d['phone'])) {
        $errors['phone'] = 'Enter a valid 10-14 digit mobile number.';
    }
    if (str_len($d['address']) < 5 || str_len($d['address']) > 250) {
        $errors['address'] = 'Please enter your full address.';
    }
    foreach (['city' => 'City', 'state' => 'State'] as $key => $label) {
        if ($d[$key] === '' || str_len($d[$key]) > 50) {
            $errors[$key] = "$label is required.";
        }
    }
    if (!preg_match('/^[A-Za-z0-9 -]{4,10}$/', $d['pincode'])) {
        $errors['pincode'] = 'Please enter a valid PIN / ZIP code.';
    }
    if (!$d['declaration']) {
        $errors['declaration'] = 'You must accept the declaration.';
    }

    // Optional photo: validated by real MIME type, embedded as a data URI (never stored on disk)
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

    return [$d, $errors];
}

/** Build a readable application number, e.g. ADM-2026-4F9A21. */
function application_id(): string
{
    return 'ADM-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/** Render the acknowledgement card as an HTML fragment. */
function render_result(array $d): string
{
    $d['app_id']    = application_id();
    $d['submitted'] = (new DateTime())->format('d M Y, h:i A');
    $d['full_name'] = $d['first_name'] . ' ' . $d['last_name'];
    $d['initials']  = strtoupper(substr($d['first_name'], 0, 1) . substr($d['last_name'], 0, 1));
    $d['dob_fmt']   = DateTime::createFromFormat('!Y-m-d', $d['dob'])->format('d F Y');

    ob_start();
    include __DIR__ . '/result-template.php';
    return (string) ob_get_clean();
}
