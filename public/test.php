<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Page</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "<p>✓ Autoload OK</p>";
    
    $config = require __DIR__ . '/../config/database.php';
    echo "<p>✓ Config OK</p>";
    
    $db = new \SysExperts\BusinessManager\Database\Database($config);
    echo "<p>✓ Database OK</p>";
    
    $result = $db->fetchOne('SELECT COUNT(*) as count FROM users');
    echo "<p>✓ Query OK: " . $result['count'] . " users</p>";
    
    echo "<h2>All systems working!</h2>";
    
} catch (Throwable $e) {
    echo "<h2 style='color:red'>ERROR:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
