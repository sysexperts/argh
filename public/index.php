<?php
/**
 * Business Manager - Entry Point
 * 
 * @package SysExperts\BusinessManager
 */

declare(strict_types=1);

// Autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap
$app = require __DIR__ . '/../bootstrap/app.php';

// Run Application
$app->run();
