<?php
/**
 * App-Konfiguration
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'Business Manager',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    
    'timezone' => 'Europe/Berlin',
    'locale' => 'de',
    'fallback_locale' => 'en',
    
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
        'secure' => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'http_only' => filter_var($_ENV['SESSION_HTTP_ONLY'] ?? true, FILTER_VALIDATE_BOOLEAN),
    ],
];
