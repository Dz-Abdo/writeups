<?php
/**
 * /handler — Legacy endpoint 
 *
 * Expected behaviour:
 *
 *   POST /handler
 *   action=media_sync
 *   url=<remote file URL>
 *
 */

header('Content-Type: application/json');

function errorResponse(string $message): void
{
    http_response_code(400);

    echo json_encode([
        'status'  => 'error',
        'message' => $message
    ]);

    exit;
}

/**
 * SSRF protection.
 * Blocks only localhost/loopback addresses.
 */
function isBlockedHost(string $host): bool
{
    $ips = gethostbynamel($host);

    if ($ips === false) {
        return true;
    }

    foreach ($ips as $ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }

        // Block localhost only.
        if (preg_match('/^127\./', $ip)) {
            return true;
        }
    }

    return false;
}

/* -------------------------------------------------------------------------- */
/* Parameter validation                                                       */
/* -------------------------------------------------------------------------- */

$action = $_POST['action'] ?? '';
$url    = trim($_POST['url'] ?? '');

if ($action !== 'media_sync') {
    errorResponse('missing action');
}

if ($url === '') {
    errorResponse('missing parameter');
}

$parts = parse_url($url);

if (
    $parts === false ||
    empty($parts['scheme']) ||
    empty($parts['host']) ||
    !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
) {
    errorResponse('invalid url');
}

if (isBlockedHost($parts['host'])) {
    errorResponse('blocked');
}

/* -------------------------------------------------------------------------- */
/* Fetch remote file                                                          */
/* -------------------------------------------------------------------------- */

$context = stream_context_create([
    'http' => [
        'timeout'          => 10,
        'follow_location'  => 1,
        'max_redirects'    => 3,
        'ignore_errors'    => true
    ]
]);

$data = @file_get_contents($url, false, $context);

if ($data === false) {
    errorResponse('download failed');
}

/* -------------------------------------------------------------------------- */
/* Filename validation                                                        */
/* -------------------------------------------------------------------------- */

$path = $parts['path'] ?? '';
$filename = basename($path);

if ($filename === '' || $filename === '.' || $filename === '/') {
    $filename = 'upload_' . time() . '.jpg';
}

/*
 * Intentionally weak:
 * FILENAME.jpg.php passes.
 */
if (
    stripos($filename, '.jpg') === false &&
    stripos($filename, '.png') === false
) {
    errorResponse('invalid filename');
}

/* -------------------------------------------------------------------------- */
/* Magic-byte validation                                                      */
/* -------------------------------------------------------------------------- */

/*
 * Intentionally bypassable:
 * Checks only the beginning of the file.
 */

$isJpeg = str_starts_with($data, "\xFF\xD8\xFF");
$isPng  = str_starts_with($data, "\x89PNG\r\n\x1A\n");

if (!$isJpeg && !$isPng) {
    errorResponse('invalid file');
}

/* -------------------------------------------------------------------------- */
/* Save file                                                                  */
/* -------------------------------------------------------------------------- */

$uploadDir = '/var/www/html/public/uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

if (@file_put_contents($destination, $data) === false) {
    errorResponse('save failed');
}

/* -------------------------------------------------------------------------- */
/* Success                                                                    */
/* -------------------------------------------------------------------------- */

echo json_encode([
    'status' => 'ok'
]);
