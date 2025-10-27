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
    
    // Datum-Filter
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-t');
    
    // Hole Zeiteinträge (leer für jetzt, da Tabelle möglicherweise nicht existiert)
    $entries = [];
    
    ob_start();
    require __DIR__ . '/../resources/views/time_tracking/index.php';
    $html = ob_get_clean();
    
    $response->getBody()->write($html);
    return $response;
});

// Settings (Einstellungen)
$app->get('/settings', function (Request $request, Response $response) use ($container) {
    return $response->withHeader('Location', '/dashboard')->withStatus(302);
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

// Catch-all für Module die noch nicht implementiert sind
$moduleRoutes = ['projects', 'tasks', 'documents', 'invoices', 'calendar', 'helpdesk'];
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
