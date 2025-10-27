<?php
/**
 * Update API Controller
 * Stellt Updates für Kunden-Instanzen bereit
 * 
 * @package SysExperts\BusinessManager\Updates
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Updates;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;

class UpdateApiController
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Prüfe auf verfügbare Updates
     * GET /api/updates/check?current_version=1.0.0&tenant_id=123
     */
    public function check(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $currentVersion = $params['current_version'] ?? '1.0.0';
        $tenantId = $params['tenant_id'] ?? null;
        
        // Authentifizierung via API-Key
        $apiKey = $request->getHeaderLine('X-API-Key');
        if (!$this->validateApiKey($apiKey, $tenantId)) {
            return $this->jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        // Hole verfügbare Updates
        $updates = $this->db->fetchAll("
            SELECT version, release_date, release_type, title, description, 
                   is_security_update, is_breaking_change, requires_migration
            FROM bm_releases 
            WHERE version > ? 
            ORDER BY release_date ASC
        ", [$currentVersion]);

        return $this->jsonResponse($response, [
            'current_version' => $currentVersion,
            'updates_available' => count($updates),
            'updates' => $updates,
            'has_security_updates' => !empty(array_filter($updates, fn($u) => $u['is_security_update'])),
        ]);
    }

    /**
     * Download Update-Package
     * GET /api/updates/download/{version}
     */
    public function download(Request $request, Response $response, array $args): Response
    {
        $version = $args['version'] ?? null;
        
        // Authentifizierung
        $apiKey = $request->getHeaderLine('X-API-Key');
        if (!$this->validateApiKey($apiKey)) {
            return $this->jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        if (!$version) {
            return $this->jsonResponse($response, ['error' => 'Version required'], 400);
        }

        // Hole Release-Info
        $release = $this->db->fetchOne("SELECT * FROM bm_releases WHERE version = ?", [$version]);
        
        if (!$release) {
            return $this->jsonResponse($response, ['error' => 'Version not found'], 404);
        }

        // Erstelle Update-Package
        $package = $this->createUpdatePackage($release);

        return $this->jsonResponse($response, [
            'version' => $version,
            'package' => $package,
            'checksum' => md5(json_encode($package)),
        ]);
    }

    /**
     * Heartbeat - Melde Update-Status
     * POST /api/updates/heartbeat
     */
    public function heartbeat(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Authentifizierung
        $apiKey = $request->getHeaderLine('X-API-Key');
        $tenantId = $data['tenant_id'] ?? null;
        
        if (!$this->validateApiKey($apiKey, $tenantId)) {
            return $this->jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        // Speichere Heartbeat
        $this->db->insert('bm_tenant_heartbeats', [
            'tenant_id' => $tenantId,
            'php_version' => $data['php_version'] ?? null,
            'database_size' => $data['database_size'] ?? null,
            'user_count' => $data['user_count'] ?? null,
            'active_modules' => $data['active_modules'] ?? null,
            'response_time' => $data['response_time'] ?? null,
            'memory_usage' => $data['memory_usage'] ?? null,
            'heartbeat_status' => $data['status'] ?? 'online',
            'error_message' => $data['error_message'] ?? null,
        ]);

        // Update last_heartbeat in tenants
        $this->db->getConnection()->prepare("
            UPDATE bm_tenants 
            SET last_heartbeat = ?, installed_version = ?
            WHERE id = ?
        ")->execute([
            date('Y-m-d H:i:s'),
            $data['current_version'] ?? null,
            $tenantId
        ]);

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Heartbeat received',
        ]);
    }

    /**
     * Validiere API-Key
     */
    private function validateApiKey(string $apiKey, ?int $tenantId = null): bool
    {
        if (empty($apiKey)) {
            return false;
        }

        // Prüfe ob API-Key zu Tenant gehört
        if ($tenantId) {
            $tenant = $this->db->fetchOne("SELECT * FROM bm_tenants WHERE id = ?", [$tenantId]);
            
            if (!$tenant) {
                return false;
            }
            
            // TODO: API-Key in bm_tenants Tabelle speichern
            // Für jetzt: einfacher Check
            return $apiKey === 'sys-experts-api-key-' . $tenantId;
        }

        // Globaler API-Key
        return $apiKey === 'sys-experts-master-api-key';
    }

    /**
     * Erstelle Update-Package
     */
    private function createUpdatePackage(array $release): array
    {
        return [
            'version' => $release['version'],
            'files' => $this->getUpdateFiles($release['version']),
            'migrations' => $this->getMigrations($release['version']),
            'changelog' => $release['changelog'],
            'instructions' => $this->getUpdateInstructions($release),
        ];
    }

    /**
     * Hole Update-Dateien
     */
    private function getUpdateFiles(string $version): array
    {
        // TODO: Tatsächliche Dateien aus Git/Storage laden
        // Für jetzt: Dummy-Daten
        return [
            'core/Updates/UpdateController.php' => base64_encode('<?php // Updated file'),
            'resources/views/updates/index.php' => base64_encode('<!-- Updated view -->'),
        ];
    }

    /**
     * Hole Migrations
     */
    private function getMigrations(string $version): array
    {
        // TODO: Migrations-Dateien laden
        return [];
    }

    /**
     * Hole Update-Anweisungen
     */
    private function getUpdateInstructions(array $release): array
    {
        $instructions = [
            'backup_required' => true,
            'maintenance_mode' => $release['is_breaking_change'],
            'steps' => [
                'Backup erstellen',
                'Dateien aktualisieren',
            ],
        ];

        if ($release['requires_migration']) {
            $instructions['steps'][] = 'Migrations ausführen';
        }

        $instructions['steps'][] = 'Cache leeren';
        $instructions['steps'][] = 'Fertig!';

        return $instructions;
    }

    /**
     * JSON Response Helper
     */
    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
