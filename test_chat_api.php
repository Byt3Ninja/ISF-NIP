<?php
require_once 'vendor/autoload.php';

// Simple test script to check if the chat API works
$response = file_get_contents('http://127.0.0.1:8001/dashboard/chat/messages', false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest'
        ]
    ]
]));

echo "Response: " . $response . "\n";
?>
