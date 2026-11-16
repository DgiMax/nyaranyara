<?php
/**
 * Nyara Nyara Nyara — waitlist endpoint for Hostinger
 * Saves each signup to data/waitlist.csv and emails the team.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

// —— Config (edit if needed) ——
$notifyEmail = 'nyara@dgi-collektive.com';
$fromEmail   = 'nyara@dgi-collektive.com';
$fromName    = 'Nyara Nyara Nyara Waitlist';
$dataDir     = __DIR__ . '/data';
$csvFile     = $dataDir . '/waitlist.csv';

// Honeypot — bots fill this; humans never see it
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true]);
    exit;
}

$contact = isset($_POST['contact']) ? trim((string) $_POST['contact']) : '';
$contact = preg_replace('/\s+/', ' ', $contact);

if ($contact === '' || mb_strlen($contact) < 5 || mb_strlen($contact) > 120) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Please enter a valid email or WhatsApp number.']);
    exit;
}

// Soft validation: email OR phone-ish value
$isEmail = (bool) filter_var($contact, FILTER_VALIDATE_EMAIL);
$isPhone = (bool) preg_match('/^\+?[0-9][0-9\s\-()]{6,}$/', $contact);

if (!$isEmail && !$isPhone) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Please enter a valid email or WhatsApp number.']);
    exit;
}

if (!is_dir($dataDir) && !mkdir($dataDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not prepare storage. Please try again later.']);
    exit;
}

$now = gmdate('c');
$ip  = $_SERVER['REMOTE_ADDR'] ?? '';
$ua  = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 180);

$writeHeader = !file_exists($csvFile) || filesize($csvFile) === 0;
$fp = fopen($csvFile, 'ab');
if ($fp === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not save your signup. Please try again later.']);
    exit;
}

if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not save your signup. Please try again later.']);
    exit;
}

if ($writeHeader) {
    fputcsv($fp, ['submitted_at_utc', 'contact', 'ip', 'user_agent']);
}
fputcsv($fp, [$now, $contact, $ip, $ua]);
flock($fp, LOCK_UN);
fclose($fp);

// Email notification (Hostinger PHP mail — works when domain email is set up)
$subject = 'New Nyara waitlist signup';
$body    = "New waitlist signup for Nyara Nyara Nyara\n\n"
    . "Contact: {$contact}\n"
    . "Time (UTC): {$now}\n"
    . "IP: {$ip}\n";

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: ' . sprintf('%s <%s>', $fromName, $fromEmail),
    'Reply-To: ' . ($isEmail ? $contact : $fromEmail),
    'X-Mailer: PHP/' . phpversion(),
];

@mail($notifyEmail, $subject, $body, implode("\r\n", $headers));

echo json_encode(['ok' => true]);
