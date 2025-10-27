<?php
// Invoices View
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="bg-gradient-to-r from-primary via-blue-500 to-indigo-500 rounded-2xl p-8 shadow-2xl">
        <div class="flex items-center justify-between">
            <div class="text-white">
                <h1 class="text-3xl font-bold mb-2">Rechnungen 📄</h1>
                <p class="text-white/90 text-lg">Verwalten Sie Ihre Rechnungen</p>
            </div>
            <div class="hidden lg:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                    <span class="material-symbols-outlined text-6xl text-white">receipt_long</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="mb-6 flex justify-end">
    <button onclick="alert('Neue Rechnung erstellen - Feature kommt bald!')" 
            class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary/90 transition flex items-center gap-2">
        <span class="material-symbols-outlined">add</span>
        Neue Rechnung
    </button>
</div>

<!-- Success Message -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg flex items-center justify-between">
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

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <?php
    $totalInvoices = count($invoices);
    $draftCount = count(array_filter($invoices, fn($i) => $i['status'] === 'draft'));
    $paidCount = count(array_filter($invoices, fn($i) => $i['status'] === 'paid'));
    $totalAmount = array_sum(array_column($invoices, 'total'));
    ?>
    
    <div class="bg-card-light dark:bg-card-dark p-4 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-text-muted-light dark:text-text-muted-dark">Gesamt</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark"><?= $totalInvoices ?></p>
            </div>
            <span class="material-symbols-outlined text-3xl text-primary">receipt_long</span>
        </div>
    </div>

    <div class="bg-card-light dark:bg-card-dark p-4 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-text-muted-light dark:text-text-muted-dark">Entwürfe</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark"><?= $draftCount ?></p>
            </div>
            <span class="material-symbols-outlined text-3xl text-orange-500">draft</span>
        </div>
    </div>

    <div class="bg-card-light dark:bg-card-dark p-4 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-text-muted-light dark:text-text-muted-dark">Bezahlt</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark"><?= $paidCount ?></p>
            </div>
            <span class="material-symbols-outlined text-3xl text-green-500">check_circle</span>
        </div>
    </div>

    <div class="bg-card-light dark:bg-card-dark p-4 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-text-muted-light dark:text-text-muted-dark">Gesamtsumme</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">€<?= number_format($totalAmount, 2, ',', '.') ?></p>
            </div>
            <span class="material-symbols-outlined text-3xl text-primary">euro</span>
        </div>
    </div>
</div>

<!-- Invoices Table -->
<div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-background-light dark:bg-background-dark">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Rechnungsnr.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Kunde</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Datum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Fällig</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Betrag</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Aktionen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-text-muted-light dark:text-text-muted-dark">
                            <span class="material-symbols-outlined text-6xl mb-4 block opacity-20">receipt_long</span>
                            <p class="text-lg font-medium mb-2">Keine Rechnungen vorhanden</p>
                            <p class="text-sm">Erstellen Sie Ihre erste Rechnung</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr class="hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="/invoices/<?= $invoice['id'] ?>" class="font-mono text-sm font-semibold text-primary hover:text-primary/80 transition-colors">
                                    <?= htmlspecialchars($invoice['invoice_number']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-text-light dark:text-text-dark">
                                    <?= htmlspecialchars($invoice['customer_name']) ?>
                                </div>
                                <?php if ($invoice['customer_email']): ?>
                                    <div class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                        <?= htmlspecialchars($invoice['customer_email']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-light dark:text-text-dark">
                                <?= date('d.m.Y', strtotime($invoice['invoice_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-light dark:text-text-dark">
                                <?= date('d.m.Y', strtotime($invoice['due_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-text-light dark:text-text-dark">
                                    €<?= number_format($invoice['total'], 2, ',', '.') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    'sent' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                    'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'overdue' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                    'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                ];
                                $statusLabels = [
                                    'draft' => 'Entwurf',
                                    'sent' => 'Versendet',
                                    'paid' => 'Bezahlt',
                                    'overdue' => 'Überfällig',
                                    'cancelled' => 'Storniert',
                                ];
                                $statusClass = $statusColors[$invoice['status']] ?? $statusColors['draft'];
                                $statusLabel = $statusLabels[$invoice['status']] ?? $invoice['status'];
                                ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="/invoices/<?= $invoice['id'] ?>" class="text-primary hover:text-primary/80" title="Details">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </a>
                                    <?php if ($invoice['status'] === 'draft'): ?>
                                        <form method="POST" action="/invoices/<?= $invoice['id'] ?>/status" class="inline">
                                            <input type="hidden" name="status" value="sent">
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 dark:text-blue-400" title="Versenden">
                                                <span class="material-symbols-outlined">send</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($invoice['status'] !== 'paid'): ?>
                                        <form method="POST" action="/invoices/<?= $invoice['id'] ?>/status" class="inline">
                                            <input type="hidden" name="status" value="paid">
                                            <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400" title="Als bezahlt markieren">
                                                <span class="material-symbols-outlined">check_circle</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="/invoices/<?= $invoice['id'] ?>/delete" class="inline" onsubmit="return confirm('Rechnung wirklich löschen?')">
                                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400" title="Löschen">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Invoice Modal -->
<div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div @click="showCreateModal = false" class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <div class="relative bg-card-light dark:bg-card-dark rounded-lg max-w-2xl w-full p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Neue Rechnung</h3>
                <button @click="showCreateModal = false" class="text-text-muted-light dark:text-text-muted-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="/invoices" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Kunde auswählen *</label>
                    <select name="customer_id" x-model="selectedCustomer" @change="if(selectedCustomer) { const c = customers.find(cu => cu.id == selectedCustomer); if(c) { $refs.customerName.value = c.company_name; $refs.customerEmail.value = c.email || ''; $refs.customerAddress.value = (c.street ? c.street + '\\n' : '') + (c.zip && c.city ? c.zip + ' ' + c.city : ''); } }" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="">-- Kunde wählen --</option>
                        <template x-for="customer in customers" :key="customer.id">
                            <option :value="customer.id" x-text="customer.company_name"></option>
                        </template>
                    </select>
                    <p class="text-xs text-text-muted-light dark:text-text-muted-dark mt-1">Oder <a href="/customers" class="text-primary hover:underline">neuen Kunden anlegen</a></p>
                </div>

                <input type="hidden" name="customer_name" x-ref="customerName">
                <input type="hidden" name="customer_email" x-ref="customerEmail">
                <input type="hidden" name="customer_address" x-ref="customerAddress">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Rechnungsdatum</label>
                        <input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Fälligkeitsdatum</label>
                        <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <input type="hidden" name="tax_rate" value="19">

                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Notizen</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary"></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showCreateModal = false" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                        Abbrechen
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-primary to-teal-600 text-white rounded-lg hover:from-primary/90 hover:to-teal-600/90">
                        Erstellen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Rechnungen';
$title = 'Rechnungen - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
