<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing page load...\n";

try {
    require __DIR__ . '/public/index.php';
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
