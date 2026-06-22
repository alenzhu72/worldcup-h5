<?php
// Copy this file to config.php on the server and fill in your real API key.
// Keep config.php on the server only. Do not share it publicly.

return [
    // Recommended provider: API-FOOTBALL / API-SPORTS.
    // Docs: https://www.api-football.com/documentation-v3
    'provider' => 'api-football',
    'api_key' => 'PUT_YOUR_API_FOOTBALL_KEY_HERE',

    // Confirm the World Cup league id in your API dashboard/docs before enabling.
    'league_id' => 1,
    'season' => 2026,
    'timezone' => 'Asia/Singapore',

    // Optional: if you want to trigger update_scores.php via browser/URL.
    // CLI scheduled tasks do not need this token.
    'web_token' => 'change-this-long-random-token',
];
