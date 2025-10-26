<?php

namespace SysExperts\BusinessManager\Auth;

use SysExperts\BusinessManager\Database\Database;

/**
 * License Checker
 * Hilfsklasse zum Prüfen von Modul-Lizenzen
 */
class LicenseChecker
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Prüfe ob User Lizenz für Modul hat
     * 
     * @param int $userId User-ID
     * @param string $moduleCode Modul-Code (z.B. 'invoices', 'time-tracking')
     * @param string $userRole User-Rolle (admin hat immer Zugriff)
     * @return bool
     */
    public function hasLicense(int $userId, string $moduleCode, string $userRole = 'user'): bool
    {
        // Admin-Bypass: Admin darf alles
        if ($userRole === 'admin') {
            return true;
        }

        // Core-Module sind immer verfügbar
        $coreModules = ['auth', 'dashboard', 'users', 'settings', 'marketplace', 'modules'];
        if (in_array($moduleCode, $coreModules)) {
            return true;
        }

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
            // Bei Fehler: Zugriff verweigern (außer Core-Module)
            return false;
        }
    }

    /**
     * Prüfe Lizenz und redirecte zu Marketplace wenn keine vorhanden
     * 
     * @param int $userId
     * @param string $moduleCode
     * @param string $userRole
     * @param string $moduleName Anzeigename für Fehlermeldung
     * @return bool true wenn Lizenz vorhanden, false wenn redirect nötig
     */
    public function checkOrRedirect(int $userId, string $moduleCode, string $userRole, string $moduleName = 'dieses Modul'): bool
    {
        if (!$this->hasLicense($userId, $moduleCode, $userRole)) {
            $_SESSION['error'] = "Sie haben keine Lizenz für {$moduleName}. Bitte aktivieren Sie es im Marketplace.";
            header('Location: /marketplace');
            exit;
        }
        return true;
    }
}
