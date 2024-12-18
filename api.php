<?php
if (!isset($_GET['action'])) {
    return;
}
// Set the base directory for mail files
$mailDir = "C:\\laragon\\bin\\sendmail\\output";

// Ensure the mail directory exists
if (!is_dir($mailDir)) {
    http_response_code(500);
    echo json_encode(["error" => "Mail directory does not exist."]);
    exit;
}

// API routing
if ('mails' === $_GET['action']) {
    header('Content-Type: application/json');
    // Fetch a specific mail file
    if (isset($_GET['file'])) {
        $filePath = $mailDir . DIRECTORY_SEPARATOR . $_GET['file'];

        // Check if file exists
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo json_encode(["error" => "File not found."]);
            exit;
        }

        // Retrieve file content
        $fileContent = file_get_contents($filePath);
        $parts = preg_split("/\r?\n\r?\n/", $fileContent, 2);
        $headers = isset($parts[0]) ? $parts[0] : '';
        $body = isset($parts[1]) ? $parts[1] : '';
        
		$parsed = [];
		$headers = explode("\r\n", $headers);
		foreach ($headers as $header) {
			if (preg_match('/^([^:]+):\s*(.+)$/', $header, $matches)) {
				$parsed[$matches[1]] = $matches[2];
			}
		}

        echo json_encode(["filename" => $_GET['file'], "headers" => $parsed, "body" => $body], JSON_PRETTY_PRINT);
        exit;
    }

    // List all mail files
    $mailFiles = array_diff(scandir($mailDir), array('.', '..'));
    echo json_encode(array_values($mailFiles), JSON_PRETTY_PRINT);
    exit;
}

// Handle invalid routes or methods
http_response_code(400);
echo json_encode(["error" => "Invalid request."]);
exit;