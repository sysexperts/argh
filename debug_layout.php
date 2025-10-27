<?php
session_start();

echo "<h1>Layout Debug</h1>";

echo "<h2>Session User:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    require __DIR__ . '/vendor/autoload.php';
    
    $config = require __DIR__ . '/config/database.php';
    $db = new \SysExperts\BusinessManager\Database\Database($config);
    $navService = new \SysExperts\BusinessManager\Navigation\NavigationService($db);
    
    $navigation = $navService->getNavigation($_SESSION['user_id'], '/partner-console', $_SESSION['user_role'] ?? null);
    
    echo "<h2>Navigation:</h2>";
    echo "<pre>";
    print_r($navigation);
    echo "</pre>";
}
