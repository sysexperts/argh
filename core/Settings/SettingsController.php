<?php
/**
 * Settings Controller
 * 
 * @package SysExperts\BusinessManager\Settings
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Settings;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;
use SysExperts\BusinessManager\Navigation\NavigationService;

class SettingsController
{
    private Database $db;
    private SessionService $session;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Einstellungen anzeigen
     */
    public function index(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        // Hole alle Einstellungen
        $settingsRows = $this->db->fetchAll('SELECT * FROM bm_settings');
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/settings', $user['role'] ?? 'user');

        ob_start();
        require __DIR__ . '/../../resources/views/settings/index.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Einstellungen speichern
     */
    public function update(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Speichere jede Einstellung
        foreach ($data as $key => $value) {
            $existing = $this->db->fetchOne('SELECT id FROM bm_settings WHERE setting_key = ?', [$key]);
            
            if ($existing) {
                $this->db->update('bm_settings', [
                    'setting_value' => $value,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'setting_key = ?', [$key]);
            } else {
                $this->db->insert('bm_settings', [
                    'setting_key' => $key,
                    'setting_value' => $value,
                ]);
            }
        }

        $_SESSION['success'] = 'Einstellungen gespeichert';
        return $response->withHeader('Location', '/settings')->withStatus(302);
    }

    /**
     * Hole eine Einstellung
     */
    public static function get(Database $db, string $key, string $default = ''): string
    {
        $setting = $db->fetchOne('SELECT setting_value FROM bm_settings WHERE setting_key = ?', [$key]);
        return $setting ? $setting['setting_value'] : $default;
    }
}
