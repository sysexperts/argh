<?php
declare(strict_types=1);

namespace SysExperts\BusinessManager\TimeTracking;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Auth\AuthService;
use SysExperts\BusinessManager\Auth\SessionManager;
use SysExperts\BusinessManager\Database\Database;

class TimeTrackingController
{
    private TimeTrackingService $service;
    private AuthService $authService;
    private SessionManager $sessionManager;
    private Database $db;

    public function __construct(Database $db, AuthService $authService, SessionManager $sessionManager)
    {
        $this->db = $db;
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
        $this->service = new TimeTrackingService($db->getConnection());
    }

    public function index(Request $request, Response $response): Response
    {
        $currentUser = $this->sessionManager->getCurrentUser($this->authService);
        if (!$currentUser) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $params = $request->getQueryParams();
        $startDate = $params['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $params['end_date'] ?? date('Y-m-d');

        if ($currentUser->isAdmin()) {
            $entries = $this->service->getAllEntries($startDate, $endDate);
        } else {
            $entries = $this->service->getEntriesByUser($currentUser->getId(), $startDate, $endDate);
        }

        $activeEntry = $this->service->getActiveEntry($currentUser->getId());

        // Für Layout
        $user = $currentUser->toPublicArray();
        $navService = new \SysExperts\BusinessManager\Navigation\NavigationService($this->db);
        $navigation = $navService->getNavigation($currentUser->getId(), '/time-tracking');

        ob_start();
        require __DIR__ . '/../../resources/views/time_tracking/index.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    public function startWork(Request $request, Response $response): Response
    {
        $currentUser = $this->sessionManager->getCurrentUser($this->authService);
        if (!$currentUser) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        try {
            $data = $request->getParsedBody();
            $notes = $data['notes'] ?? null;

            $this->service->startWork($currentUser->getId(), $notes);

            $_SESSION['success'] = 'Arbeitsbeginn erfasst';
            return $response->withHeader('Location', '/time-tracking')->withStatus(302);
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return $response->withHeader('Location', '/time-tracking')->withStatus(302);
        }
    }

    public function endWork(Request $request, Response $response, array $args): Response
    {
        $currentUser = $this->sessionManager->getCurrentUser($this->authService);
        if (!$currentUser) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        try {
            $entryId = (int)$args['id'];
            $this->service->endWork($entryId, $currentUser->getId());

            $_SESSION['success'] = 'Arbeitsende erfasst';
            return $response->withHeader('Location', '/time-tracking')->withStatus(302);
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return $response->withHeader('Location', '/time-tracking')->withStatus(302);
        }
    }

    public function startBreak(Request $request, Response $response, array $args): Response
    {
        $currentUser = $this->sessionManager->getCurrentUser($this->authService);
        if (!$currentUser) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        try {
            $entryId = (int)$args['id'];
            $data = $request->getParsedBody();
            $breakType = $data['break_type'] ?? 'regular';

            $this->service->startBreak($entryId, $currentUser->getId(), $breakType);

            $_SESSION['success'] = 'Pause gestartet';
            return $response->withHeader('Location', '/time-tracking')->withStatus(302);
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return $response->withHeader('Location', '/time-tracking')->withStatus(302);
        }
    }

    public function endBreak(Request $request, Response $response, array $args): Response
    {
        $currentUser = $this->sessionManager->getCurrentUser($this->authService);
        if (!$currentUser) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        try {
            $entryId = (int)$args['id'];
            $breakId = (int)$args['break_id'];

            $this->service->endBreak($breakId, $entryId, $currentUser->getId());

            $_SESSION['success'] = 'Pause beendet';
            return $response->withHeader('Location', '/time-tracking')->withStatus(302);
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return $response->withHeader('Location', '/time-tracking')->withStatus(302);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $currentUser = $this->sessionManager->getCurrentUser($this->authService);
        if (!$currentUser) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        try {
            $entryId = (int)$args['id'];
            $entry = $this->service->getEntryById($entryId);

            if (!$currentUser->isAdmin() && $entry->getUserId() !== $currentUser->getId()) {
                $_SESSION['error'] = 'Keine Berechtigung';
                return $response->withHeader('Location', '/time-tracking')->withStatus(302);
            }

            $violations = $this->service->checkViolations($entry);

            // Für Layout
            $user = $currentUser->toPublicArray();
            $navService = new \SysExperts\BusinessManager\Navigation\NavigationService($this->db);
            $navigation = $navService->getNavigation($currentUser->getId(), '/time-tracking');

            ob_start();
            require __DIR__ . '/../../resources/views/time_tracking/show.php';
            $html = ob_get_clean();

            $response->getBody()->write($html);
            return $response;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return $response->withHeader('Location', '/time-tracking')->withStatus(302);
        }
    }

    public function exportCsv(Request $request, Response $response): Response
    {
        $currentUser = $this->sessionManager->getCurrentUser($this->authService);
        if (!$currentUser) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $params = $request->getQueryParams();
        $startDate = $params['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $params['end_date'] ?? date('Y-m-d');

        if ($currentUser->isAdmin()) {
            $entries = $this->service->getAllEntries($startDate, $endDate);
        } else {
            $entries = $this->service->getEntriesByUser($currentUser->getId(), $startDate, $endDate);
        }

        $csv = "Datum,Arbeitsbeginn,Arbeitsende,Gesamtstunden,Überstunden,Status,Notizen\n";
        foreach ($entries as $entry) {
            $csv .= sprintf(
                "%s,%s,%s,%.2f,%.2f,%s,%s\n",
                $entry->getDate(),
                date('H:i', strtotime($entry->getStartTime())),
                $entry->getEndTime() ? date('H:i', strtotime($entry->getEndTime())) : '-',
                $entry->getTotalHours() ?? 0,
                $entry->getOvertimeHours(),
                $entry->getStatus(),
                str_replace(["\n", "\r", ","], [" ", " ", ";"], $entry->getNotes() ?? '')
            );
        }

        $response->getBody()->write($csv);
        return $response
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="zeiterfassung_' . date('Y-m-d') . '.csv"');
    }

    public function exportPdf(Request $request, Response $response): Response
    {
        $currentUser = $this->sessionManager->getCurrentUser($this->authService);
        if (!$currentUser) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $params = $request->getQueryParams();
        $startDate = $params['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $params['end_date'] ?? date('Y-m-d');

        if ($currentUser->isAdmin()) {
            $entries = $this->service->getAllEntries($startDate, $endDate);
        } else {
            $entries = $this->service->getEntriesByUser($currentUser->getId(), $startDate, $endDate);
        }

        $pdfService = new TimeTrackingPdfService();
        $pdf = $pdfService->generateReport($entries, $currentUser, $startDate, $endDate);

        $response->getBody()->write($pdf);
        return $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'attachment; filename="zeiterfassung_' . date('Y-m-d') . '.pdf"');
    }

    public function weeklySummary(Request $request, Response $response): Response
    {
        $currentUser = $this->sessionManager->getCurrentUser($this->authService);
        if (!$currentUser) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $params = $request->getQueryParams();
        $weekStart = $params['week_start'] ?? date('Y-m-d', strtotime('monday this week'));

        $summary = $this->service->getWeeklySummary($currentUser->getId(), $weekStart);

        // Für Layout
        $user = $currentUser->toPublicArray();
        $navService = new \SysExperts\BusinessManager\Navigation\NavigationService($this->db);
        $navigation = $navService->getNavigation($currentUser->getId(), '/time-tracking');

        ob_start();
        require __DIR__ . '/../../resources/views/time_tracking/weekly_summary.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }
}
