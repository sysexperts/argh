<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require __DIR__ . '/../vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\AuthService;
use SysExperts\BusinessManager\Auth\SessionManager;

$config = require __DIR__ . '/../config/database.php';
$db = new Database($config);
$pdo = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h2>Login-Versuch</h2>";
    echo "<p>Email: " . htmlspecialchars($email) . "</p>";
    
    try {
        // Genau wie in routes/web.php
        $authService = new AuthService($pdo);
        $sessionManager = new SessionManager($pdo);
        
        echo "<p>✓ Services erstellt</p>";
        
        // Login
        $user = $authService->login($email, $password);
        
        if (!$user) {
            echo "<p style='color:red'>✗ Login fehlgeschlagen (user = null)</p>";
        } else {
            echo "<p style='color:green'>✓ Login erfolgreich!</p>";
            echo "<pre>";
            print_r($user->toArray());
            echo "</pre>";
            
            // Session erstellen
            echo "<p>Erstelle Session...</p>";
            $sessionId = $sessionManager->createSession($user);
            echo "<p>✓ Session erstellt: $sessionId</p>";
            
            // PHP Session setzen
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_email'] = $user->getEmail();
            $_SESSION['user_name'] = $user->getFullName();
            $_SESSION['user_role'] = $user->getRole();
            $_SESSION['authenticated'] = true;
            
            echo "<p>✓ PHP Session gesetzt</p>";
            echo "<p><a href='/dashboard'>→ Zum Dashboard</a></p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Login</title>
</head>
<body>
    <h1>Debug Login</h1>
    <form method="POST">
        <div>
            <label>Email:</label><br>
            <input type="email" name="email" value="admin@sys-experts.de" style="width: 300px; padding: 5px;">
        </div>
        <div style="margin-top: 10px;">
            <label>Password:</label><br>
            <input type="password" name="password" value="admin123" style="width: 300px; padding: 5px;">
        </div>
        <div style="margin-top: 10px;">
            <button type="submit" style="padding: 10px 20px;">Login</button>
        </div>
    </form>
</body>
</html>
