<?php

namespace SysExperts\BusinessManager\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use SysExperts\BusinessManager\Database\Database;

/**
 * License Middleware
 * Prüft ob Benutzer Lizenz für das angeforderte Modul hat
 */
class LicenseMiddleware
{
    private Database $db;
    private string $moduleCode;

    public function __construct(Database $db, string $moduleCode)
    {
        $this->db = $db;
        $this->moduleCode = $moduleCode;
    }

    /**
     * Middleware-Handler
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Hole aktuellen User aus Request (wurde von AuthMiddleware gesetzt)
        $user = $request->getAttribute('user');

        if (!$user) {
            // Kein User → sollte nicht passieren, aber sicher ist sicher
            $response = new \Slim\Psr7\Response();
            return $response
                ->withHeader('Location', '/auth/login')
                ->withStatus(302);
        }

        // Admin-Bypass: Admin darf alles
        if ($user->getRole() === 'admin') {
            return $handler->handle($request);
        }

        // Prüfe ob User Lizenz für dieses Modul hat
        $hasLicense = $this->checkLicense($user->getId(), $this->moduleCode);

        if (!$hasLicense) {
            // Keine Lizenz → Redirect zu Marketplace mit Hinweis
            $_SESSION['error'] = 'Sie haben keine Lizenz für dieses Modul. Bitte aktivieren Sie es im Marketplace.';
            
            $response = new \Slim\Psr7\Response();
            return $response
                ->withHeader('Location', '/marketplace')
                ->withStatus(302);
        }

        // Lizenz vorhanden → Request durchlassen
        return $handler->handle($request);
    }

    /**
     * Prüfe ob User Lizenz für Modul hat
     */
    private function checkLicense(int $userId, string $moduleCode): bool
    {
        try {
            $result = $this->db->fetchOne("
                SELECT ml.id
                FROM bm_module_licenses ml
                INNER JOIN bm_modules m ON ml.module_id = m.id
                WHERE ml.user_id = ? 
                  AND m.code = ? 
                  AND ml.is_enabled = 1
                  AND m.is_active = 1
            ", [$userId, $moduleCode]);

            return $result !== null;
        } catch (\Exception $e) {
            // Bei Fehler: Zugriff verweigern
            return false;
        }
    }
}
