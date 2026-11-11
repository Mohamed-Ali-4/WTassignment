<?php
/**
 * Fest configuration - the single source of truth for events, fees and choice lists.
 * The registration form, the server-side validation and the admin panel all read from here.
 */

declare(strict_types=1);

const FEST_NAME     = 'TechVerse 2027';
const FEST_TAGLINE  = 'National Level Technical Symposium';
const FEST_DATES    = '15 - 17 January 2027';
const FEST_START    = '2027-01-15T09:00:00+05:30';
const FEST_VENUE    = 'Main Campus, Innovation Block';
const REG_PREFIX    = 'TV27';

const MAX_EVENTS        = 3;
const MAX_TEAM_MEMBERS  = 3;   // in addition to the participant (team leader)
const ACCOMMODATION_FEE = 300; // per participant, in rupees

const ALLOWED_GENDERS     = ['Male', 'Female', 'Other'];
const ALLOWED_DEPARTMENTS = [
    'Computer Science & Engineering', 'Information Technology', 'AI & Data Science',
    'Electronics & Communication', 'Electrical & Electronics', 'Mechanical Engineering',
    'Civil Engineering', 'Other',
];
const ALLOWED_YEARS          = ['1st Year', '2nd Year', '3rd Year', '4th Year', 'Postgraduate'];
const ALLOWED_PARTICIPATION  = ['Solo', 'Team'];
const ALLOWED_TSHIRTS        = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
const ALLOWED_FOOD           = ['Veg', 'Non-Veg'];
const ALLOWED_ACCOMMODATION  = ['No', 'Yes'];
const MAX_PHOTO_BYTES        = 2 * 1024 * 1024;

/**
 * Events on offer. Keys are stored in the database.
 * icon = name of an SVG in event_icon().
 */
function fest_events(): array
{
    return [
        'hackathon'   => ['name' => '24-Hour Hackathon',      'category' => 'Coding',   'date' => '15-16 Jan, 10:00 AM', 'venue' => 'Innovation Hub',    'fee' => 400, 'icon' => 'code',    'blurb' => 'Build a working product in 24 hours.'],
        'code_sprint' => ['name' => 'Code Sprint',            'category' => 'Coding',   'date' => '15 Jan, 2:00 PM',     'venue' => 'Lab Block A',       'fee' => 150, 'icon' => 'terminal', 'blurb' => 'Competitive programming, 3 hours.'],
        'ai_ml'       => ['name' => 'AI / ML Challenge',      'category' => 'AI',       'date' => '16 Jan, 11:00 AM',    'venue' => 'Lab Block B',       'fee' => 250, 'icon' => 'brain',   'blurb' => 'Train the best model on a surprise dataset.'],
        'robo_wars'   => ['name' => 'Robo Wars',              'category' => 'Robotics', 'date' => '16 Jan, 3:00 PM',     'venue' => 'Main Arena',        'fee' => 300, 'icon' => 'robot',   'blurb' => 'Bring your bot. Last one standing wins.'],
        'web_design'  => ['name' => 'Web Design Battle',      'category' => 'Design',   'date' => '15 Jan, 11:00 AM',    'venue' => 'Lab Block C',       'fee' => 150, 'icon' => 'layout',  'blurb' => 'Design and build a site against the clock.'],
        'tech_quiz'   => ['name' => 'Tech Quiz',              'category' => 'Quiz',     'date' => '17 Jan, 10:00 AM',    'venue' => 'Seminar Hall 1',    'fee' => 100, 'icon' => 'quiz',    'blurb' => 'Three rounds of tech trivia.'],
        'paper'       => ['name' => 'Paper Presentation',     'category' => 'Research', 'date' => '17 Jan, 12:00 PM',    'venue' => 'Seminar Hall 2',    'fee' => 100, 'icon' => 'doc',     'blurb' => 'Present your research to a jury.'],
        'esports'     => ['name' => 'E-Sports Tournament',    'category' => 'Gaming',   'date' => '17 Jan, 2:00 PM',     'venue' => 'Gaming Arena',      'fee' => 200, 'icon' => 'gamepad', 'blurb' => 'Squad-based knockout tournament.'],
    ];
}

/** Small inline SVG icons for the event cards (stroke style, inherits currentColor). */
function event_icon(string $name): string
{
    $paths = [
        'code'     => '<path d="m16 18 6-6-6-6"/><path d="m8 6-6 6 6 6"/>',
        'terminal' => '<path d="m4 17 6-6-6-6"/><path d="M12 19h8"/>',
        'brain'    => '<path d="M12 5a3 3 0 1 0-5.99.14A4 4 0 0 0 4 12a4 4 0 0 0 2 3.46V17a3 3 0 0 0 6 0Z"/><path d="M12 5a3 3 0 1 1 5.99.14A4 4 0 0 1 20 12a4 4 0 0 1-2 3.46V17a3 3 0 0 1-6 0"/><path d="M12 5v14"/>',
        'robot'    => '<rect x="4" y="8" width="16" height="12" rx="2"/><path d="M12 8V4"/><circle cx="12" cy="3" r="1"/><circle cx="9" cy="14" r="1.2"/><circle cx="15" cy="14" r="1.2"/><path d="M2 13v3M22 13v3"/>',
        'layout'   => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>',
        'quiz'     => '<circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',
        'doc'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
        'gamepad'  => '<rect x="2" y="6" width="20" height="12" rx="6"/><path d="M6 12h4M8 10v4"/><circle cx="15.5" cy="11" r=".8"/><circle cx="17.5" cy="13" r=".8"/>',
    ];
    return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($paths[$name] ?? $paths['code']) . '</svg>';
}

/** Format an amount in rupees, e.g. 1250 -> "₹1,250". */
function rupees(int $amount): string
{
    return '&#8377;' . number_format($amount);
}

/** Total fee for a set of event keys (+ accommodation). Always computed on the server. */
function calculate_fee(array $eventKeys, bool $accommodation): int
{
    $events = fest_events();
    $total = 0;
    foreach ($eventKeys as $k) {
        $total += $events[$k]['fee'] ?? 0;
    }
    return $total + ($accommodation ? ACCOMMODATION_FEE : 0);
}
