<?php
// DelegAI — Rejestracja użytkownika + Trial 7 dni
// Storage: data/users.json
// Pliki pomocnicze: data/trials.json

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);
$usersFile = $dataDir . '/users.json';
$trialsFile = $dataDir . '/trials.json';

function load_json($path) {
    return file_exists($path) ? json_decode(file_get_contents($path), true) ?? [] : [];
}
function save_json($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? '';

switch ($action) {
    case 'register':
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $name = trim($input['name'] ?? '');
        $plan = $input['plan'] ?? 'max';

        if (!$email || !$password || strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'Email i hasło (min 6 znaków) wymagane']);
            exit;
        }

        $users = load_json($usersFile);
        if (isset($users[$email])) {
            http_response_code(409);
            echo json_encode(['error' => 'Konto z tym emailem już istnieje']);
            exit;
        }

        $now = date('c');
        $trialEnd = date('c', strtotime('+7 days'));

        $users[$email] = [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'name' => $name,
            'plan' => $plan,
            'created_at' => $now,
            'trial_end' => $trialEnd,
            'status' => 'trial', // trial | active | expired | cancelled
            'subscription_id' => null
        ];
        save_json($usersFile, $users);

        // Log trial
        $trials = load_json($trialsFile);
        $trials[] = ['email' => $email, 'plan' => $plan, 'start' => $now, 'end' => $trialEnd, 'status' => 'active'];
        save_json($trialsFile, $trials);

        echo json_encode([
            'success' => true,
            'message' => 'Konto utworzone! Trial 7 dni aktywowany.',
            'trial_end' => $trialEnd,
            'days_left' => 7
        ]);
        break;

    case 'login':
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        $users = load_json($usersFile);
        if (!isset($users[$email]) || !password_verify($password, $users[$email]['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Nieprawidłowy email lub hasło']);
            exit;
        }

        $user = $users[$email];
        $trialEnd = new DateTime($user['trial_end']);
        $now = new DateTime();
        $daysLeft = max(0, (int)$now->diff($trialEnd)->format('%a'));
        $isExpired = $now > $trialEnd && $user['status'] === 'trial';

        if ($isExpired) {
            $users[$email]['status'] = 'expired';
            save_json($usersFile, $users);
        }

        echo json_encode([
            'success' => true,
            'email' => $email,
            'name' => $user['name'],
            'plan' => $user['plan'],
            'status' => $users[$email]['status'],
            'trial_end' => $user['trial_end'],
            'days_left' => $daysLeft,
            'is_expired' => $isExpired,
            'plan_info' => [
                'content' => ['name' => 'Content', 'price' => 249],
                'automation' => ['name' => 'Automation', 'price' => 499],
                'max' => ['name' => 'Max', 'price' => 800],
            ][$user['plan']] ?? ['name' => 'Nieznany', 'price' => 0]
        ]);
        break;

    case 'status':
        $email = trim($input['email'] ?? '');
        $users = load_json($usersFile);
        if (!isset($users[$email])) {
            http_response_code(404);
            echo json_encode(['error' => 'Nie znaleziono konta']);
            exit;
        }
        $user = $users[$email];
        $trialEnd = new DateTime($user['trial_end']);
        $now = new DateTime();
        $daysLeft = max(0, (int)$now->diff($trialEnd)->format('%a'));
        $isExpired = $now > $trialEnd && $user['status'] === 'trial';
        echo json_encode([
            'success' => true,
            'email' => $email,
            'name' => $user['name'],
            'plan' => $user['plan'],
            'plan_info' => [
                'content' => ['name' => 'Content', 'price' => 249],
                'automation' => ['name' => 'Automation', 'price' => 499],
                'max' => ['name' => 'Max', 'price' => 800],
            ][$user['plan']] ?? ['name' => 'Nieznany', 'price' => 0],
            'status' => $user['status'],
            'trial_end' => $user['trial_end'],
            'days_left' => $daysLeft,
            'is_expired' => $isExpired
        ]);
        break;

    case 'usage':
        // Zwraca statystyki użycia dla zalogowanego usera
        $email = trim($input['email'] ?? '');
        $users = load_json($usersFile);
        if (!isset($users[$email])) {
            http_response_code(404);
            echo json_encode(['error' => 'Nie znaleziono konta']);
            exit;
        }
        echo json_encode([
            'success' => true,
            'email' => $email,
            'stats' => [
                'chat_messages_used' => $users[$email]['stats']['chat_messages'] ?? 0,
                'chat_messages_limit' => $users[$email]['plan'] === 'max' ? 999999 : 2000,
                'seo_articles_used' => $users[$email]['stats']['seo_articles'] ?? 0,
                'seo_articles_limit' => $users[$email]['plan'] === 'content' || $users[$email]['plan'] === 'max' ? 15 : 0,
                'leads_captured' => $users[$email]['stats']['leads'] ?? 0,
                'widget_active' => $users[$email]['stats']['widget_active'] ?? false,
            ]
        ]);
        break;

    case 'update_settings':
        $email = trim($input['email'] ?? '');
        $users = load_json($usersFile);
        if (!isset($users[$email])) {
            http_response_code(404);
            echo json_encode(['error' => 'Nie znaleziono konta']);
            exit;
        }
        if (!empty($input['name'])) $users[$email]['name'] = trim($input['name']);
        if (!empty($input['company'])) $users[$email]['company'] = trim($input['company']);
        if (!empty($input['phone'])) $users[$email]['phone'] = trim($input['phone']);
        if (!empty($input['new_password'])) {
            if (strlen($input['new_password']) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'Hasło musi mieć min 6 znaków']);
                exit;
            }
            $users[$email]['password'] = password_hash($input['new_password'], PASSWORD_BCRYPT);
        }
        save_json($usersFile, $users);
        echo json_encode(['success' => true, 'message' => 'Ustawienia zapisane']);
        break;

    case 'change_plan':
        $email = trim($input['email'] ?? '');
        $newPlan = $input['plan'] ?? '';
        $users = load_json($usersFile);
        if (!isset($users[$email])) {
            http_response_code(404);
            echo json_encode(['error' => 'Nie znaleziono konta']);
            exit;
        }
        $validPlans = ['content', 'automation', 'max'];
        if (!in_array($newPlan, $validPlans)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nieprawidłowy plan']);
            exit;
        }
        $users[$email]['plan'] = $newPlan;
        save_json($usersFile, $users);
        echo json_encode(['success' => true, 'message' => 'Plan zmieniony na ' . $newPlan]);
        break;

    case 'plans':
        echo json_encode([
            'success' => true,
            'plans' => [
                ['id' => 'content', 'name' => 'Content', 'price' => 249, 'period' => 'miesiąc', 'description' => 'Treści SEO które przyciągają klientów', 'popular' => false],
                ['id' => 'automation', 'name' => 'Automation', 'price' => 499, 'period' => 'miesiąc', 'description' => 'AI który pracuje 24/7 za Ciebie', 'popular' => true],
                ['id' => 'max', 'name' => 'Max', 'price' => 800, 'period' => 'miesiąc', 'description' => 'Wszystko co mamy — bez ograniczeń', 'popular' => false],
            ],
            'trial_days' => 7
        ]);
        break;

    default:
        echo json_encode(['error' => 'Nieznana akcja', 'actions' => ['register', 'login', 'status', 'plans']]);
}
