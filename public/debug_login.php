<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\AuthService;

$config = require __DIR__ . '/../config/database.php';
$db = new Database($config);
$pdo = $db->getConnection();

echo "<h1>Login Debug</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h2>Login-Versuch</h2>";
    echo "<p>Email: " . htmlspecialchars($email) . "</p>";
    
    try {
        $authService = new AuthService($pdo);
        
        echo "<p>✓ AuthService erstellt</p>";
        
        $user = $authService->login($email, $password);
        
        if ($user) {
            echo "<p style='color:green'>✓ Login erfolgreich!</p>";
            echo "<pre>";
            print_r($user->toArray());
            echo "</pre>";
        } else {
            echo "<p style='color:red'>✗ Login fehlgeschlagen (User = null)</p>";
            
            // Prüfe ob User existiert
            $stmt = $pdo->prepare("SELECT id, email, tenant_id, role, is_active FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userData) {
                echo "<p>User existiert in DB:</p>";
                echo "<pre>";
                print_r($userData);
                echo "</pre>";
                
                // Prüfe Passwort
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $hash = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $hash['password_hash'])) {
                    echo "<p style='color:orange'>⚠️ Passwort ist korrekt, aber Login gibt null zurück!</p>";
                } else {
                    echo "<p style='color:red'>✗ Passwort ist falsch!</p>";
                }
            } else {
                echo "<p style='color:red'>✗ User existiert nicht in DB!</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}
?>

<h2>Test Login</h2>
<form method="POST">
    <div style="margin-bottom: 10px;">
        <label>Email:</label><br>
        <input type="email" name="email" value="admin@sys-experts.de" style="width: 300px; padding: 5px;">
    </div>
    <div style="margin-bottom: 10px;">
        <label>Password:</label><br>
        <input type="password" name="password" value="admin123" style="width: 300px; padding: 5px;">
    </div>
    <button type="submit" style="padding: 10px 20px;">Login testen</button>
</form>

<hr>

<h3>Schnelltest:</h3>
<form method="POST">
    <input type="hidden" name="email" value="user@sys-experts.de">
    <input type="hidden" name="password" value="user123">
    <button type="submit" style="padding: 10px 20px;">Test: user@sys-experts.de</button>
</form>
