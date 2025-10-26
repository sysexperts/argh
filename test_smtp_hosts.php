<?php
/**
 * Test verschiedene Microsoft SMTP-Hosts
 */

$username = 'info@sys-experts.de';
$password = 'nthdqclhssmdslpk';

$hosts = [
    'smtp.office365.com' => 587,
    'smtp-mail.outlook.com' => 587,
    'outlook.office365.com' => 587,
];

echo "=== SMTP Host Test ===\n\n";
echo "User: $username\n\n";

foreach ($hosts as $host => $port) {
    echo "Testing $host:$port ... ";
    
    try {
        $socket = @fsockopen($host, $port, $errno, $errstr, 10);
        
        if (!$socket) {
            echo "❌ Connection failed: $errstr\n";
            continue;
        }
        
        // Read greeting
        $response = fgets($socket, 515);
        
        // Send EHLO
        fwrite($socket, "EHLO sys-experts.de\r\n");
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        
        // STARTTLS
        fwrite($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        
        if (strpos($response, '220') === 0) {
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            
            // EHLO again after TLS
            fwrite($socket, "EHLO sys-experts.de\r\n");
            $response = '';
            while ($line = fgets($socket, 515)) {
                $response .= $line;
                if (substr($line, 3, 1) === ' ') break;
            }
            
            // AUTH LOGIN
            fwrite($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            
            if (strpos($response, '334') === 0) {
                fwrite($socket, base64_encode($username) . "\r\n");
                $response = fgets($socket, 515);
                
                fwrite($socket, base64_encode($password) . "\r\n");
                $response = fgets($socket, 515);
                
                if (strpos($response, '235') === 0) {
                    echo "✅ SUCCESS! Authentication worked!\n";
                    fwrite($socket, "QUIT\r\n");
                    fclose($socket);
                    
                    echo "\n✅ WORKING HOST: $host:$port\n";
                    exit(0);
                } else {
                    echo "❌ Auth failed: " . trim($response) . "\n";
                }
            } else {
                echo "❌ AUTH LOGIN not supported\n";
            }
        } else {
            echo "❌ STARTTLS failed\n";
        }
        
        fclose($socket);
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n❌ No working host found!\n";
