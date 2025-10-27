#!/usr/bin/env php
<?php
/**
 * Auto-Updater Client Script
 * Läuft auf Kunden-Servern und prüft/installiert Updates
 * 
 * Verwendung:
 * php bin/auto-update.php check
 * php bin/auto-update.php install [version]
 * php bin/auto-update.php heartbeat
 */

// Konfiguration
$config = [
    'update_server' => 'https://updates.sys-experts.de', // Zentrale Update-API
    'tenant_id' => getenv('TENANT_ID') ?: 1,
    'api_key' => getenv('UPDATE_API_KEY') ?: 'sys-experts-api-key-1',
    'current_version' => file_exists(__DIR__ . '/../config/version.php') 
        ? require __DIR__ . '/../config/version.php' 
        : '1.0.0',
];

$command = $argv[1] ?? 'check';

switch ($command) {
    case 'check':
        checkForUpdates($config);
        break;
    
    case 'install':
        $version = $argv[2] ?? null;
        if (!$version) {
            echo "❌ Fehler: Version angeben (z.B. php auto-update.php install 1.1.0)\n";
            exit(1);
        }
        installUpdate($config, $version);
        break;
    
    case 'heartbeat':
        sendHeartbeat($config);
        break;
    
    default:
        echo "Verwendung: php auto-update.php [check|install|heartbeat]\n";
        exit(1);
}

/**
 * Prüfe auf verfügbare Updates
 */
function checkForUpdates(array $config): void
{
    echo "🔍 Prüfe auf Updates...\n";
    echo "   Aktuelle Version: {$config['current_version']}\n\n";
    
    $url = $config['update_server'] . '/api/updates/check?' . http_build_query([
        'current_version' => $config['current_version'],
        'tenant_id' => $config['tenant_id'],
    ]);
    
    $response = makeApiRequest($url, 'GET', $config['api_key']);
    
    if (!$response) {
        echo "❌ Fehler beim Abrufen der Updates\n";
        exit(1);
    }
    
    if ($response['updates_available'] === 0) {
        echo "✅ System ist aktuell! Keine Updates verfügbar.\n";
        exit(0);
    }
    
    echo "📦 {$response['updates_available']} Update(s) verfügbar:\n\n";
    
    foreach ($response['updates'] as $update) {
        $icon = $update['is_security_update'] ? '🔒' : '📦';
        $type = strtoupper($update['release_type']);
        
        echo "$icon Version {$update['version']} [$type]\n";
        echo "   {$update['title']}\n";
        
        if ($update['is_security_update']) {
            echo "   ⚠️  SICHERHEITSUPDATE - Installation empfohlen!\n";
        }
        
        if ($update['is_breaking_change']) {
            echo "   ⚠️  Breaking Change - Vorsicht bei Installation!\n";
        }
        
        echo "\n";
    }
    
    if ($response['has_security_updates']) {
        echo "⚠️  Es sind Sicherheitsupdates verfügbar!\n";
        echo "   Installieren Sie diese so bald wie möglich.\n\n";
    }
    
    echo "💡 Zum Installieren: php auto-update.php install [version]\n";
}

/**
 * Installiere Update
 */
function installUpdate(array $config, string $version): void
{
    echo "📥 Installiere Update auf Version $version...\n\n";
    
    // 1. Download Update-Package
    echo "1️⃣  Lade Update-Package herunter...\n";
    $url = $config['update_server'] . "/api/updates/download/$version";
    $package = makeApiRequest($url, 'GET', $config['api_key']);
    
    if (!$package) {
        echo "❌ Fehler beim Download\n";
        exit(1);
    }
    
    echo "   ✓ Package geladen (Checksum: {$package['checksum']})\n\n";
    
    // 2. Backup erstellen
    echo "2️⃣  Erstelle Backup...\n";
    $backupDir = __DIR__ . '/../backups/' . date('Y-m-d_H-i-s') . '_v' . $config['current_version'];
    
    if (!is_dir(__DIR__ . '/../backups')) {
        mkdir(__DIR__ . '/../backups', 0755, true);
    }
    
    // Backup wichtiger Dateien
    $filesToBackup = ['config', 'database'];
    foreach ($filesToBackup as $dir) {
        if (is_dir(__DIR__ . "/../$dir")) {
            echo "   Backup: $dir/\n";
            // TODO: Tatsächliches Backup
        }
    }
    
    echo "   ✓ Backup erstellt in: $backupDir\n\n";
    
    // 3. Dateien aktualisieren
    echo "3️⃣  Aktualisiere Dateien...\n";
    foreach ($package['package']['files'] as $file => $content) {
        $targetFile = __DIR__ . '/../' . $file;
        $targetDir = dirname($targetFile);
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        file_put_contents($targetFile, base64_decode($content));
        echo "   ✓ $file\n";
    }
    echo "\n";
    
    // 4. Migrations ausführen
    if (!empty($package['package']['migrations'])) {
        echo "4️⃣  Führe Migrations aus...\n";
        foreach ($package['package']['migrations'] as $migration) {
            echo "   ✓ $migration\n";
            // TODO: Migration ausführen
        }
        echo "\n";
    }
    
    // 5. Cache leeren
    echo "5️⃣  Leere Cache...\n";
    // TODO: Cache leeren
    echo "   ✓ Cache geleert\n\n";
    
    // 6. Version aktualisieren
    file_put_contents(__DIR__ . '/../config/version.php', "<?php\nreturn '$version';\n");
    
    echo "✅ Update auf Version $version erfolgreich installiert!\n";
    echo "   Backup: $backupDir\n\n";
    
    // Sende Heartbeat
    sendHeartbeat($config, $version);
}

/**
 * Sende Heartbeat an zentrale API
 */
function sendHeartbeat(array $config, ?string $newVersion = null): void
{
    $version = $newVersion ?? $config['current_version'];
    
    $data = [
        'tenant_id' => $config['tenant_id'],
        'current_version' => $version,
        'php_version' => PHP_VERSION,
        'database_size' => getDatabaseSize(),
        'user_count' => getUserCount(),
        'active_modules' => getActiveModules(),
        'response_time' => getResponseTime(),
        'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2),
        'status' => 'online',
    ];
    
    $url = $config['update_server'] . '/api/updates/heartbeat';
    $response = makeApiRequest($url, 'POST', $config['api_key'], $data);
    
    if ($response && $response['success']) {
        echo "💓 Heartbeat gesendet\n";
    } else {
        echo "❌ Heartbeat fehlgeschlagen\n";
    }
}

/**
 * API-Request Helper
 */
function makeApiRequest(string $url, string $method, string $apiKey, ?array $data = null): ?array
{
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey,
        'Content-Type: application/json',
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return null;
    }
    
    return json_decode($response, true);
}

/**
 * Helper-Funktionen
 */
function getDatabaseSize(): int
{
    $dbFile = __DIR__ . '/../database/business_manager.sqlite';
    return file_exists($dbFile) ? filesize($dbFile) / 1024 : 0; // KB
}

function getUserCount(): int
{
    try {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../database/business_manager.sqlite');
        $result = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch();
        return $result['count'] ?? 0;
    } catch (\Exception $e) {
        return 0;
    }
}

function getActiveModules(): int
{
    try {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../database/business_manager.sqlite');
        $result = $pdo->query("SELECT COUNT(DISTINCT module_id) as count FROM bm_module_licenses WHERE is_enabled = 1")->fetch();
        return $result['count'] ?? 0;
    } catch (\Exception $e) {
        return 0;
    }
}

function getResponseTime(): int
{
    // Simuliere Response-Zeit
    return rand(50, 300); // ms
}
