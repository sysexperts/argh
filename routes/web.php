<?php
/**
 * Web Routes
 * 
 * @package SysExperts\BusinessManager
 */

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Auth\AuthController;
use SysExperts\BusinessManager\Auth\AuthMiddleware;
use SysExperts\BusinessManager\Auth\LicenseMiddleware;
use SysExperts\BusinessManager\Auth\SessionService;
use SysExperts\BusinessManager\Auth\SessionManager;
use SysExperts\BusinessManager\Auth\AuthService as CoreAuthService;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Navigation\NavigationService;
use SysExperts\BusinessManager\Users\UserController;
use SysExperts\BusinessManager\Modules\ModuleController;
use SysExperts\BusinessManager\Invoices\InvoiceController;
use SysExperts\BusinessManager\Customers\CustomerController;
use SysExperts\BusinessManager\Settings\SettingsController;

// Home - Redirect zu Login oder Dashboard
$app->get('/', function (Request $request, Response $response) use ($container) {
    // Nutze neuen SessionManager + AuthService
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if ($currentUser) {
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    return $response->withHeader('Location', '/auth/login')->withStatus(302);
});

// Dashboard
$app->get('/dashboard', function (Request $request, Response $response) use ($container) {
    // Auth-Check mit neuem SessionManager
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
    }

    $db = $container->get(Database::class);
    $navService = new NavigationService($db);

    // Hole Tenant-ID vom aktuellen User
    $tenantId = $currentUser->getTenantId();
    
    // Hole echte Stats aus der Datenbank (MIT TENANT-ISOLATION!)
    $stats = [
        'total_users' => $db->fetchOne('SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND is_active = 1', [$tenantId])['count'] ?? 0,
        'total_customers' => $db->fetchOne('SELECT COUNT(*) as count FROM bm_customers WHERE tenant_id = ? AND is_active = 1', [$tenantId])['count'] ?? 0,
        'total_invoices' => $db->fetchOne('SELECT COUNT(*) as count FROM bm_invoices WHERE tenant_id = ?', [$tenantId])['count'] ?? 0,
        'pending_invoices' => $db->fetchOne('SELECT COUNT(*) as count FROM bm_invoices WHERE tenant_id = ? AND status IN ("draft", "sent")', [$tenantId])['count'] ?? 0,
        'total_revenue' => $db->fetchOne('SELECT SUM(total) as sum FROM bm_invoices WHERE tenant_id = ? AND status = "paid"', [$tenantId])['sum'] ?? 0,
        'pending_amount' => $db->fetchOne('SELECT SUM(total) as sum FROM bm_invoices WHERE tenant_id = ? AND status IN ("sent", "overdue")', [$tenantId])['sum'] ?? 0,
        'active_modules' => $db->fetchOne('SELECT COUNT(DISTINCT module_id) as count FROM bm_module_licenses WHERE tenant_id = ? AND user_id = ? AND is_enabled = 1', [$tenantId, $currentUser->getId()])['count'] ?? 0,
    ];

    // Für Layout
    $user = $currentUser->toPublicArray();
    $navigation = $navService->getNavigation($currentUser->getId(), '/dashboard', $currentUser->getRole());
    
    ob_start();
    require __DIR__ . '/../resources/views/dashboard.php';
    $html = ob_get_clean();
    
    $response->getBody()->write($html);
    return $response;
});

// Modules/Marketplace
$app->get('/modules', function (Request $request, Response $response) use ($container) {
    // Auth-Check
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
    }

    $db = $container->get(Database::class);
    $navService = new NavigationService($db);
    
    // Für Layout
    $user = $currentUser->toPublicArray();
    $navigation = $navService->getNavigation($currentUser->getId(), '/modules', $currentUser->getRole());
    
    // Hole alle verfügbaren Module
    $tenantId = $currentUser->getTenantId();
    $userId = $currentUser->getId();
    
    $allModules = $pdo->query("
        SELECT 
            m.*,
            ml.is_enabled,
            ml.id as license_id
        FROM bm_modules m
        LEFT JOIN bm_module_licenses ml ON m.id = ml.module_id 
            AND ml.user_id = {$userId}
        WHERE m.is_active = 1
        ORDER BY m.category, m.display_order, m.name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Gruppiere nach Kategorie
    $modulesByCategory = [];
    foreach ($allModules as $module) {
        $category = $module['category'] ?? 'Sonstiges';
        if (!isset($modulesByCategory[$category])) {
            $modulesByCategory[$category] = [];
        }
        $modulesByCategory[$category][] = $module;
    }
    
    ob_start();
    require __DIR__ . '/../resources/views/modules/index.php';
    $html = ob_get_clean();
    
    $response->getBody()->write($html);
    return $response;
});

// Users (Benutzerverwaltung)
$app->get('/users', function (Request $request, Response $response) use ($container) {
    // Auth-Check
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
    }

    // Nur Admins dürfen Benutzer verwalten
    if ($currentUser->getRole() !== 'admin') {
        $_SESSION['error'] = 'Sie haben keine Berechtigung, Benutzer zu verwalten.';
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    $db = $container->get(Database::class);
    $navService = new NavigationService($db);
    
    // Für Layout
    $user = $currentUser->toPublicArray();
    $navigation = $navService->getNavigation($currentUser->getId(), '/users', $currentUser->getRole());
    
    // Hole alle Benutzer des gleichen Tenants
    $tenantId = $currentUser->getTenantId();
    $users = $pdo->query("
        SELECT 
            id, email, first_name, last_name, role, is_active, 
            last_login, created_at
        FROM users 
        WHERE tenant_id = {$tenantId}
        ORDER BY created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    ob_start();
    require __DIR__ . '/../resources/views/users/index.php';
    $html = ob_get_clean();
    
    $response->getBody()->write($html);
    return $response;
});

// Customers (Kundenverwaltung)
$app->get('/customers', function (Request $request, Response $response) use ($container) {
    // Auth-Check
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
    }

    $db = $container->get(Database::class);
    $navService = new NavigationService($db);
    
    // Für Layout
    $user = $currentUser->toPublicArray();
    $navigation = $navService->getNavigation($currentUser->getId(), '/customers', $currentUser->getRole());
    
    // Hole alle Kunden des gleichen Tenants
    $tenantId = $currentUser->getTenantId();
    $customers = $pdo->query("
        SELECT *
        FROM bm_customers 
        WHERE tenant_id = {$tenantId}
        ORDER BY created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    ob_start();
    require __DIR__ . '/../resources/views/customers/index.php';
    $html = ob_get_clean();
    
    $response->getBody()->write($html);
    return $response;
});

// Time Tracking (Zeiterfassung)
$app->get('/time-tracking', function (Request $request, Response $response) use ($container) {
    // Auth-Check
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
    }

    $db = $container->get(Database::class);
    $navService = new NavigationService($db);
    
    // Für Layout
    $user = $currentUser->toPublicArray();
    $navigation = $navService->getNavigation($currentUser->getId(), '/time-tracking', $currentUser->getRole());
    
    // Variablen für Zeiterfassung
    $userId = $currentUser->getId();
    $tenantId = $currentUser->getTenantId();
    
    // Hole aktiven Eintrag
    $activeEntry = null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE user_id = ? AND end_time IS NULL ORDER BY date DESC, start_time DESC LIMIT 1");
        $stmt->execute([$userId]);
        $activeEntry = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($activeEntry) {
            // Kombiniere date und start_time für Kompatibilität
            $activeEntry['start_time'] = $activeEntry['date'] . ' ' . $activeEntry['start_time'];
        }
    } catch (\Exception $e) {
        // Tabelle existiert möglicherweise nicht
    }
    
    // Datum-Filter
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-t');
    
    // Hole Zeiteinträge
    $entries = [];
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM time_entries 
            WHERE user_id = ? 
            AND date BETWEEN ? AND ?
            ORDER BY date DESC, start_time DESC
        ");
        $stmt->execute([$userId, $startDate, $endDate]);
        $rawEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Kombiniere date und times für Kompatibilität mit View
        foreach ($rawEntries as $entry) {
            // Nur kombinieren wenn start_time noch kein Datum enthält
            if (!str_contains($entry['start_time'], ' ')) {
                $entry['start_time'] = $entry['date'] . ' ' . $entry['start_time'];
            }
            if ($entry['end_time'] && !str_contains($entry['end_time'], ' ')) {
                $entry['end_time'] = $entry['date'] . ' ' . $entry['end_time'];
            }
            $entries[] = $entry;
        }
    } catch (\Exception $e) {
        // Tabelle existiert möglicherweise nicht
    }
    
    require __DIR__ . '/../resources/views/time_tracking/index.php';
    return $response;
});

// Time Tracking API Endpoints
$app->post('/api/time-tracking/start', function (Request $request, Response $response) use ($container) {
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Nicht angemeldet']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $data = $request->getParsedBody();
    $userId = $currentUser->getId();
    $tenantId = $currentUser->getTenantId();
    
    try {
        $now = new \DateTime();
        $stmt = $pdo->prepare("
            INSERT INTO time_entries (user_id, tenant_id, date, start_time, status, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'active', ?, datetime('now'), datetime('now'))
        ");
        $stmt->execute([
            $userId,
            $tenantId,
            $now->format('Y-m-d'),
            $now->format('H:i:s'),
            $data['description'] ?? ''
        ]);
        
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Zeiterfassung gestartet']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
});

$app->post('/api/time-tracking/stop', function (Request $request, Response $response) use ($container) {
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Nicht angemeldet']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $userId = $currentUser->getId();
    
    try {
        $now = new \DateTime();
        $stmt = $pdo->prepare("
            UPDATE time_entries 
            SET end_time = ?, status = 'completed', updated_at = datetime('now')
            WHERE user_id = ? AND end_time IS NULL
        ");
        $stmt->execute([$now->format('H:i:s'), $userId]);
        
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Zeiterfassung beendet']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
});

$app->post('/api/time-tracking/manual', function (Request $request, Response $response) use ($container) {
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Nicht angemeldet']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $data = $request->getParsedBody();
    $userId = $currentUser->getId();
    $tenantId = $currentUser->getTenantId();
    
    try {
        // Validierung: Nicht in der Zukunft
        $entryDate = new \DateTime($data['start_time']);
        $now = new \DateTime();
        
        if ($entryDate > $now) {
            throw new \Exception('Zeiteinträge können nicht in der Zukunft liegen.');
        }
        
        // Parse times
        $startDateTime = new \DateTime($data['start_time']);
        $endDateTime = new \DateTime($data['end_time']);
        
        if ($endDateTime <= $startDateTime) {
            throw new \Exception('Endzeit muss nach Startzeit liegen.');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO time_entries (user_id, tenant_id, date, start_time, end_time, status, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 'completed', ?, datetime('now'), datetime('now'))
        ");
        $stmt->execute([
            $userId,
            $tenantId,
            $startDateTime->format('Y-m-d'),
            $startDateTime->format('H:i:s'),
            $endDateTime->format('H:i:s'),
            $data['description'] ?? ''
        ]);
        
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Zeiteintrag erstellt']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
});

$app->delete('/api/time-tracking/{id}', function (Request $request, Response $response, array $args) use ($container) {
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Nicht angemeldet']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $entryId = $args['id'];
    $userId = $currentUser->getId();
    
    try {
        $stmt = $pdo->prepare("DELETE FROM time_entries WHERE id = ? AND user_id = ?");
        $stmt->execute([$entryId, $userId]);
        
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Eintrag gelöscht']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
});

// Settings (Einstellungen)
$app->get('/settings', function (Request $request, Response $response) use ($container) {
    return $response->withHeader('Location', '/dashboard')->withStatus(302);
});

// User API Endpoints
$app->post('/api/users', function (Request $request, Response $response) use ($container) {
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser || $currentUser->getRole() !== 'admin') {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Keine Berechtigung']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }

    $data = $request->getParsedBody();
    $tenantId = $currentUser->getTenantId();
    
    try {
        // Prüfe ob Email bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND tenant_id = ?");
        $stmt->execute([$data['email'], $tenantId]);
        if ($stmt->fetch()) {
            throw new \Exception('Diese E-Mail-Adresse wird bereits verwendet.');
        }
        
        // Erstelle Benutzer
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (
                tenant_id, email, password_hash, first_name, last_name, 
                role, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, 1, datetime('now'), datetime('now'))
        ");
        $stmt->execute([
            $tenantId,
            $data['email'],
            $hashedPassword,
            $data['first_name'],
            $data['last_name'],
            $data['role'] ?? 'user'
        ]);
        
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Benutzer erfolgreich erstellt']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
});

$app->put('/api/users/{id}', function (Request $request, Response $response, array $args) use ($container) {
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser || $currentUser->getRole() !== 'admin') {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Keine Berechtigung']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }

    $data = $request->getParsedBody();
    $userId = $args['id'];
    $tenantId = $currentUser->getTenantId();
    
    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, email = ?, role = ?, is_active = ?, updated_at = datetime('now')
            WHERE id = ? AND tenant_id = ?
        ");
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['role'],
            $data['is_active'] ? 1 : 0,
            $userId,
            $tenantId
        ]);
        
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Benutzer erfolgreich aktualisiert']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
});

$app->delete('/api/users/{id}', function (Request $request, Response $response, array $args) use ($container) {
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser || $currentUser->getRole() !== 'admin') {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Keine Berechtigung']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }

    $userId = $args['id'];
    $tenantId = $currentUser->getTenantId();
    
    try {
        // Verhindere Selbst-Löschung
        if ($userId == $currentUser->getId()) {
            throw new \Exception('Sie können sich nicht selbst löschen.');
        }
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$userId, $tenantId]);
        
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Benutzer erfolgreich gelöscht']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
});

// Audit Logs
$app->get('/audit-logs', function (Request $request, Response $response) use ($container) {
    // Auth-Check
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
    }

    $db = $container->get(Database::class);
    $navService = new NavigationService($db);
    
    $user = $currentUser->toPublicArray();
    $navigation = $navService->getNavigation($currentUser->getId(), '/audit-logs', $currentUser->getRole());
    
    // Hole Audit Logs
    $tenantId = $currentUser->getTenantId();
    $filters = [];
    $logs = [];
    $totalPages = 0;
    $page = 1;
    
    require __DIR__ . '/../resources/views/audit-logs/index.php';
    return $response;
});

// Invoices (Rechnungen)
$app->get('/invoices', function (Request $request, Response $response) use ($container) {
    // Auth-Check
    $pdo = $container->get(Database::class)->getConnection();
    $authService = new CoreAuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    $currentUser = $sessionManager->getCurrentUser($authService);

    if (!$currentUser) {
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
    }

    $db = $container->get(Database::class);
    $navService = new NavigationService($db);
    
    $user = $currentUser->toPublicArray();
    $navigation = $navService->getNavigation($currentUser->getId(), '/invoices', $currentUser->getRole());
    
    // Hole Rechnungen
    $tenantId = $currentUser->getTenantId();
    $invoices = [];
    
    try {
        $invoices = $pdo->query("
            SELECT * FROM bm_invoices 
            WHERE tenant_id = {$tenantId}
            ORDER BY created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Exception $e) {
        // Tabelle existiert möglicherweise nicht
    }
    
    require __DIR__ . '/../resources/views/invoices/index.php';
    return $response;
});

// Catch-all für Module die noch nicht implementiert sind
$moduleRoutes = ['projects', 'tasks', 'documents', 'calendar', 'helpdesk'];
foreach ($moduleRoutes as $moduleRoute) {
    $app->get('/' . $moduleRoute, function (Request $request, Response $response) use ($container, $moduleRoute) {
        // Auth-Check
        $pdo = $container->get(Database::class)->getConnection();
        $authService = new CoreAuthService($pdo);
        $sessionManager = new SessionManager($pdo);
        $currentUser = $sessionManager->getCurrentUser($authService);

        if (!$currentUser) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $db = $container->get(Database::class);
        $navService = new NavigationService($db);
        
        $user = $currentUser->toPublicArray();
        $navigation = $navService->getNavigation($currentUser->getId(), '/' . $moduleRoute, $currentUser->getRole());
        
        $moduleName = ucfirst(str_replace('-', ' ', $moduleRoute));
        
        ob_start();
        ?>
        <div class="mb-8">
            <div class="bg-gradient-to-r from-primary via-purple-500 to-pink-500 rounded-2xl p-8 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div class="text-white">
                        <h1 class="text-3xl font-bold mb-2"><?= $moduleName ?></h1>
                        <p class="text-white/90 text-lg">Dieses Modul wird gerade entwickelt</p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                            <span class="material-symbols-outlined text-6xl text-white">construction</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg p-12 text-center">
            <span class="material-symbols-outlined text-8xl text-primary mb-4 block">construction</span>
            <h2 class="text-2xl font-bold text-text-light dark:text-text-dark mb-4">In Entwicklung</h2>
            <p class="text-text-muted-light dark:text-text-muted-dark mb-6">
                Das Modul "<?= $moduleName ?>" wird gerade entwickelt und ist bald verfügbar.
            </p>
            <a href="/dashboard" class="inline-block bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary/90 transition">
                Zurück zum Dashboard
            </a>
        </div>
        <?php
        $content = ob_get_clean();
        $pageTitle = $moduleName;
        $title = $moduleName . ' - Business Manager';
        require __DIR__ . '/../resources/views/layouts/app.php';
        
        $response->getBody()->write($content);
        return $response;
    });
}

// API Health Check
$app->get('/api/health', function (Request $request, Response $response) {
    $data = [
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '0.1.0',
    ];
    
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// Auth Routes
$app->group('/auth', function (RouteCollectorProxy $group) use ($container) {
    // Login anzeigen
    $group->get('/login', function (Request $request, Response $response) use ($container) {
        $db = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($db);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($db);
        $controller = new AuthController($authService, $sessionManager);
        return $controller->showLogin($request, $response);
    });
    
    // Login verarbeiten
    $group->post('/login', function (Request $request, Response $response) use ($container) {
        $db = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($db);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($db);
        $controller = new AuthController($authService, $sessionManager);
        return $controller->login($request, $response);
    });
    
    // Logout
    $group->get('/logout', function (Request $request, Response $response) use ($container) {
        $db = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($db);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($db);
        $controller = new AuthController($authService, $sessionManager);
        return $controller->logout($request, $response);
    });
    
    // Registrierung anzeigen
    $group->get('/register', function (Request $request, Response $response) use ($container) {
        $db = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($db);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($db);
        $controller = new AuthController($authService, $sessionManager);
        return $controller->showRegister($request, $response);
    });
    
    // Registrierung verarbeiten
    $group->post('/register', function (Request $request, Response $response) use ($container) {
        $db = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($db);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($db);
        $controller = new AuthController($authService, $sessionManager);
        return $controller->register($request, $response);
    });
    
    // E-Mail verifizieren
    $group->get('/verify-email', function (Request $request, Response $response) use ($container) {
        $db = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($db);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($db);
        $controller = new AuthController($authService, $sessionManager);
        return $controller->verifyEmail($request, $response);
    });
});
