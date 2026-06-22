<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "CLI only." . PHP_EOL;
    exit(1);
}

if ($argc < 5) {
    echo "Usage: php set_score.php HOME AWAY HOME_SCORE AWAY_SCORE" . PHP_EOL;
    echo "Example: php set_score.php ESP CPV 0 0" . PHP_EOL;
    exit(1);
}

$home = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $argv[1]));
$away = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $argv[2]));
$homeScore = filter_var($argv[3], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
$awayScore = filter_var($argv[4], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);

if ($home === '' || $away === '' || $homeScore === false || $awayScore === false) {
    echo "Invalid team code or score." . PHP_EOL;
    exit(1);
}

$matchesFile = __DIR__ . DIRECTORY_SEPARATOR . 'matches.json';
$matches = json_decode(file_get_contents($matchesFile) ?: '[]', true);
if (!is_array($matches)) {
    echo "matches.json is invalid." . PHP_EOL;
    exit(1);
}

$updated = 0;
foreach ($matches as &$match) {
    $sameOrder = ($match['home'] ?? '') === $home && ($match['away'] ?? '') === $away;
    $reverseOrder = ($match['home'] ?? '') === $away && ($match['away'] ?? '') === $home;
    if ($sameOrder || $reverseOrder) {
        $match['done'] = true;
        $match['score'] = $sameOrder ? [$homeScore, $awayScore] : [$awayScore, $homeScore];
        $match['updatedAt'] = date(DATE_ATOM);
        $updated++;
    }
}
unset($match);

file_put_contents($matchesFile, json_encode($matches, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL, LOCK_EX);

echo "Updated {$updated} match(es)." . PHP_EOL;
