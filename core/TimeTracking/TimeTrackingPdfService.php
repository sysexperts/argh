<?php
declare(strict_types=1);

namespace SysExperts\BusinessManager\TimeTracking;

use TCPDF;
use SysExperts\BusinessManager\Auth\User;

class TimeTrackingPdfService
{
    public function generateReport(array $entries, User $user, string $startDate, string $endDate): string
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

        $pdf->SetCreator('Business Manager');
        $pdf->SetAuthor('sys-experts.de');
        $pdf->SetTitle('Zeiterfassung');
        $pdf->SetSubject('Arbeitszeitnachweis');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Arbeitszeitnachweis', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'Mitarbeiter: ' . $user->getFullName(), 0, 1);
        $pdf->Cell(0, 6, 'Zeitraum: ' . date('d.m.Y', strtotime($startDate)) . ' - ' . date('d.m.Y', strtotime($endDate)), 0, 1);
        $pdf->Cell(0, 6, 'Erstellt am: ' . date('d.m.Y H:i'), 0, 1);

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(25, 7, 'Datum', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Beginn', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Ende', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Pausen', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Gesamt', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Überstd.', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Status', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $totalHours = 0;
        $totalOvertime = 0;

        foreach ($entries as $entry) {
            $breakMinutes = 0;
            foreach ($entry->getBreaks() as $break) {
                if ($break['duration_minutes']) {
                    $breakMinutes += $break['duration_minutes'];
                }
            }

            $pdf->Cell(25, 6, date('d.m.Y', strtotime($entry->getDate())), 1, 0, 'C');
            $pdf->Cell(25, 6, date('H:i', strtotime($entry->getStartTime())), 1, 0, 'C');
            $pdf->Cell(25, 6, $entry->getEndTime() ? date('H:i', strtotime($entry->getEndTime())) : '-', 1, 0, 'C');
            $pdf->Cell(20, 6, $breakMinutes > 0 ? $breakMinutes . ' min' : '-', 1, 0, 'C');
            $pdf->Cell(25, 6, $entry->getTotalHours() ? number_format($entry->getTotalHours(), 2) . ' h' : '-', 1, 0, 'C');
            $pdf->Cell(25, 6, number_format($entry->getOvertimeHours(), 2) . ' h', 1, 0, 'C');

            $statusText = match($entry->getStatus()) {
                'active' => 'Aktiv',
                'paused' => 'Pausiert',
                'completed' => 'Abgeschlossen',
                default => $entry->getStatus()
            };
            $pdf->Cell(35, 6, $statusText, 1, 1, 'C');

            if ($entry->getTotalHours()) {
                $totalHours += $entry->getTotalHours();
                $totalOvertime += $entry->getOvertimeHours();
            }
        }

        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(120, 7, 'Summe:', 0, 0, 'R');
        $pdf->Cell(25, 7, number_format($totalHours, 2) . ' h', 1, 0, 'C');
        $pdf->Cell(25, 7, number_format($totalOvertime, 2) . ' h', 1, 1, 'C');

        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'Dieser Nachweis wurde maschinell erstellt und ist ohne Unterschrift gültig.', 0, 1);
        $pdf->Cell(0, 5, 'Gemäß § 16 Abs. 2 ArbZG müssen Arbeitszeiten dokumentiert und mindestens 2 Jahre aufbewahrt werden.', 0, 1);

        return $pdf->Output('', 'S');
    }
}
