<?php
// Ensure 'action' parameter is set
if (!isset($_GET['action'])) {
    http_response_code(400);
    echo json_encode(["error" => "Action parameter is missing."]);
    exit;
}

// Logs action
if ('logs' === $_GET['action']) {
    header('Content-Type: application/json');
    $errorPath = "C:\\laragon\\tmp\\php_errors.log";

    // Check if the error log file exists
    if (file_exists($errorPath)) {
        $errorContent = file_get_contents($errorPath);
        echo json_encode(["error"=> $errorContent], JSON_PRETTY_PRINT);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error log file not found."]);
    }
    exit;
}

// Mails action
if ('mails' === $_GET['action']) {
    header('Content-Type: application/json');
    $mailDir = "C:\\laragon\\bin\\sendmail\\output";

    // Check if the mail directory exists
    if (!is_dir($mailDir)) {
        http_response_code(500);
        echo json_encode(["error" => "Mail directory does not exist."]);
        exit;
    }

    // Fetch a specific mail file
    if (isset($_GET['file'])) {
        $filePath = $mailDir . DIRECTORY_SEPARATOR . basename($_GET['file']); // Avoid directory traversal

        // Validate that the file exists
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo json_encode(["error" => "File not found."]);
            exit;
        }

        // Retrieve the file content
        $fileContent = file_get_contents($filePath);
        // Split the file into headers and body
        $parts = preg_split("/\r?\n\r?\n/", $fileContent, 2);
        $headers = isset($parts[0]) ? $parts[0] : '';
        $body = isset($parts[1]) ? $parts[1] : '';

        // Parse headers into key-value pairs
        $parsed = [];
        $headers = explode("\r\n", $headers);
        foreach ($headers as $header) {
            if (preg_match('/^([^:]+):\s*(.+)$/', $header, $matches)) {
                $parsed[$matches[1]] = $matches[2];
            }
        }

        // Return headers and body of the email in JSON format
        echo json_encode(["filename" => $_GET['file'], "headers" => $parsed, "body" => $body], JSON_PRETTY_PRINT);
        exit;
    }

    // List all mail files in the directory
    $mailFiles = array_diff(scandir($mailDir), ['.', '..']);
    echo json_encode(array_values($mailFiles), JSON_PRETTY_PRINT);
    exit;
}

// Handle invalid routes or methods
http_response_code(400);
echo json_encode(["error" => "Invalid request."]);
exit;
