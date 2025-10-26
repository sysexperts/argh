<?php
/**
 * Invoice PDF Service
 * 
 * @package SysExperts\BusinessManager\Invoices
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Invoices;

use TCPDF;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Settings\SettingsController;

class InvoicePdfService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Generiere PDF für Rechnung
     */
    public function generatePdf(int $invoiceId): string
    {
        // Hole Rechnung
        $invoice = $this->db->fetchOne('SELECT * FROM bm_invoices WHERE id = ?', [$invoiceId]);
        if (!$invoice) {
            throw new \Exception('Rechnung nicht gefunden');
        }

        // Hole Positionen
        $items = $this->db->fetchAll('SELECT * FROM bm_invoice_items WHERE invoice_id = ? ORDER BY id', [$invoiceId]);

        // Hole Einstellungen
        $settings = $this->getSettings();

        // Erstelle PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Dokument-Informationen
        $pdf->SetCreator('Business Manager');
        $pdf->SetAuthor($settings['company_name']);
        $pdf->SetTitle('Rechnung ' . $invoice['invoice_number']);
        
        // Header/Footer ausblenden
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Margins
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 25);
        
        // Seite hinzufügen
        $pdf->AddPage();
        
        // Font
        $pdf->SetFont('helvetica', '', 10);
        
        // Firmen-Logo/Name (oben links)
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $settings['company_name'], 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 4, $settings['company_street'], 0, 1, 'L');
        $pdf->Cell(0, 4, $settings['company_zip'] . ' ' . $settings['company_city'], 0, 1, 'L');
        $pdf->Cell(0, 4, 'Tel: ' . $settings['company_phone'], 0, 1, 'L');
        $pdf->Cell(0, 4, 'E-Mail: ' . $settings['company_email'], 0, 1, 'L');
        
        $pdf->Ln(10);
        
        // Kundenadresse
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, $invoice['customer_name'], 0, 1, 'L');
        if ($invoice['customer_address']) {
            $addressLines = explode("\n", $invoice['customer_address']);
            foreach ($addressLines as $line) {
                $pdf->Cell(0, 5, trim($line), 0, 1, 'L');
            }
        }
        
        $pdf->Ln(15);
        
        // Rechnungstitel
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Rechnung ' . $invoice['invoice_number'], 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 6, 'Rechnungsdatum:', 0, 0, 'L');
        $pdf->Cell(0, 6, date('d.m.Y', strtotime($invoice['invoice_date'])), 0, 1, 'L');
        $pdf->Cell(50, 6, 'Fälligkeitsdatum:', 0, 0, 'L');
        $pdf->Cell(0, 6, date('d.m.Y', strtotime($invoice['due_date'])), 0, 1, 'L');
        
        $pdf->Ln(10);
        
        // Positionen-Tabelle (Breite angepasst für A4: 170mm nutzbar)
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(65, 7, 'Beschreibung', 1, 0, 'L', true);
        $pdf->Cell(18, 7, 'Menge', 1, 0, 'R', true);
        $pdf->Cell(20, 7, 'Einheit', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Einzelpreis', 1, 0, 'R', true);
        $pdf->Cell(12, 7, 'MwSt', 1, 0, 'R', true);
        $pdf->Cell(30, 7, 'Gesamt', 1, 1, 'R', true);
        
        $pdf->SetFont('helvetica', '', 8);
        foreach ($items as $item) {
            $pdf->Cell(65, 6, $item['description'], 1, 0, 'L');
            $pdf->Cell(18, 6, number_format((float)$item['quantity'], 2, ',', '.'), 1, 0, 'R');
            $pdf->Cell(20, 6, $item['unit'] ?? 'Stück', 1, 0, 'C');
            $pdf->Cell(25, 6, number_format((float)$item['unit_price'], 2, ',', '.') . ' €', 1, 0, 'R');
            $pdf->Cell(12, 6, number_format((float)($item['tax_rate'] ?? 19), 0) . '%', 1, 0, 'R');
            $pdf->Cell(30, 6, number_format((float)$item['total'], 2, ',', '.') . ' €', 1, 1, 'R');
        }
        
        $pdf->Ln(5);
        
        // Summen (angepasst an Tabellenbreite: 170mm)
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(128, 6, '', 0, 0, 'L');
        $pdf->Cell(12, 6, 'Netto:', 0, 0, 'L');
        $pdf->Cell(30, 6, number_format((float)$invoice['subtotal'], 2, ',', '.') . ' €', 0, 1, 'R');
        
        $pdf->Cell(128, 6, '', 0, 0, 'L');
        $pdf->Cell(12, 6, 'MwSt:', 0, 0, 'L');
        $pdf->Cell(30, 6, number_format((float)$invoice['tax_amount'], 2, ',', '.') . ' €', 0, 1, 'R');
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(128, 8, '', 0, 0, 'L');
        $pdf->Cell(12, 8, 'Gesamt:', 0, 0, 'L');
        $pdf->Cell(30, 8, number_format((float)$invoice['total'], 2, ',', '.') . ' €', 0, 1, 'R');
        
        $pdf->Ln(10);
        
        // Bankverbindung
        if ($settings['bank_iban']) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'Bankverbindung:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, 'Bank: ' . $settings['bank_name'], 0, 1, 'L');
            $pdf->Cell(0, 5, 'IBAN: ' . $settings['bank_iban'], 0, 1, 'L');
            $pdf->Cell(0, 5, 'BIC: ' . $settings['bank_bic'], 0, 1, 'L');
        }
        
        $pdf->Ln(5);
        
        // Footer-Text
        if ($invoice['notes']) {
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->MultiCell(0, 5, $invoice['notes'], 0, 'L');
        }
        
        if ($settings['invoice_footer']) {
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->MultiCell(0, 4, $settings['invoice_footer'], 0, 'C');
        }
        
        // Firmeninfo am Ende
        $pdf->SetY(-30);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(128, 128, 128);
        $companyInfo = $settings['company_name'] . ' | ' . $settings['company_street'] . ' | ' . 
                       $settings['company_zip'] . ' ' . $settings['company_city'];
        if ($settings['company_tax_id']) {
            $companyInfo .= ' | Steuernr: ' . $settings['company_tax_id'];
        }
        if ($settings['company_vat_id']) {
            $companyInfo .= ' | USt-IdNr: ' . $settings['company_vat_id'];
        }
        $pdf->MultiCell(0, 3, $companyInfo, 0, 'C');
        
        // PDF als String zurückgeben
        return $pdf->Output('', 'S');
    }

    /**
     * Hole alle Einstellungen
     */
    private function getSettings(): array
    {
        $settingsRows = $this->db->fetchAll('SELECT * FROM bm_settings');
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
}
