<?php
// DelegAI — Trial/Lead Capture API
// InfinityFree PHP backend
// POST /api/trial.php

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
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$plan = $input['plan'] ?? 'max';
$message = trim($input['message'] ?? '');
$phone = trim($input['phone'] ?? '');

if (empty($name) || empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Imię i email są wymagane.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nieprawidłowy email.']);
    exit;
}

// Save lead to local file
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$leadsFile = $dataDir . '/leads.json';
$leads = [];
if (file_exists($leadsFile)) {
    $leads = json_decode(file_get_contents($leadsFile), true) ?? [];
}

$lead = [
    'name' => $name,
    'email' => $email,
    'plan' => $plan,
    'phone' => $phone,
    'message' => $message,
    'created_at' => date('c'),
    'source' => 'landing_page'
];
$leads[] = $lead;
file_put_contents($leadsFile, json_encode($leads, JSON_PRETTY_PRINT));

// Send Telegram alert
$TELEGRAM_BOT_TOKEN = '8608237257:AAESg8VRcMPH7PonIy9fx65KOjBPwnbfwfU';
$TELEGRAM_CHAT_ID = '5979739506';

$plans = ['content' => '📝 Content (249 zł)', 'automation' => '⚙️ Automation (499 zł)', 'max' => '🚀 Max (800 zł)'];
$planLabel = $plans[$plan] ?? $plan;

$telegramMsg = "🆕 <b>Nowy lead z DelegAI!</b>\n\n"
    . "👤 <b>Imię:</b> {$name}\n"
    . "📧 <b>Email:</b> {$email}\n"
    . ($phone ? "📞 <b>Telefon:</b> {$phone}\n" : "")
    . "📋 <b>Plan:</b> {$planLabel}\n"
    . ($message ? "💬 <b>Wiadomość:</b> {$message}\n" : "")
    . "---\n"
    . "⏰ <b>Data:</b> " . date('d.m.Y H:i');

$tgPayload = json_encode([
    'chat_id' => $TELEGRAM_CHAT_ID,
    'text' => $telegramMsg,
    'parse_mode' => 'HTML'
]);

$ch = curl_init("https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $tgPayload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 10
]);
curl_exec($ch);
curl_close($ch);

echo json_encode([
    'success' => true,
    'message' => 'Dziękujemy! Skontaktujemy się wkrótce.'
]);
