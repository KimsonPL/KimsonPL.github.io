<?php
// Debug endpoint - check what's happening
header('Content-Type: application/json');

$result = [
    'php_version' => phpversion(),
    'curl_available' => function_exists('curl_version') ? curl_version()['version'] : false,
    'deepseek_test' => false,
    'error' => null
];

// Test DeepSeek connectivity
if (function_exists('curl_version')) {
    $ch = curl_init('https://api.deepseek.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'deepseek-v4-pro',
            'messages' => [['role' => 'user', 'content' => 'Say hello']],
            'max_tokens' => 10
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer sk-82f0707a5d2742b99cba8230e8b31b8a'
        ],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $result['deepseek_test'] = $httpCode;
    $result['response_preview'] = substr($response, 0, 200);
    $result['curl_error'] = $error;
}

echo json_encode($result, JSON_PRETTY_PRINT);
