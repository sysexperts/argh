<?php
/**
 * Update Notification Service
 * Sendet E-Mail-Benachrichtigungen über Updates
 * 
 * @package SysExperts\BusinessManager\Updates
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Updates;

use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Mail\MailService;

class UpdateNotificationService
{
    private Database $db;
    private MailService $mailService;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $mailConfig = require __DIR__ . '/../../config/mail.php';
        $this->mailService = new MailService($mailConfig);
    }

    /**
     * Benachrichtige alle Mandanten über neues Update
     */
    public function notifyNewRelease(array $release): void
    {
        // Hole alle Mandanten
        $tenants = $this->db->fetchAll("SELECT * FROM bm_tenants WHERE tenant_status = 'active'");
        
        foreach ($tenants as $tenant) {
            $this->sendUpdateNotification($tenant, $release);
        }
    }

    /**
     * Sende Update-Benachrichtigung an Mandant
     */
    private function sendUpdateNotification(array $tenant, array $release): void
    {
        $subject = $release['is_security_update'] 
            ? "🔒 Wichtiges Sicherheitsupdate verfügbar - Version {$release['version']}"
            : "📦 Neues Update verfügbar - Version {$release['version']}";

        $body = $this->getEmailBody($tenant, $release);

        try {
            $this->mailService->send(
                $tenant['contact_email'],
                $subject,
                $body
            );
        } catch (\Exception $e) {
            error_log("Failed to send update notification to {$tenant['contact_email']}: " . $e->getMessage());
        }
    }

    /**
     * Erstelle E-Mail-Body
     */
    private function getEmailBody(array $tenant, array $release): string
    {
        $updateUrl = "https://{$tenant['domain']}/updates";
        
        $typeLabels = [
            'feature' => 'Feature-Update',
            'bugfix' => 'Bugfix',
            'security' => 'Sicherheitsupdate',
            'hotfix' => 'Hotfix',
        ];
        
        $typeLabel = $typeLabels[$release['release_type']] ?? 'Update';
        
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
                .badge { display: inline-block; padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; margin: 5px 0; }
                .badge-security { background: #ef4444; color: white; }
                .badge-feature { background: #3b82f6; color: white; }
                .badge-bugfix { background: #10b981; color: white; }
                .button { display: inline-block; background: #14b8a6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                .changelog { background: white; padding: 15px; border-left: 4px solid #14b8a6; margin: 15px 0; }
                .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚀 Neues Update verfügbar!</h1>
                    <h2>Version {$release['version']}</h2>
                </div>
                <div class='content'>
                    <p>Hallo {$tenant['company_name']},</p>
                    
                    <p>Es ist ein neues Update für Ihr Business Manager System verfügbar:</p>
                    
                    <h3>
                        {$release['title']}
                        <span class='badge badge-" . strtolower($release['release_type']) . "'>$typeLabel</span>
                    </h3>
                    
                    <p>{$release['description']}</p>
        ";
        
        if ($release['is_security_update']) {
            $html .= "
                    <div class='warning'>
                        <strong>⚠️ Wichtiger Sicherheitshinweis</strong><br>
                        Dieses Update behebt Sicherheitslücken und sollte zeitnah installiert werden.
                    </div>
            ";
        }
        
        if ($release['is_breaking_change']) {
            $html .= "
                    <div class='warning'>
                        <strong>⚠️ Breaking Change</strong><br>
                        Dieses Update enthält Änderungen, die möglicherweise Anpassungen erfordern.
                        Bitte lesen Sie das Changelog sorgfältig durch.
                    </div>
            ";
        }
        
        if ($release['changelog']) {
            $html .= "
                    <div class='changelog'>
                        <strong>📋 Changelog:</strong>
                        <pre style='white-space: pre-wrap; font-family: monospace; font-size: 13px;'>{$release['changelog']}</pre>
                    </div>
            ";
        }
        
        $html .= "
                    <p style='text-align: center;'>
                        <a href='$updateUrl' class='button'>Jetzt aktualisieren</a>
                    </p>
                    
                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;'>
                    
                    <p style='font-size: 12px; color: #6b7280;'>
                        <strong>Veröffentlicht:</strong> " . date('d.m.Y', strtotime($release['release_date'])) . "<br>
                        <strong>Ihre aktuelle Version:</strong> {$tenant['installed_version']}<br>
                        <strong>Domain:</strong> {$tenant['domain']}
                    </p>
                    
                    <p style='font-size: 12px; color: #6b7280;'>
                        Bei Fragen wenden Sie sich bitte an: <a href='mailto:support@sys-experts.de'>support@sys-experts.de</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }

    /**
     * Benachrichtige über verpasste Sicherheitsupdates
     */
    public function notifyMissedSecurityUpdates(): void
    {
        // Hole Mandanten mit veralteten Versionen
        $tenants = $this->db->fetchAll("
            SELECT t.*, 
                   (SELECT COUNT(*) FROM bm_releases 
                    WHERE version > t.installed_version 
                    AND is_security_update = 1) as missed_security_updates
            FROM bm_tenants t
            WHERE t.tenant_status = 'active'
            AND missed_security_updates > 0
        ");
        
        foreach ($tenants as $tenant) {
            $this->sendSecurityWarning($tenant);
        }
    }

    /**
     * Sende Sicherheitswarnung
     */
    private function sendSecurityWarning(array $tenant): void
    {
        $subject = "⚠️ Wichtig: Sicherheitsupdates ausstehend";
        
        $html = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: #ef4444; color: white; padding: 20px; border-radius: 8px;'>
                    <h2>⚠️ Sicherheitswarnung</h2>
                </div>
                <div style='padding: 20px; background: #fef2f2; border-radius: 0 0 8px 8px;'>
                    <p>Hallo {$tenant['company_name']},</p>
                    
                    <p><strong>Ihr System hat {$tenant['missed_security_updates']} ausstehende Sicherheitsupdate(s).</strong></p>
                    
                    <p>Bitte installieren Sie diese Updates zeitnah, um die Sicherheit Ihres Systems zu gewährleisten.</p>
                    
                    <p style='text-align: center;'>
                        <a href='https://{$tenant['domain']}/updates' style='display: inline-block; background: #ef4444; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;'>
                            Jetzt Updates installieren
                        </a>
                    </p>
                    
                    <p style='font-size: 12px; color: #991b1b; margin-top: 20px;'>
                        <strong>Hinweis:</strong> Gemäß unseren Nutzungsbedingungen sind Sicherheitsupdates verpflichtend.
                        Bei wiederholter Nichtinstallation behalten wir uns vor, den Zugang zu sperren.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        try {
            $this->mailService->send($tenant['contact_email'], $subject, $html);
        } catch (\Exception $e) {
            error_log("Failed to send security warning to {$tenant['contact_email']}: " . $e->getMessage());
        }
    }
}
