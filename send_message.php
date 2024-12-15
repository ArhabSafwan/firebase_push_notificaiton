<?php
require 'vendor/autoload.php';

// Define the path to your service account credentials JSON file
define('SERVICE_ACCOUNT_JSON', 'testnotification-ebd59-firebase-adminsdk-8sq53-1c56ceb8b1.json');

// Read and decode POST data from the request
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? '';
$title = $data['title'] ?? '';
$body = $data['body'] ?? '';
$clickAction = $data['click_action'] ?? '';

// Prepare the message payload for FCM
$message = [
    'message' => [
        'token' => $token,
        'notification' => [
            'title' => $title,
            'body' => $body,
        ],
        'webpush' => [
            'notification' => [
                'click_action' => $clickAction,
            ],
        ],
    ],
];

// Log the request data to fcm_log.txt in a structured format
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'request' => $message,
];
file_put_contents("fcm_log.txt", json_encode($logData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

try {
    // Initialize the Google Client
    $client = new Google_Client();
    $client->setApplicationName('FCM Sender');
    $client->setAuthConfig(SERVICE_ACCOUNT_JSON);
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

    // Fetch the OAuth 2.0 access token
    $accessToken = $client->fetchAccessTokenWithAssertion();
    if (isset($accessToken['error'])) {
        throw new Exception('Error fetching access token: ' . $accessToken['error_description']);
    }

    // Prepare headers for the HTTP request
    $headers = [
        'Authorization: Bearer ' . $accessToken['access_token'],
        'Content-Type: application/json',
    ];

    // Firebase Cloud Messaging v1 endpoint
    $projectId = 'testnotification-ebd59';
    $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

    // Send the POST request
    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'POST',
            'content' => json_encode($message),
        ],
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    // Handle any errors in the request
    if ($response === FALSE) {
        $error = error_get_last();
        $errorLog = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => $error['message'],
            'request_context' => $options,
        ];
        file_put_contents("fcm_log.txt", json_encode($errorLog, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        throw new Exception('FCM Send Error: ' . $error['message']);
    }

    // Log the successful response
    $responseLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'response' => json_decode($response, true),
    ];
    file_put_contents("fcm_log.txt", json_encode($responseLog, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

    // Return the response to the client
    echo json_encode(['success' => true, 'response' => json_decode($response)]);
} catch (Exception $e) {
    // Log and return the error
    $errorLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage(),
    ];
    file_put_contents("fcm_log.txt", json_encode($errorLog, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
