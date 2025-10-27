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

// Notification API Routes
$app->group('/api/notifications', function (RouteCollectorProxy $group) use ($container) {
    // Liste abrufen
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\Notifications\NotificationController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
    
    // Als gelesen markieren
    $group->post('/{id}/read', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\Notifications\NotificationController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->markAsRead($request, $response, $args);
    });
    
    // Alle als gelesen markieren
    $group->post('/read-all', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\Notifications\NotificationController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->markAllAsRead($request, $response);
    });
    
    // Löschen
    $group->delete('/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\Notifications\NotificationController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->delete($request, $response, $args);
    });
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

// User Routes
$app->group('/users', function (RouteCollectorProxy $group) use ($container) {
    // Liste
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new UserController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
    
    // Erstellen
    $group->post('', function (Request $request, Response $response) use ($container) {
        $controller = new UserController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->store($request, $response);
    });
    
    // Aktualisieren
    $group->post('/{id}/update', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new UserController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->update($request, $response, $args);
    });
    
    // Deaktivieren
    $group->post('/{id}/delete', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new UserController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->delete($request, $response, $args);
    });
    
    // Aktivieren
    $group->post('/{id}/activate', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new UserController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->activate($request, $response, $args);
    });
    
    // Lizenzen verwalten
    $group->get('/{id}/licenses', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new UserController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->manageLicenses($request, $response, $args);
    });

    // Lizenz erteilen
    $group->post('/{user_id}/licenses/{module_id}/grant', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new UserController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->grantLicense($request, $response, $args);
    });

    // Lizenz entziehen
    $group->post('/{user_id}/licenses/{module_id}/revoke', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new UserController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->revokeLicense($request, $response, $args);
    });
});

// Module Routes
$app->group('/modules', function (RouteCollectorProxy $group) use ($container) {
    // Übersicht
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new ModuleController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
    
    // Aktivieren
    $group->post('/{id}/activate', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new ModuleController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->activate($request, $response, $args);
    });
    
    // Deaktivieren
    $group->post('/{id}/deactivate', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new ModuleController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->deactivate($request, $response, $args);
    });
});

// Marketplace Route
$app->get('/marketplace', function (Request $request, Response $response) use ($container) {
    $controller = new ModuleController(
        $container->get(Database::class),
        $container->get(SessionService::class)
    );
    return $controller->marketplace($request, $response);
});

// Customer Routes
$app->group('/customers', function (RouteCollectorProxy $group) use ($container) {
    // Liste
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new CustomerController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
    
    // Erstellen
    $group->post('', function (Request $request, Response $response) use ($container) {
        $controller = new CustomerController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->store($request, $response);
    });
    
    // Bearbeiten
    $group->post('/{id}/update', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new CustomerController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->update($request, $response, $args);
    });
    
    // Deaktivieren
    $group->post('/{id}/delete', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new CustomerController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->delete($request, $response, $args);
    });
});

// Settings Routes
$app->group('/settings', function (RouteCollectorProxy $group) use ($container) {
    // Anzeigen
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new SettingsController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
    
    // Speichern
    $group->post('/update', function (Request $request, Response $response) use ($container) {
        $controller = new SettingsController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->update($request, $response);
    });
});

// Invoice Routes
$app->group('/invoices', function (RouteCollectorProxy $group) use ($container) {
    // Liste
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new InvoiceController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
    
    // Details
    $group->get('/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new InvoiceController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->show($request, $response, $args);
    });
    
    // Erstellen
    $group->post('', function (Request $request, Response $response) use ($container) {
        $controller = new InvoiceController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->store($request, $response);
    });
    
    // Item hinzufügen
    $group->post('/{id}/items', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new InvoiceController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->addItem($request, $response, $args);
    });
    
    // Item löschen
    $group->post('/{id}/items/{item_id}/delete', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new InvoiceController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->deleteItem($request, $response, $args);
    });
    
    // Status ändern
    $group->post('/{id}/status', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new InvoiceController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->updateStatus($request, $response, $args);
    });
    
    // Löschen
    $group->post('/{id}/delete', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new InvoiceController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->delete($request, $response, $args);
    });
    
    // PDF Export
    $group->get('/{id}/pdf', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new InvoiceController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->exportPdf($request, $response, $args);
    });
    
    // E-Mail versenden
    $group->post('/{id}/send-email', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new InvoiceController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->sendEmail($request, $response, $args);
    });
});

// Helpdesk Routes
$app->group('/helpdesk', function (RouteCollectorProxy $group) use ($container) {
    // Übersicht
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\Helpdesk\HelpdeskController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
    
    // Ticket-Details
    $group->get('/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\Helpdesk\HelpdeskController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->show($request, $response, $args);
    });
    
    // Neues Ticket erstellen
    $group->post('', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\Helpdesk\HelpdeskController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->store($request, $response);
    });
    
    // Kommentar hinzufügen
    $group->post('/{id}/comments', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\Helpdesk\HelpdeskController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->addComment($request, $response, $args);
    });
    
    // Status ändern
    $group->post('/{id}/status', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\Helpdesk\HelpdeskController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->updateStatus($request, $response, $args);
    });
    
    // Zuweisen
    $group->post('/{id}/assign', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\Helpdesk\HelpdeskController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->assign($request, $response, $args);
    });
});

// Calendar Routes
$app->group('/calendar', function (RouteCollectorProxy $group) use ($container) {
    // Kalender-Ansicht
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\Calendar\CalendarController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
});

// Calendar API Routes
$app->group('/api/calendar', function (RouteCollectorProxy $group) use ($container) {
    // Events abrufen
    $group->get('/events', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\Calendar\CalendarController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->getEvents($request, $response);
    });
    
    // Event erstellen
    $group->post('/events', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\Calendar\CalendarController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->store($request, $response);
    });
    
    // Event aktualisieren
    $group->put('/events/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\Calendar\CalendarController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->update($request, $response, $args);
    });
    
    // Event löschen
    $group->delete('/events/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\Calendar\CalendarController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->delete($request, $response, $args);
    });
});

// Time Tracking Routes
$app->group('/time-tracking', function (RouteCollectorProxy $group) use ($container) {
    // Übersicht
    $group->get('', function (Request $request, Response $response) use ($container) {
        $pdo = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($pdo);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($pdo);
        $controller = new \SysExperts\BusinessManager\TimeTracking\TimeTrackingController(
            $container->get(Database::class),
            $authService,
            $sessionManager
        );
        return $controller->index($request, $response);
    });
    
    // Arbeit starten
    $group->post('/start', function (Request $request, Response $response) use ($container) {
        $pdo = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($pdo);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($pdo);
        $controller = new \SysExperts\BusinessManager\TimeTracking\TimeTrackingController(
            $container->get(Database::class),
            $authService,
            $sessionManager
        );
        return $controller->startWork($request, $response);
    });
    
    // Arbeit beenden
    $group->post('/{id}/end', function (Request $request, Response $response, array $args) use ($container) {
        $pdo = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($pdo);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($pdo);
        $controller = new \SysExperts\BusinessManager\TimeTracking\TimeTrackingController(
            $container->get(Database::class),
            $authService,
            $sessionManager
        );
        return $controller->endWork($request, $response, $args);
    });
    
    // Pause starten
    $group->post('/{id}/break/start', function (Request $request, Response $response, array $args) use ($container) {
        $pdo = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($pdo);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($pdo);
        $controller = new \SysExperts\BusinessManager\TimeTracking\TimeTrackingController(
            $container->get(Database::class),
            $authService,
            $sessionManager
        );
        return $controller->startBreak($request, $response, $args);
    });
    
    // Pause beenden
    $group->post('/{id}/break/{break_id}/end', function (Request $request, Response $response, array $args) use ($container) {
        $pdo = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($pdo);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($pdo);
        $controller = new \SysExperts\BusinessManager\TimeTracking\TimeTrackingController(
            $container->get(Database::class),
            $authService,
            $sessionManager
        );
        return $controller->endBreak($request, $response, $args);
    });
    
    // Wochenübersicht (muss VOR /{id} stehen)
    $group->get('/weekly-summary', function (Request $request, Response $response) use ($container) {
        $pdo = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($pdo);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($pdo);
        $controller = new \SysExperts\BusinessManager\TimeTracking\TimeTrackingController(
            $container->get(Database::class),
            $authService,
            $sessionManager
        );
        return $controller->weeklySummary($request, $response);
    });
    
    // CSV Export (muss VOR /{id} stehen)
    $group->get('/export/csv', function (Request $request, Response $response) use ($container) {
        $pdo = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($pdo);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($pdo);
        $controller = new \SysExperts\BusinessManager\TimeTracking\TimeTrackingController(
            $container->get(Database::class),
            $authService,
            $sessionManager
        );
        return $controller->exportCsv($request, $response);
    });
    
    // PDF Export (muss VOR /{id} stehen)
    $group->get('/export/pdf', function (Request $request, Response $response) use ($container) {
        $pdo = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($pdo);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($pdo);
        $controller = new \SysExperts\BusinessManager\TimeTracking\TimeTrackingController(
            $container->get(Database::class),
            $authService,
            $sessionManager
        );
        return $controller->exportPdf($request, $response);
    });
    
    // Details (dynamische Route muss NACH statischen Routen stehen)
    $group->get('/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $pdo = $container->get(Database::class)->getConnection();
        $authService = new \SysExperts\BusinessManager\Auth\AuthService($pdo);
        $sessionManager = new \SysExperts\BusinessManager\Auth\SessionManager($pdo);
        $controller = new \SysExperts\BusinessManager\TimeTracking\TimeTrackingController(
            $container->get(Database::class),
            $authService,
            $sessionManager
        );
        return $controller->show($request, $response, $args);
    });
});

// Audit-Log Routes
$app->group('/audit-logs', function (RouteCollectorProxy $group) use ($container) {
    // Übersicht
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\AuditLog\AuditLogController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
    
    // Export
    $group->get('/export', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\AuditLog\AuditLogController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->export($request, $response);
    });
});

// Update Routes
$app->group('/updates', function (RouteCollectorProxy $group) use ($container) {
    // Übersicht
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\Updates\UpdateController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
    
    // Update installieren
    $group->post('/install/{version}', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\Updates\UpdateController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->install($request, $response, $args);
    });
});

// Update API Routes (für Kunden-Instanzen)
$app->group('/api/updates', function (RouteCollectorProxy $group) use ($container) {
    // Prüfe auf Updates
    $group->get('/check', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\Updates\UpdateApiController(
            $container->get(Database::class)
        );
        return $controller->check($request, $response);
    });
    
    // Download Update-Package
    $group->get('/download/{version}', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\Updates\UpdateApiController(
            $container->get(Database::class)
        );
        return $controller->download($request, $response, $args);
    });
    
    // Heartbeat
    $group->post('/heartbeat', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\Updates\UpdateApiController(
            $container->get(Database::class)
        );
        return $controller->heartbeat($request, $response);
    });
});

// Partner Console Routes (nur für sys-experts.de Admins)
$app->group('/partner-console', function (RouteCollectorProxy $group) use ($container) {
    // Dashboard
    $group->get('', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\PartnerConsole\PartnerConsoleController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->index($request, $response);
    });
    
    // Neuer Mandant - Formular
    $group->get('/tenants/create', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\PartnerConsole\PartnerConsoleController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->create($request, $response);
    });
    
    // Mandant speichern
    $group->post('/tenants', function (Request $request, Response $response) use ($container) {
        $controller = new \SysExperts\BusinessManager\PartnerConsole\PartnerConsoleController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->store($request, $response);
    });
    
    // Mandanten-Details
    $group->get('/tenants/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $controller = new \SysExperts\BusinessManager\PartnerConsole\PartnerConsoleController(
            $container->get(Database::class),
            $container->get(SessionService::class)
        );
        return $controller->show($request, $response, $args);
    });
});
