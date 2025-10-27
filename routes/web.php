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
