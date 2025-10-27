<?php
/**
 * Business Manager - Installation Wizard
 * 
 * Dieser Installer führt durch den Setup-Prozess:
 * 1. Server-Anforderungen prüfen
 * 2. Datenbank-Verbindung konfigurieren
 * 3. Datenbank-Schema erstellen
 * 4. Admin-User anlegen
 * 5. Grundeinstellungen setzen
 */

session_start();

// Verhindere erneute Installation wenn bereits installiert
if (file_exists(__DIR__ . '/../.env') && !isset($_GET['force'])) {
    die('Installation bereits abgeschlossen. Lösche die .env Datei oder verwende ?force=1 zum Neuinstallieren.');
}

$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

// Schritt 1: Server-Anforderungen prüfen
if ($step == 1) {
    $requirements = [
        'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO SQLite' => extension_loaded('pdo_sqlite'),
        'JSON Extension' => extension_loaded('json'),
        'MBString Extension' => extension_loaded('mbstring'),
        'Writable: /' => is_writable(__DIR__ . '/..'),
        'Writable: /database' => is_writable(__DIR__ . '/../database'),
    ];
    
    $allOk = !in_array(false, $requirements, true);
}

// Schritt 2: Datenbank-Konfiguration
if ($step == 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbType = $_POST['db_type'] ?? 'sqlite';
    
    if ($dbType === 'sqlite') {
        $dbPath = __DIR__ . '/../database/business_manager.sqlite';
        
        // Erstelle .env Datei
        $envContent = "# Business Manager Configuration\n";
        $envContent .= "APP_ENV=production\n";
        $envContent .= "APP_DEBUG=false\n";
        $envContent .= "APP_URL=" . ($_POST['app_url'] ?? 'http://localhost') . "\n\n";
        $envContent .= "DB_TYPE=sqlite\n";
        $envContent .= "DB_PATH=database/business_manager.sqlite\n\n";
        $envContent .= "SESSION_LIFETIME=7200\n";
        $envContent .= "SESSION_SECURE=" . (isset($_SERVER['HTTPS']) ? 'true' : 'false') . "\n";
        
        if (file_put_contents(__DIR__ . '/../.env', $envContent)) {
            $_SESSION['db_configured'] = true;
            header('Location: install.php?step=3');
            exit;
        } else {
            $errors[] = 'Konnte .env Datei nicht erstellen. Prüfe Schreibrechte.';
        }
    }
}

// Schritt 3: Datenbank-Schema erstellen
if ($step == 3 && isset($_SESSION['db_configured'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $pdo = new PDO('sqlite:' . __DIR__ . '/../database/business_manager.sqlite');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Führe Migrationen aus
            $migrationFiles = glob(__DIR__ . '/../database/migrations/*.sql');
            sort($migrationFiles);
            
            foreach ($migrationFiles as $file) {
                $sql = file_get_contents($file);
                $pdo->exec($sql);
            }
            
            $_SESSION['db_migrated'] = true;
            header('Location: install.php?step=4');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Datenbank-Fehler: ' . $e->getMessage();
        }
    }
}

// Schritt 4: Admin-User erstellen
if ($step == 4 && isset($_SESSION['db_migrated'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $pdo = new PDO('sqlite:' . __DIR__ . '/../database/business_manager.sqlite');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Erstelle Tenant
            $stmt = $pdo->prepare("INSERT INTO tenants (name, subdomain, is_active, created_at) VALUES (?, ?, 1, datetime('now'))");
            $stmt->execute([$_POST['company_name'], 'main']);
            $tenantId = $pdo->lastInsertId();
            
            // Erstelle Admin-User
            $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (tenant_id, email, password_hash, first_name, last_name, role, is_active, email_verified_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'admin', 1, datetime('now'), datetime('now'), datetime('now'))
            ");
            $stmt->execute([
                $tenantId,
                $_POST['email'],
                $passwordHash,
                $_POST['first_name'],
                $_POST['last_name']
            ]);
            
            $_SESSION['admin_created'] = true;
            header('Location: install.php?step=5');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Fehler beim Erstellen des Admin-Users: ' . $e->getMessage();
        }
    }
}

// Schritt 5: Abschluss
if ($step == 5 && isset($_SESSION['admin_created'])) {
    // Installation abgeschlossen
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Manager - Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        :root {
            --primary: #6366f1;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-2xl w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Business Manager</h1>
                <p class="text-gray-600">Installation & Setup</p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <?php
                    $steps = [
                        1 => 'Anforderungen',
                        2 => 'Datenbank',
                        3 => 'Schema',
                        4 => 'Admin-User',
                        5 => 'Fertig'
                    ];
                    foreach ($steps as $num => $label):
                        $active = $num == $step;
                        $completed = $num < $step;
                    ?>
                        <div class="flex-1 <?= $num < count($steps) ? 'mr-2' : '' ?>">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center <?= $active ? 'bg-indigo-600 text-white' : ($completed ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600') ?>">
                                    <?= $completed ? '✓' : $num ?>
                                </div>
                                <?php if ($num < count($steps)): ?>
                                    <div class="flex-1 h-1 mx-2 <?= $completed ? 'bg-green-500' : 'bg-gray-300' ?>"></div>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs mt-1 text-center <?= $active ? 'font-semibold' : '' ?>"><?= $label ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Content Card -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <?php if (!empty($errors)): ?>
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($step == 1): ?>
                    <!-- Schritt 1: Server-Anforderungen -->
                    <h2 class="text-2xl font-bold mb-6">Server-Anforderungen prüfen</h2>
                    
                    <div class="space-y-3 mb-6">
                        <?php foreach ($requirements as $name => $met): ?>
                            <div class="flex items-center justify-between p-3 rounded <?= $met ? 'bg-green-50' : 'bg-red-50' ?>">
                                <span class="<?= $met ? 'text-green-800' : 'text-red-800' ?>"><?= $name ?></span>
                                <span class="material-symbols-outlined <?= $met ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $met ? 'check_circle' : 'cancel' ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($allOk): ?>
                        <a href="install.php?step=2" class="block w-full bg-indigo-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-indigo-700">
                            Weiter zur Datenbank-Konfiguration
                        </a>
                    <?php else: ?>
                        <p class="text-red-600 text-center">Bitte beheben Sie die fehlenden Anforderungen und laden Sie die Seite neu.</p>
                    <?php endif; ?>

                <?php elseif ($step == 2): ?>
                    <!-- Schritt 2: Datenbank-Konfiguration -->
                    <h2 class="text-2xl font-bold mb-6">Datenbank konfigurieren</h2>
                    
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Anwendungs-URL</label>
                            <input type="url" name="app_url" value="<?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] ?>" required class="w-full px-4 py-2 border rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Die URL unter der die Anwendung erreichbar ist</p>
                        </div>

                        <input type="hidden" name="db_type" value="sqlite">
                        
                        <div class="bg-blue-50 border border-blue-200 p-4 rounded">
                            <p class="text-sm text-blue-800">
                                <strong>Datenbank:</strong> SQLite wird verwendet (database/business_manager.sqlite)
                            </p>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700">
                            Konfiguration speichern
                        </button>
                    </form>

                <?php elseif ($step == 3): ?>
                    <!-- Schritt 3: Datenbank-Schema -->
                    <h2 class="text-2xl font-bold mb-6">Datenbank-Schema erstellen</h2>
                    
                    <p class="mb-6">Jetzt wird das Datenbank-Schema erstellt. Dieser Vorgang kann einige Sekunden dauern.</p>
                    
                    <form method="POST">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700">
                            Datenbank-Schema erstellen
                        </button>
                    </form>

                <?php elseif ($step == 4): ?>
                    <!-- Schritt 4: Admin-User -->
                    <h2 class="text-2xl font-bold mb-6">Administrator-Account erstellen</h2>
                    
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Firmenname</label>
                            <input type="text" name="company_name" required class="w-full px-4 py-2 border rounded-lg">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Vorname</label>
                                <input type="text" name="first_name" required class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Nachname</label>
                                <input type="text" name="last_name" required class="w-full px-4 py-2 border rounded-lg">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">E-Mail</label>
                            <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Passwort</label>
                            <input type="password" name="password" required minlength="8" class="w-full px-4 py-2 border rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Mindestens 8 Zeichen</p>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700">
                            Administrator erstellen
                        </button>
                    </form>

                <?php elseif ($step == 5): ?>
                    <!-- Schritt 5: Fertig -->
                    <div class="text-center">
                        <div class="mb-6">
                            <span class="material-symbols-outlined text-green-600" style="font-size: 80px;">check_circle</span>
                        </div>
                        
                        <h2 class="text-2xl font-bold mb-4">Installation erfolgreich!</h2>
                        
                        <p class="text-gray-600 mb-8">
                            Business Manager wurde erfolgreich installiert und ist einsatzbereit.
                        </p>

                        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded mb-6">
                            <p class="text-sm text-yellow-800">
                                <strong>Wichtig:</strong> Löschen Sie aus Sicherheitsgründen die Datei <code>public/install.php</code> oder setzen Sie die Schreibrechte auf read-only.
                            </p>
                        </div>

                        <a href="/" class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-indigo-700">
                            Zum Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-500 text-sm">
                <p>Business Manager v1.0 &copy; <?= date('Y') ?> sys-experts.de</p>
            </div>
        </div>
    </div>
</body>
</html>
