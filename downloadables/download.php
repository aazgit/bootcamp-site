<?php
/*
 * Kala-Klub Download Handler
 * Secure file download with logging and access control
 * Compatible with shared hosting environments
 */

// Configuration
$config = [
    'allowed_files' => [
        'syllabus' => [
            'filename' => 'Kala-Klub-Figma-EDU-Bootcamp-Syllabus.pdf',
            'content_type' => 'application/pdf',
            'description' => 'Complete 12-Week Curriculum Guide'
        ],
        'brochure' => [
            'filename' => 'Kala-Klub-Program-Brochure.pdf',
            'content_type' => 'application/pdf',
            'description' => 'Program Overview Brochure'
        ]
    ],
    'download_log' => 'downloads.log',
    'rate_limit_minutes' => 1
];

// Rate limiting for downloads
function checkDownloadRateLimit($minutes = 1) {
    session_start();
    $now = time();
    $limit_key = 'last_download_' . $_SERVER['REMOTE_ADDR'];
    
    if (isset($_SESSION[$limit_key])) {
        $time_diff = ($now - $_SESSION[$limit_key]) / 60;
        if ($time_diff < $minutes) {
            return false;
        }
    }
    
    $_SESSION[$limit_key] = $now;
    return true;
}

// Log download
function logDownload($file_key, $filename) {
    $log_entry = [
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        $file_key,
        $filename,
        $_SERVER['HTTP_REFERER'] ?? 'Direct'
    ];
    
    $log_line = implode(' | ', $log_entry) . "\n";
    file_put_contents('downloads.log', $log_line, FILE_APPEND | LOCK_EX);
}

// Get requested file
$file_key = $_GET['file'] ?? '';

// Validate file request
if (!$file_key || !array_key_exists($file_key, $config['allowed_files'])) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>File Not Found - Kala-Klub</title>
        <link rel="stylesheet" href="../css/main.css">
    </head>
    <body>
        <div class="container" style="padding: 4rem 1rem; text-align: center;">
            <div class="card glass-panel" style="max-width: 500px; margin: 0 auto;">
                <h1 style="color: #e74c3c;">File Not Found</h1>
                <p>The requested file could not be found. Please check the link or contact us for assistance.</p>
                <a href="../index.html" class="btn btn-primary">Return Home</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Check rate limit
if (!checkDownloadRateLimit($config['rate_limit_minutes'])) {
    http_response_code(429);
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Too Many Requests - Kala-Klub</title>
        <link rel="stylesheet" href="../css/main.css">
    </head>
    <body>
        <div class="container" style="padding: 4rem 1rem; text-align: center;">
            <div class="card glass-panel" style="max-width: 500px; margin: 0 auto;">
                <h1 style="color: #f39c12;">Please Wait</h1>
                <p>You're downloading files too quickly. Please wait a moment before trying again.</p>
                <a href="../index.html" class="btn btn-secondary">Return Home</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$file_info = $config['allowed_files'][$file_key];
$file_path = __DIR__ . '/' . $file_info['filename'];

// Check if file exists
if (!file_exists($file_path)) {
    // Generate placeholder PDF if file doesn't exist
    generatePlaceholderPDF($file_info['filename'], $file_info['description']);
    
    // Now check again
    if (!file_exists($file_path)) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>File Error - Kala-Klub</title>
            <link rel="stylesheet" href="../css/main.css">
        </head>
        <body>
            <div class="container" style="padding: 4rem 1rem; text-align: center;">
                <div class="card glass-panel" style="max-width: 500px; margin: 0 auto;">
                    <h1 style="color: #e74c3c;">File Error</h1>
                    <p>There was an error accessing the requested file. Please try again later or contact support.</p>
                    <a href="../contact.php" class="btn btn-primary">Contact Support</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Log the download
logDownload($file_key, $file_info['filename']);

// Set headers for file download
header('Content-Type: ' . $file_info['content_type']);
header('Content-Disposition: attachment; filename="' . $file_info['filename'] . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output file
readfile($file_path);
exit;

// Function to generate placeholder PDF
function generatePlaceholderPDF($filename, $description) {
    $pdf_content = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj

2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj

3 0 obj
<<
/Type /Page
/Parent 2 0 R
/Resources <<
  /Font <<
    /F1 4 0 R
  >>
>>
/MediaBox [0 0 612 792]
/Contents 5 0 R
>>
endobj

4 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Arial
>>
endobj

5 0 obj
<<
/Length 200
>>
stream
BT
/F1 24 Tf
72 720 Td
(Kala-Klub Figma EDU Bootcamp) Tj
0 -50 Td
/F1 18 Tf
(' . $description . ') Tj
0 -100 Td
/F1 12 Tf
(This document is currently being prepared.) Tj
0 -20 Td
(Please check back soon or contact us at info@kala-klub.com) Tj
0 -40 Td
(for the most up-to-date curriculum information.) Tj
0 -100 Td
(Contact: 175, Sonagiri, Bhopal, MP) Tj
0 -20 Td
(Website: https://kala-klub.com) Tj
ET
endstream
endobj

xref
0 6
0000000000 65535 f 
0000000010 00000 n 
0000000053 00000 n 
0000000110 00000 n 
0000000285 00000 n 
0000000354 00000 n 
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
609
%%EOF';

    file_put_contents(__DIR__ . '/' . $filename, $pdf_content);
}
?>