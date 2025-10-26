<?php

namespace SysExperts\BusinessManager\Mail;

/**
 * Mail Service
 * Einfacher E-Mail-Versand ohne externe Dependencies
 */
class MailService
{
    private array $config;
    private bool $testMode;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->testMode = $config['test_mode'] ?? false;
    }

    /**
     * E-Mail versenden
     * 
     * @param string $to Empfänger-E-Mail
     * @param string $subject Betreff
     * @param string $body HTML-Body
     * @param array $attachments Optional: ['path' => '/path/to/file.pdf', 'name' => 'file.pdf']
     * @return bool
     */
    public function send(string $to, string $subject, string $body, array $attachments = []): bool
    {
        if ($this->testMode) {
            return $this->logTestEmail($to, $subject, $body, $attachments);
        }

        if ($this->config['driver'] === 'smtp') {
            return $this->sendViaSMTP($to, $subject, $body, $attachments);
        }

        return $this->sendViaPHPMail($to, $subject, $body, $attachments);
    }

    /**
     * E-Mail via SMTP versenden
     */
    private function sendViaSMTP(string $to, string $subject, string $body, array $attachments = []): bool
    {
        $smtp = $this->config['smtp'];
        
        try {
            // Socket-Verbindung aufbauen
            $socket = fsockopen(
                $smtp['host'],
                $smtp['port'],
                $errno,
                $errstr,
                $smtp['timeout']
            );

            if (!$socket) {
                throw new \Exception("SMTP Connection failed: $errstr ($errno)");
            }

            // SMTP-Kommunikation
            $this->smtpCommand($socket, null, '220'); // Server greeting
            $this->smtpCommand($socket, "EHLO {$smtp['host']}", '250');

            // STARTTLS für TLS
            if ($smtp['encryption'] === 'tls') {
                $this->smtpCommand($socket, "STARTTLS", '220');
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->smtpCommand($socket, "EHLO {$smtp['host']}", '250');
            }

            // Authentifizierung
            if (!empty($smtp['username']) && !empty($smtp['password'])) {
                $this->smtpCommand($socket, "AUTH LOGIN", '334');
                $this->smtpCommand($socket, base64_encode($smtp['username']), '334');
                $this->smtpCommand($socket, base64_encode($smtp['password']), '235');
            }

            // E-Mail senden
            $from = $this->config['from']['address'];
            $this->smtpCommand($socket, "MAIL FROM: <$from>", '250');
            $this->smtpCommand($socket, "RCPT TO: <$to>", '250');
            $this->smtpCommand($socket, "DATA", '354');

            // E-Mail-Header und Body
            $message = $this->buildMessage($to, $subject, $body, $attachments);
            $this->smtpCommand($socket, $message . "\r\n.", '250');

            // Verbindung schließen
            $this->smtpCommand($socket, "QUIT", '221');
            fclose($socket);

            return true;

        } catch (\Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * E-Mail via PHP mail() versenden
     */
    private function sendViaPHPMail(string $to, string $subject, string $body, array $attachments = []): bool
    {
        $from = $this->config['from']['address'];
        $fromName = $this->config['from']['name'];

        $headers = [
            "From: $fromName <$from>",
            "Reply-To: $from",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "X-Mailer: PHP/" . phpversion()
        ];

        // TODO: Attachments für mail() implementieren (komplexer)
        
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * SMTP-Kommando senden und Antwort prüfen
     */
    private function smtpCommand($socket, ?string $command, string $expectedCode): void
    {
        if ($command !== null) {
            fwrite($socket, $command . "\r\n");
        }

        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }

        if (strpos($response, $expectedCode) !== 0) {
            throw new \Exception("SMTP Error: Expected $expectedCode, got: $response");
        }
    }

    /**
     * E-Mail-Nachricht mit Attachments erstellen
     */
    private function buildMessage(string $to, string $subject, string $body, array $attachments): string
    {
        $from = $this->config['from']['address'];
        $fromName = $this->config['from']['name'];
        $boundary = md5(uniqid(time()));

        $message = "From: $fromName <$from>\r\n";
        $message .= "To: $to\r\n";
        $message .= "Subject: $subject\r\n";
        $message .= "MIME-Version: 1.0\r\n";

        if (empty($attachments)) {
            // Einfache HTML-E-Mail
            $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $message .= $body;
        } else {
            // Multipart mit Attachments
            $message .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n\r\n";
            
            // HTML-Body
            $message .= "--$boundary\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $message .= $body . "\r\n\r\n";

            // Attachments
            foreach ($attachments as $attachment) {
                if (file_exists($attachment['path'])) {
                    $content = chunk_split(base64_encode(file_get_contents($attachment['path'])));
                    $message .= "--$boundary\r\n";
                    $message .= "Content-Type: application/pdf; name=\"{$attachment['name']}\"\r\n";
                    $message .= "Content-Transfer-Encoding: base64\r\n";
                    $message .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n\r\n";
                    $message .= $content . "\r\n";
                }
            }

            $message .= "--$boundary--";
        }

        return $message;
    }

    /**
     * Test-E-Mail loggen (statt versenden)
     */
    private function logTestEmail(string $to, string $subject, string $body, array $attachments): bool
    {
        $logDir = dirname($this->config['test_log']);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $log = "\n" . str_repeat('=', 80) . "\n";
        $log .= "📧 TEST E-MAIL (nicht versendet)\n";
        $log .= "Zeit: " . date('Y-m-d H:i:s') . "\n";
        $log .= "An: $to\n";
        $log .= "Betreff: $subject\n";
        $log .= "Attachments: " . count($attachments) . "\n";
        $log .= str_repeat('-', 80) . "\n";
        $log .= strip_tags($body) . "\n";
        $log .= str_repeat('=', 80) . "\n";

        file_put_contents($this->config['test_log'], $log, FILE_APPEND);
        
        return true;
    }
}
