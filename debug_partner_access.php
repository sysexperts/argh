<?php
session_start();

echo "<h1>Partner Console Debug</h1>";

echo "<h2>Session:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>User Check:</h2>";

if (isset($_SESSION['user_id'])) {
    $pdo = new PDO('sqlite:database/business_manager.sqlite');
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($user);
    echo "</pre>";
    
    echo "<p><strong>Email:</strong> " . ($user['email'] ?? 'N/A') . "</p>";
    echo "<p><strong>Role:</strong> " . ($user['role'] ?? 'N/A') . "</p>";
    echo "<p><strong>Is admin@sys-experts.de?</strong> " . ($user['email'] === 'admin@sys-experts.de' ? 'YES' : 'NO') . "</p>";
} else {
    echo "<p>Nicht eingeloggt!</p>";
}
