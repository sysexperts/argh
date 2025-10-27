<?php
// Settings View
ob_start();
?>

<!-- Header -->
<div class="mb-6">
    <h2 class="text-2xl font-bold text-text-light dark:text-text-dark">Einstellungen</h2>
    <p class="text-sm text-text-muted-light dark:text-text-muted-dark mt-1">
        Verwalten Sie Ihre Firmen- und System-Einstellungen
    </p>
</div>

<!-- Tabs -->
<div class="mb-6">
    <div class="border-b border-border-light dark:border-border-dark">
        <nav class="flex gap-2 overflow-x-auto">
            <button onclick="switchTab('company')" id="tab-company" class="settings-tab active flex items-center gap-2 px-4 py-3 border-b-2 border-primary text-primary font-medium whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">business</span>
                Firma
            </button>
            <button onclick="switchTab('tax')" id="tab-tax" class="settings-tab flex items-center gap-2 px-4 py-3 border-b-2 border-transparent text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">receipt</span>
                Steuer
            </button>
            <button onclick="switchTab('invoice')" id="tab-invoice" class="settings-tab flex items-center gap-2 px-4 py-3 border-b-2 border-transparent text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">receipt_long</span>
                Rechnungen
            </button>
            <button onclick="switchTab('updates')" id="tab-updates" class="settings-tab flex items-center gap-2 px-4 py-3 border-b-2 border-transparent text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">system_update</span>
                Updates
            </button>
            <button onclick="switchTab('email')" id="tab-email" class="settings-tab flex items-center gap-2 px-4 py-3 border-b-2 border-transparent text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">email</span>
                E-Mail
            </button>
        </nav>
    </div>
</div>

<!-- Success Message -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            <p class="text-sm"><?= htmlspecialchars($_SESSION['success']) ?></p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-600 dark:text-green-400">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<form method="POST" action="/settings/update" class="space-y-6">
    
    <!-- Tab: Firmeninformationen -->
    <div id="content-company" class="tab-content">
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">business</span>
            Firmeninformationen
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Firmenname *</label>
                <input type="text" name="company_name" value="<?= htmlspecialchars($settings['company_name'] ?? '') ?>" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Straße & Hausnummer</label>
                <input type="text" name="company_street" value="<?= htmlspecialchars($settings['company_street'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">PLZ</label>
                <input type="text" name="company_zip" value="<?= htmlspecialchars($settings['company_zip'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Stadt</label>
                <input type="text" name="company_city" value="<?= htmlspecialchars($settings['company_city'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Land</label>
                <input type="text" name="company_country" value="<?= htmlspecialchars($settings['company_country'] ?? 'Deutschland') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Telefon</label>
                <input type="text" name="company_phone" value="<?= htmlspecialchars($settings['company_phone'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">E-Mail</label>
                <input type="email" name="company_email" value="<?= htmlspecialchars($settings['company_email'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Website</label>
                <input type="text" name="company_website" value="<?= htmlspecialchars($settings['company_website'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Steuernummer</label>
                <input type="text" name="company_tax_id" value="<?= htmlspecialchars($settings['company_tax_id'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">USt-IdNr.</label>
                <input type="text" name="company_vat_id" value="<?= htmlspecialchars($settings['company_vat_id'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
        </div>
    </div>
    </div>

    <!-- Tab: Steuer & Bankverbindung -->
    <div id="content-tax" class="tab-content hidden">
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">receipt</span>
            Steuerinformationen
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Steuernummer</label>
                <input type="text" name="company_tax_id" value="<?= htmlspecialchars($settings['company_tax_id'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">USt-IdNr.</label>
                <input type="text" name="company_vat_id" value="<?= htmlspecialchars($settings['company_vat_id'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
        </div>
        <hr class="border-border-light dark:border-border-dark my-6">
    <!-- Bankverbindung -->
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">account_balance</span>
            Bankverbindung
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Bankname</label>
                <input type="text" name="bank_name" value="<?= htmlspecialchars($settings['bank_name'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">IBAN</label>
                <input type="text" name="bank_iban" value="<?= htmlspecialchars($settings['bank_iban'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">BIC</label>
                <input type="text" name="bank_bic" value="<?= htmlspecialchars($settings['bank_bic'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
        </div>
    </div>
    </div>

    <!-- Tab: Rechnungseinstellungen -->
    <div id="content-invoice" class="tab-content hidden">
    <!-- Rechnungseinstellungen -->
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">receipt_long</span>
            Rechnungseinstellungen
        </h3>
        
        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Rechnungsnummern-Präfix</label>
                <input type="text" name="invoice_prefix" value="<?= htmlspecialchars($settings['invoice_prefix'] ?? 'RE') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary" placeholder="z.B. RE, INV">
                <p class="text-xs text-text-muted-light dark:text-text-muted-dark mt-1">Format: RE-2025-0001</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Rechnungs-Fußzeile</label>
                <textarea name="invoice_footer" rows="3" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary"><?= htmlspecialchars($settings['invoice_footer'] ?? '') ?></textarea>
                <p class="text-xs text-text-muted-light dark:text-text-muted-dark mt-1">Wird am Ende jeder Rechnung angezeigt</p>
            </div>
        </div>
    </div>
    </div>

    <!-- Tab: Updates -->
    <div id="content-updates" class="tab-content hidden">
        <!-- Update-Einstellungen -->
        <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4">Update-Einstellungen</h3>
            <div class="space-y-4">
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="auto_update_enabled" value="1" <?= !empty($settings['auto_update_enabled']) ? 'checked' : '' ?> class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <span class="text-sm text-text-light dark:text-text-dark font-medium">Automatische Updates aktivieren</span>
                    </label>
                    <p class="text-xs text-text-muted-light dark:text-text-muted-dark mt-1 ml-6">
                        Feature-Updates werden automatisch installiert (Sicherheitsupdates immer)
                    </p>
                </div>
                
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="update_notifications" value="1" <?= !empty($settings['update_notifications']) ? 'checked' : '' ?> checked class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <span class="text-sm text-text-light dark:text-text-dark font-medium">E-Mail-Benachrichtigungen bei Updates</span>
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Update-Zeitfenster</label>
                    <select name="update_time_window" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="immediate" <?= ($settings['update_time_window'] ?? '') === 'immediate' ? 'selected' : '' ?>>Sofort installieren</option>
                        <option value="night" <?= ($settings['update_time_window'] ?? '') === 'night' ? 'selected' : '' ?>>Nachts (02:00 - 05:00 Uhr)</option>
                        <option value="weekend" <?= ($settings['update_time_window'] ?? '') === 'weekend' ? 'selected' : '' ?>>Am Wochenende</option>
                        <option value="manual" <?= ($settings['update_time_window'] ?? 'manual') === 'manual' ? 'selected' : '' ?>>Manuell</option>
                    </select>
                    <p class="text-xs text-text-muted-light dark:text-text-muted-dark mt-1">
                        Wann sollen Updates installiert werden?
                    </p>
                </div>
                
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-lg">info</span>
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Wichtig:</strong> Sicherheitsupdates sind verpflichtend und werden unabhängig von diesen Einstellungen installiert.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Schnellzugriff: Zur Update-Seite -->
        <div class="bg-gradient-to-r from-primary/10 to-teal-500/10 border border-primary/20 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-2">Updates verwalten</h3>
                    <p class="text-sm text-text-muted-light dark:text-text-muted-dark">
                        Verfügbare Updates anzeigen und installieren
                    </p>
                </div>
                <a href="/updates" class="flex items-center gap-2 bg-gradient-to-r from-primary to-teal-600 hover:from-primary/90 hover:to-teal-600/90 text-white px-6 py-3 rounded-lg font-semibold shadow-md hover:shadow-lg transition-all">
                    <span class="material-symbols-outlined">system_update</span>
                    Zu Updates
                </a>
            </div>
        </div>
    </div>

    <!-- Tab: E-Mail Einstellungen -->
    <div id="content-email" class="tab-content hidden">
    <!-- E-Mail Einstellungen -->
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">email</span>
            E-Mail Einstellungen
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Absender-Name</label>
                <input type="text" name="email_from_name" value="<?= htmlspecialchars($settings['email_from_name'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Absender-E-Mail</label>
                <input type="email" name="email_from_address" value="<?= htmlspecialchars($settings['email_from_address'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
            </div>
        </div>
    </div>
    </div>

    <!-- Speichern Button -->
    <div class="flex justify-end">
        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary to-teal-600 hover:from-primary/90 hover:to-teal-600/90 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all">
            <span class="flex items-center gap-2">
                <span class="material-symbols-outlined">save</span>
                Einstellungen speichern
            </span>
        </button>
    </div>
</form>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.settings-tab').forEach(el => {
        el.classList.remove('active', 'border-primary', 'text-primary');
        el.classList.add('border-transparent', 'text-text-muted-light', 'dark:text-text-muted-dark');
    });
    document.getElementById('content-' + tabName).classList.remove('hidden');
    const btn = document.getElementById('tab-' + tabName);
    btn.classList.add('active', 'border-primary', 'text-primary');
    btn.classList.remove('border-transparent', 'text-text-muted-light', 'dark:text-text-muted-dark');
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Einstellungen';
$title = 'Einstellungen - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
