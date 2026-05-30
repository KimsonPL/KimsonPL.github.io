<?php
// DelegAI — Chat API (AI Sales Agent)
// InfinityFree PHP backend
// POST /api/chat.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$history = $input['history'] ?? [];

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message required']);
    exit;
}

// DeepSeek API key
$DEEPSEEK_API_KEY = 'sk-82f0707a5d2742b99cba8230e8b31b8a';

// Build messages array
$messages = [
    [
        'role' => 'system',
        'content' => 'Jesteś DelegAI — AI Sales Agent polskiej agencji automatyzacji AI.
Twoim celem jest SPRZEDAWAĆ, nie tylko odpowiadać na pytania.

OFERTA:
- Content (249 zł/mies): 15 artykułów SEO/miesiąc, social media, newsletter
- Automation (499 zł/mies): AI chatbot, lead capture, automatyzacja maili, raporty
- Max (800 zł/mies): Content + Automation + priorytetowy support

ZASADY:
1. Po 3-4 wiadomościach zbieraj dane kontaktowe (email, firma)
2. Podkreślaj 7-dniowy darmowy trial (bez karty kredytowej)
3. Mów o wdrożeniu w 48h
4. Używaj polskiego, profesjonalny ale ciepły ton
5. Jeśli klient pyta o cenę — podawaj konkretne kwoty
6. Zachęcaj do rejestracji i wypróbowania demo

WAŻNE: Nie udawaj że jesteś człowiekiem. Mów wprost że jesteś AI asystentem DelegAI.'
    ]
];

// Add conversation history
foreach ($history as $msg) {
    if (isset($msg['role']) && isset($msg['content'])) {
        $messages[] = [
            'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
            'content' => $msg['content']
        ];
    }
}

// Add current message
$messages[] = ['role' => 'user', 'content' => $message];

// Call DeepSeek API
$payload = json_encode([
    'model' => 'deepseek-v4-pro',
    'messages' => $messages,
    'temperature' => 0.7,
    'max_tokens' => 500
]);

$ch = curl_init('https://api.deepseek.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $DEEPSEEK_API_KEY
    ],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'API error: ' . $error]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode !== 200 || !isset($data['choices'][0]['message']['content'])) {
    http_response_code(500);
    echo json_encode(['error' => 'DeepSeek API failed', 'detail' => $data['error']['message'] ?? 'unknown']);
    exit;
}

$reply = $data['choices'][0]['message']['content'];

echo json_encode([
    'response' => $reply,
    'usage' => $data['usage'] ?? null
]);
