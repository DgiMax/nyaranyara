<?php
/**
 * Nyara Nyara Nyara — waitlist endpoint for Hostinger
 * POST: save signup to data/waitlist.csv (locked) and return live count
 * GET:  return live waitlist count only (no personal data)
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$dataDir   = __DIR__ . '/data';
$csvFile   = $dataDir . '/waitlist.csv';
$countFile = $dataDir . '/waitlist.count';

/**
 * Count data rows in the CSV (excludes header). Caller must hold a lock on $fp.
 */
function nyara_count_rows($fp) {
    $pos = ftell($fp);
    rewind($fp);
    $rows = 0;
    $isHeader = true;
    while (($row = fgetcsv($fp)) !== false) {
        if ($isHeader) {
            $isHeader = false;
            // Empty file edge case or malformed — if first cell isn't the header label, count it
            if (!isset($row[0]) || $row[0] !== 'submitted_at_utc') {
                $rows++;
            }
            continue;
        }
        if ($row === [null] || $row === false) {
            continue;
        }
        if (count($row) === 1 && ($row[0] === null || $row[0] === '')) {
            continue;
        }
        $rows++;
    }
    if ($pos !== false) {
        fseek($fp, $pos);
    }
    return $rows;
}

/**
 * Read count from cache file, or rebuild from CSV under shared lock.
 */
function nyara_get_count($csvFile, $countFile) {
    if (is_file($countFile)) {
        $raw = @file_get_contents($countFile);
        if ($raw !== false && preg_match('/^\d+$/', trim($raw))) {
            return (int) trim($raw);
        }
    }

    if (!is_file($csvFile)) {
        return 0;
    }

    $fp = @fopen($csvFile, 'rb');
    if ($fp === false) {
        return 0;
    }

    if (!flock($fp, LOCK_SH)) {
        fclose($fp);
        return 0;
    }

    $count = nyara_count_rows($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    @file_put_contents($countFile, (string) $count, LOCK_EX);
    return $count;
}

// —— GET: public count only ——
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'ok' => true,
        'count' => nyara_get_count($csvFile, $countFile),
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

// —— Config (edit if needed) ——
$notifyEmail = 'nyara@dgi-collektive.com';
$fromEmail   = 'nyara@dgi-collektive.com';
$fromName    = 'Nyara Nyara Nyara Waitlist';

// Honeypot — bots fill this; humans never see it
if (!empty($_POST['website'])) {
    echo json_encode([
        'ok' => true,
        'count' => nyara_get_count($csvFile, $countFile),
    ]);
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

if (!is_dir($dataDir) && !@mkdir($dataDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not prepare storage. Please try again later.']);
    exit;
}

$now = gmdate('c');
$ip  = $_SERVER['REMOTE_ADDR'] ?? '';
$ua  = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 180);

// Exclusive lock for the whole write + count update (safe under concurrent joins)
$fp = @fopen($csvFile, 'c+b');
if ($fp === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not save your signup. Please try again later.']);
    exit;
}

$locked = false;
for ($attempt = 0; $attempt < 8; $attempt++) {
    if (flock($fp, LOCK_EX)) {
        $locked = true;
        break;
    }
    usleep(50000); // 50ms backoff
}

if (!$locked) {
    fclose($fp);
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Server is busy. Please try again in a moment.']);
    exit;
}

clearstatcache(true, $csvFile);
$stat = fstat($fp);
$size = is_array($stat) ? (int) $stat['size'] : 0;

// Move to end for append; write header only if file is empty (checked inside the lock)
fseek($fp, 0, SEEK_END);
if ($size === 0) {
    fputcsv($fp, ['submitted_at_utc', 'contact', 'ip', 'user_agent']);
}
fputcsv($fp, [$now, $contact, $ip, $ua]);
fflush($fp);

$count = nyara_count_rows($fp);
@file_put_contents($countFile, (string) $count, LOCK_EX);

flock($fp, LOCK_UN);
fclose($fp);

// Email notification (Hostinger PHP mail — works when domain email is set up)
$subject = 'New Nyara waitlist signup';
$body    = "New waitlist signup for Nyara Nyara Nyara\n\n"
    . "Contact: {$contact}\n"
    . "Time (UTC): {$now}\n"
    . "IP: {$ip}\n"
    . "Waitlist total: {$count}\n";

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: ' . sprintf('%s <%s>', $fromName, $fromEmail),
    'Reply-To: ' . ($isEmail ? $contact : $fromEmail),
    'X-Mailer: PHP/' . phpversion(),
];

@mail($notifyEmail, $subject, $body, implode("\r\n", $headers));

echo json_encode([
    'ok' => true,
    'count' => $count,
]);
