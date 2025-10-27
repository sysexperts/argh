<?php
/**
 * Update Controller
 * Verwaltet System-Updates und Releases
 * 
 * @package SysExperts\BusinessManager\Updates
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Updates;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;

class UpdateController
{
    private Database $db;
    private SessionService $session;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Übersicht verfügbarer Updates
     */
    public function index(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        // Aktuelle Version
        $currentVersion = $this->getCurrentVersion();
        
        // Verfügbare Updates
        $availableUpdates = $this->db->fetchAll("
            SELECT * FROM bm_releases 
            WHERE version > ? 
            ORDER BY release_date DESC
        ", [$currentVersion]);
        
        // Installierte Updates
        $installedUpdates = $this->db->fetchAll("
            SELECT * FROM bm_releases 
            WHERE version <= ? 
            ORDER BY release_date DESC 
            LIMIT 10
        ", [$currentVersion]);
        
        // Update-Historie
        $updateHistory = $this->db->fetchAll("
            SELECT * FROM bm_tenant_versions 
            ORDER BY deployed_at DESC 
            LIMIT 20
        ");

        $navService = new \SysExperts\BusinessManager\Navigation\NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/updates', $user['role'] ?? 'user');

        $pageTitle = 'Updates';
        $title = 'System-Updates';
        
        ob_start();
        require __DIR__ . '/../../resources/views/updates/index.php';
        $content = ob_get_clean();
        
        ob_start();
        require __DIR__ . '/../../resources/views/layouts/app.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Update installieren
     */
    public function install(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        // Nur Admins dürfen Updates installieren
        if ($user['role'] !== 'admin') {
            $_SESSION['error'] = 'Nur Administratoren dürfen Updates installieren.';
            return $response->withHeader('Location', '/updates')->withStatus(302);
        }

        $version = $args['version'] ?? null;
        
        if (!$version) {
            $_SESSION['error'] = 'Keine Version angegeben.';
            return $response->withHeader('Location', '/updates')->withStatus(302);
        }

        // Hole Release-Info
        $release = $this->db->fetchOne("SELECT * FROM bm_releases WHERE version = ?", [$version]);
        
        if (!$release) {
            $_SESSION['error'] = 'Version nicht gefunden.';
            return $response->withHeader('Location', '/updates')->withStatus(302);
        }

        try {
            // Installiere Update
            $this->installUpdate($release, $user);
            
            $_SESSION['success'] = "Update auf Version {$version} erfolgreich installiert!";
            return $response->withHeader('Location', '/updates')->withStatus(302);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler beim Update: ' . $e->getMessage();
            return $response->withHeader('Location', '/updates')->withStatus(302);
        }
    }

    /**
     * Aktuelle Version ermitteln
     */
    private function getCurrentVersion(): string
    {
        // Hole letzte installierte Version
        $lastVersion = $this->db->fetchOne("
            SELECT version FROM bm_tenant_versions 
            WHERE module_code = 'core' 
            ORDER BY deployed_at DESC 
            LIMIT 1
        ");
        
        return $lastVersion['version'] ?? '1.0.0';
    }

    /**
     * Update installieren
     */
    private function installUpdate(array $release, array $user): void
    {
        // Führe Migrations aus (falls vorhanden)
        if ($release['requires_migration']) {
            $this->runMigrations($release['version']);
        }

        // Speichere Update-Info
        $this->db->insert('bm_tenant_versions', [
            'tenant_id' => 1, // TODO: Multi-Tenant Support
            'module_code' => 'core',
            'version' => $release['version'],
            'deployed_by' => $user['name'] ?? $user['email'],
            'changelog' => $release['changelog'],
            'is_security_update' => $release['is_security_update'],
        ]);

        // Update System-Version
        $this->updateSystemVersion($release['version']);
    }

    /**
     * Migrations ausführen
     */
    private function runMigrations(string $version): void
    {
        // TODO: Migrations-System implementieren
        // Suche nach Migrations-Dateien für diese Version
        // z.B. database/migrations/v{$version}/*.sql
    }

    /**
     * System-Version aktualisieren
     */
    private function updateSystemVersion(string $version): void
    {
        // Speichere Version in Config oder DB
        $configFile = __DIR__ . '/../../config/version.php';
        file_put_contents($configFile, "<?php\nreturn '{$version}';\n");
    }
}
