<?php
/**
 * Invoice Controller
 * 
 * @package SysExperts\BusinessManager\Invoices
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Invoices;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;
use SysExperts\BusinessManager\Auth\LicenseChecker;
use SysExperts\BusinessManager\Navigation\NavigationService;
use SysExperts\BusinessManager\Mail\MailService;

class InvoiceController
{
    private Database $db;
    private SessionService $session;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Liste aller Rechnungen
     */
    public function index(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        // Lizenzprüfung
        $licenseChecker = new LicenseChecker($this->db);
        if (!$licenseChecker->hasLicense($user['id'], 'invoices', $user['role'] ?? 'user')) {
            $_SESSION['error'] = 'Sie haben keine Lizenz für das Rechnungs-Modul. Bitte aktivieren Sie es im Marketplace.';
            return $response->withHeader('Location', '/marketplace')->withStatus(302);
        }
        
        // Hole alle Rechnungen
        $invoices = $this->db->fetchAll("
            SELECT * FROM bm_invoices
            ORDER BY invoice_date DESC, created_at DESC
        ");

        // Navigation
        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/invoices', $user['role'] ?? 'user');

        // Mache $this->db für View verfügbar
        $db = $this->db;

        ob_start();
        require __DIR__ . '/../../resources/views/invoices/index.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Rechnungs-Details anzeigen
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        $invoiceId = (int) $args['id'];
        
        // Hole Rechnung
        $invoice = $this->db->fetchOne('SELECT * FROM bm_invoices WHERE id = ?', [$invoiceId]);
        
        if (!$invoice) {
            $_SESSION['error'] = 'Rechnung nicht gefunden';
            return $response->withHeader('Location', '/invoices')->withStatus(302);
        }

        // Hole Rechnungspositionen
        $items = $this->db->fetchAll('SELECT * FROM bm_invoice_items WHERE invoice_id = ? ORDER BY id', [$invoiceId]);

        // Navigation
        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/invoices', $user['role'] ?? 'user');

        ob_start();
        require __DIR__ . '/../../resources/views/invoices/show.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Rechnung erstellen
     */
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $user = $this->session->getUser();

        // Generiere Rechnungsnummer
        $year = date('Y');
        $lastInvoice = $this->db->fetchOne("
            SELECT invoice_number FROM bm_invoices 
            WHERE invoice_number LIKE ? 
            ORDER BY invoice_number DESC LIMIT 1
        ", ["RE-$year-%"]);
        
        $nextNumber = 1;
        if ($lastInvoice) {
            preg_match('/RE-\d{4}-(\d+)/', $lastInvoice['invoice_number'], $matches);
            $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        }
        $invoiceNumber = sprintf('RE-%s-%04d', $year, $nextNumber);

        // Erstelle Rechnung
        $invoiceId = $this->db->insert('bm_invoices', [
            'tenant_id' => 1,
            'user_id' => $user['id'],
            'invoice_number' => $invoiceNumber,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'customer_address' => $data['customer_address'] ?? null,
            'invoice_date' => $data['invoice_date'] ?? date('Y-m-d'),
            'due_date' => $data['due_date'] ?? date('Y-m-d', strtotime('+14 days')),
            'status' => 'draft',
            'subtotal' => 0,
            'tax_rate' => (float)($data['tax_rate'] ?? 19),
            'tax_amount' => 0,
            'total' => 0,
            'notes' => $data['notes'] ?? null,
        ]);

        $_SESSION['success'] = 'Rechnung erfolgreich erstellt';
        return $response->withHeader('Location', "/invoices/$invoiceId")->withStatus(302);
    }

    /**
     * Rechnungsposition hinzufügen
     */
    public function addItem(Request $request, Response $response, array $args): Response
    {
        $invoiceId = (int) $args['id'];
        $data = $request->getParsedBody();

        $quantity = (float)($data['quantity'] ?? 1);
        $unitPrice = (float)($data['unit_price'] ?? 0);
        $taxRate = (float)($data['tax_rate'] ?? 19);
        
        // Berechne Netto-Total
        $netTotal = round($quantity * $unitPrice, 2);
        // Berechne MwSt-Betrag
        $taxAmount = round($netTotal * ($taxRate / 100), 2);
        // Berechne Brutto-Total
        $itemTotal = $netTotal + $taxAmount;

        // Füge Item hinzu
        $this->db->insert('bm_invoice_items', [
            'invoice_id' => $invoiceId,
            'description' => $data['description'],
            'quantity' => $quantity,
            'unit' => $data['unit'] ?? 'Stück',
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'total' => $itemTotal,
        ]);

        // Wenn als Vorlage speichern gewünscht
        if (isset($data['save_as_template']) && $data['save_as_template']) {
            // TODO: In Vorlagen-Tabelle speichern (später implementieren)
        }

        // Aktualisiere Rechnungssummen
        $this->recalculateInvoice($invoiceId);

        $_SESSION['success'] = 'Position hinzugefügt';
        return $response->withHeader('Location', "/invoices/$invoiceId")->withStatus(302);
    }

    /**
     * Rechnungsposition löschen
     */
    public function deleteItem(Request $request, Response $response, array $args): Response
    {
        $invoiceId = (int) $args['id'];
        $itemId = (int) $args['item_id'];

        $this->db->query('DELETE FROM bm_invoice_items WHERE id = ? AND invoice_id = ?', [$itemId, $invoiceId]);

        // Aktualisiere Rechnungssummen
        $this->recalculateInvoice($invoiceId);

        $_SESSION['success'] = 'Position gelöscht';
        return $response->withHeader('Location', "/invoices/$invoiceId")->withStatus(302);
    }

    /**
     * Rechnung neu berechnen
     */
    private function recalculateInvoice(int $invoiceId): void
    {
        $items = $this->db->fetchAll('SELECT * FROM bm_invoice_items WHERE invoice_id = ?', [$invoiceId]);
        $invoice = $this->db->fetchOne('SELECT tax_rate FROM bm_invoices WHERE id = ?', [$invoiceId]);

        $subtotal = array_sum(array_column($items, 'total'));
        $taxRate = (float)($invoice['tax_rate'] ?? 19);
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $total = $subtotal + $taxAmount;

        $this->db->update('bm_invoices', [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ], 'id = ?', [$invoiceId]);
    }

    /**
     * Rechnungsstatus ändern
     */
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $invoiceId = (int) $args['id'];
        $data = $request->getParsedBody();
        $status = $data['status'] ?? 'draft';

        $updateData = ['status' => $status];
        
        // Wenn auf "paid" gesetzt, speichere Zahlungsdatum
        if ($status === 'paid') {
            $updateData['paid_at'] = date('Y-m-d H:i:s');
        }

        $this->db->update('bm_invoices', $updateData, 'id = ?', [$invoiceId]);

        $_SESSION['success'] = 'Status aktualisiert';
        return $response->withHeader('Location', '/invoices')->withStatus(302);
    }

    /**
     * Rechnung löschen
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $invoiceId = (int) $args['id'];

        // Lösche Rechnungspositionen
        $this->db->query('DELETE FROM bm_invoice_items WHERE invoice_id = ?', [$invoiceId]);
        
        // Lösche Rechnung
        $this->db->query('DELETE FROM bm_invoices WHERE id = ?', [$invoiceId]);

        $_SESSION['success'] = 'Rechnung gelöscht';
        return $response->withHeader('Location', '/invoices')->withStatus(302);
    }

    /**
     * PDF Export
     */
    public function exportPdf(Request $request, Response $response, array $args): Response
    {
        $invoiceId = (int) $args['id'];
        
        // Hole Rechnung
        $invoice = $this->db->fetchOne('SELECT * FROM bm_invoices WHERE id = ?', [$invoiceId]);
        if (!$invoice) {
            $_SESSION['error'] = 'Rechnung nicht gefunden';
            return $response->withHeader('Location', '/invoices')->withStatus(302);
        }

        // Generiere PDF
        $pdfService = new InvoicePdfService($this->db);
        $pdfContent = $pdfService->generatePdf($invoiceId);

        // Sende PDF als Download
        $response->getBody()->write($pdfContent);
        return $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'attachment; filename="Rechnung_' . $invoice['invoice_number'] . '.pdf"')
            ->withHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->withHeader('Pragma', 'public');
    }

    /**
     * Rechnung per E-Mail versenden
     */
    public function sendEmail(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        $invoiceId = (int) $args['id'];
        
        // Hole Rechnung
        $invoice = $this->db->fetchOne('SELECT * FROM bm_invoices WHERE id = ? AND tenant_id = ?', [$invoiceId, $user['tenant_id'] ?? 1]);
        if (!$invoice) {
            $_SESSION['error'] = 'Rechnung nicht gefunden';
            return $response->withHeader('Location', '/invoices')->withStatus(302);
        }

        // Hole Kunden-E-Mail
        $customer = $this->db->fetchOne('SELECT email, company_name FROM bm_customers WHERE id = ?', [$invoice['customer_id']]);
        if (!$customer || empty($customer['email'])) {
            $_SESSION['error'] = 'Kunde hat keine E-Mail-Adresse hinterlegt';
            return $response->withHeader('Location', '/invoices/' . $invoiceId)->withStatus(302);
        }

        try {
            // PDF generieren
            $pdfService = new InvoicePdfService($this->db);
            $pdfContent = $pdfService->generatePdf($invoiceId);
            
            // PDF temporär speichern
            $tempPdfPath = sys_get_temp_dir() . '/invoice_' . $invoice['invoice_number'] . '.pdf';
            file_put_contents($tempPdfPath, $pdfContent);

            // E-Mail-Template rendern
            $companyName = 'Business Manager'; // TODO: Aus Settings laden
            ob_start();
            $statusLabels = [
                'draft' => 'Entwurf',
                'sent' => 'Versendet',
                'paid' => 'Bezahlt',
                'overdue' => 'Überfällig',
                'cancelled' => 'Storniert'
            ];
            $getStatusLabel = function($status) use ($statusLabels) {
                return $statusLabels[$status] ?? $status;
            };
            $this->getStatusLabel = $getStatusLabel;
            require __DIR__ . '/../../resources/views/emails/invoice.php';
            $emailBody = ob_get_clean();

            // E-Mail versenden
            $mailConfig = require __DIR__ . '/../../config/mail.php';
            $mailService = new MailService($mailConfig);
            
            $success = $mailService->send(
                $customer['email'],
                'Rechnung ' . $invoice['invoice_number'],
                $emailBody,
                [
                    [
                        'path' => $tempPdfPath,
                        'name' => 'Rechnung_' . $invoice['invoice_number'] . '.pdf'
                    ]
                ]
            );

            // Temp-Datei löschen
            unlink($tempPdfPath);

            if ($success) {
                // Status auf "sent" setzen, falls noch "draft"
                if ($invoice['status'] === 'draft') {
                    $this->db->update('bm_invoices', [
                        'status' => 'sent',
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$invoiceId]);
                }

                $_SESSION['success'] = 'Rechnung wurde erfolgreich an ' . $customer['email'] . ' versendet';
            } else {
                $_SESSION['error'] = 'E-Mail konnte nicht versendet werden. Bitte prüfen Sie die E-Mail-Konfiguration.';
            }

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler beim E-Mail-Versand: ' . $e->getMessage();
        }

        return $response->withHeader('Location', '/invoices/' . $invoiceId)->withStatus(302);
    }
    
    private function getStatusLabel(string $status): string
    {
        $labels = [
            'draft' => 'Entwurf',
            'sent' => 'Versendet',
            'paid' => 'Bezahlt',
            'overdue' => 'Überfällig',
            'cancelled' => 'Storniert'
        ];
        return $labels[$status] ?? $status;
    }
}
