<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$dataDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
$dataFile = $dataDir . DIRECTORY_SEPARATOR . 'predictions.json';

function respond(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function clean_user($value): string {
    $value = trim((string)$value);
    $value = preg_replace('/[^\p{Han}\w .-]/u', '', $value);
    return function_exists('mb_substr') ? mb_substr($value, 0, 24, 'UTF-8') : substr($value, 0, 24);
}

function clean_match_id($value): string {
    $value = trim((string)$value);
    return preg_replace('/[^A-Za-z0-9_-]/', '', $value);
}

function read_predictions(string $dataDir, string $dataFile): array {
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, "{}", LOCK_EX);
    }
    $raw = file_get_contents($dataFile);
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}

function write_predictions(string $dataFile, array $data): void {
    file_put_contents($dataFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    if ($action !== 'list') {
        respond(['ok' => false, 'error' => 'Unknown action'], 400);
    }
    respond(['ok' => true, 'predictions' => read_predictions($dataDir, $dataFile)]);
}

if ($method !== 'POST') {
    respond(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$payload = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($payload) || ($payload['action'] ?? '') !== 'savePrediction') {
    respond(['ok' => false, 'error' => 'Invalid request'], 400);
}

$user = clean_user($payload['user'] ?? '');
$matchId = clean_match_id($payload['matchId'] ?? '');
$score = $payload['score'] ?? null;

if ($user === '' || $matchId === '' || !is_array($score) || count($score) !== 2) {
    respond(['ok' => false, 'error' => 'Missing user, matchId, or score'], 400);
}

$home = filter_var($score[0], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
$away = filter_var($score[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
if ($home === false || $away === false) {
    respond(['ok' => false, 'error' => 'Invalid score'], 400);
}

$predictions = read_predictions($dataDir, $dataFile);
$predictions[$matchId] = $predictions[$matchId] ?? [];
$predictions[$matchId][$user] = [(string)$home, (string)$away];
write_predictions($dataFile, $predictions);

respond(['ok' => true, 'predictions' => $predictions]);
