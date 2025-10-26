<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechnung <?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #14b8a6;
        }
        .header h1 {
            color: #14b8a6;
            margin: 0;
            font-size: 28px;
        }
        .invoice-info {
            background-color: #f0fdfa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 30px;
        }
        .invoice-info p {
            margin: 5px 0;
        }
        .invoice-info strong {
            color: #14b8a6;
        }
        .message {
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .amount {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: white;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
            margin: 30px 0;
        }
        .amount .label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .amount .value {
            font-size: 32px;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Neue Rechnung</h1>
        </div>

        <div class="message">
            <p>Sehr geehrte Damen und Herren,</p>
            <p>anbei erhalten Sie die Rechnung <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong> als PDF-Anhang.</p>
        </div>

        <div class="invoice-info">
            <p><strong>Rechnungsnummer:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?></p>
            <p><strong>Rechnungsdatum:</strong> <?= date('d.m.Y', strtotime($invoice['invoice_date'])) ?></p>
            <p><strong>Fälligkeitsdatum:</strong> <?= date('d.m.Y', strtotime($invoice['due_date'])) ?></p>
            <p><strong>Status:</strong> <?= $this->getStatusLabel($invoice['status']) ?></p>
        </div>

        <div class="amount">
            <div class="label">Rechnungsbetrag</div>
            <div class="value">€<?= number_format($invoice['total'], 2, ',', '.') ?></div>
        </div>

        <div class="message">
            <p>Bitte überweisen Sie den Betrag bis zum <strong><?= date('d.m.Y', strtotime($invoice['due_date'])) ?></strong> auf das in der Rechnung angegebene Konto.</p>
            <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
        </div>

        <div class="footer">
            <p>Mit freundlichen Grüßen<br>
            <strong><?= htmlspecialchars($companyName ?? 'Business Manager') ?></strong></p>
            <p style="margin-top: 20px;">
                Diese E-Mail wurde automatisch generiert.<br>
                Bitte antworten Sie nicht direkt auf diese E-Mail.
            </p>
        </div>
    </div>
</body>
</html>
