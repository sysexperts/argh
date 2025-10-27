<?php
// Invoice Detail View
ob_start();
?>

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <div>
        <div class="flex items-center gap-3">
            <a href="/invoices" class="text-text-muted-light dark:text-text-muted-dark hover:text-primary">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Rechnung <?= htmlspecialchars($invoice['invoice_number']) ?>
            </h2>
            <?php
            $statusColors = [
                'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                'sent' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                'overdue' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
            ];
            $statusLabels = [
                'draft' => 'Entwurf',
                'sent' => 'Versendet',
                'paid' => 'Bezahlt',
                'overdue' => 'Überfällig',
            ];
            $statusClass = $statusColors[$invoice['status']] ?? $statusColors['draft'];
            $statusLabel = $statusLabels[$invoice['status']] ?? $invoice['status'];
            ?>
            <span class="px-3 py-1 text-sm font-semibold rounded-full <?= $statusClass ?>">
                <?= $statusLabel ?>
            </span>
        </div>
    </div>
    <div class="flex gap-2">
        <!-- E-Mail versenden -->
        <form method="POST" action="/invoices/<?= $invoice['id'] ?>/send-email" class="inline">
            <button type="submit" class="flex items-center gap-2 bg-primary hover:bg-teal-600 text-white px-4 py-2 rounded-lg transition-colors">
                <span class="material-symbols-outlined">email</span>
                <span>Per E-Mail senden</span>
            </button>
        </form>
        
        <!-- PDF Download -->
        <a href="/invoices/<?= $invoice['id'] ?>/pdf" target="_blank" class="flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
            <span class="material-symbols-outlined">picture_as_pdf</span>
            <span>PDF</span>
        </a>
        
        <?php if ($invoice['status'] === 'draft'): ?>
            <form method="POST" action="/invoices/<?= $invoice['id'] ?>/status" class="inline">
                <input type="hidden" name="status" value="sent">
                <button type="submit" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">send</span>
                    <span>Versenden</span>
                </button>
            </form>
        <?php endif; ?>
        <?php if ($invoice['status'] !== 'paid'): ?>
            <form method="POST" action="/invoices/<?= $invoice['id'] ?>/status" class="inline">
                <input type="hidden" name="status" value="paid">
                <button type="submit" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span>Als bezahlt markieren</span>
                </button>
            </form>
        <?php endif; ?>
    </div>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Customer Info -->
        <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4">Kundeninformationen</h3>
            <div class="space-y-2">
                <div>
                    <span class="text-sm text-text-muted-light dark:text-text-muted-dark">Name:</span>
                    <span class="ml-2 text-sm font-medium text-text-light dark:text-text-dark"><?= htmlspecialchars($invoice['customer_name']) ?></span>
                </div>
                <?php if ($invoice['customer_email']): ?>
                    <div>
                        <span class="text-sm text-text-muted-light dark:text-text-muted-dark">E-Mail:</span>
                        <span class="ml-2 text-sm font-medium text-text-light dark:text-text-dark"><?= htmlspecialchars($invoice['customer_email']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($invoice['customer_address']): ?>
                    <div>
                        <span class="text-sm text-text-muted-light dark:text-text-muted-dark">Adresse:</span>
                        <p class="mt-1 text-sm text-text-light dark:text-text-dark whitespace-pre-line"><?= htmlspecialchars($invoice['customer_address']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Rechnungspositionen</h3>
                <?php if ($invoice['status'] === 'draft'): ?>
                    <button @click="showAddItemModal = true" class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span>Position hinzufügen</span>
                    </button>
                <?php endif; ?>
            </div>

            <?php if (empty($items)): ?>
                <div class="text-center py-8 text-text-muted-light dark:text-text-muted-dark">
                    <span class="material-symbols-outlined text-4xl mb-2 block opacity-20">inventory_2</span>
                    <p class="text-sm">Keine Positionen vorhanden</p>
                    <?php if ($invoice['status'] === 'draft'): ?>
                        <button @click="showAddItemModal = true" class="mt-3 text-primary hover:text-primary/80 text-sm font-medium">
                            Erste Position hinzufügen
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-background-light dark:bg-background-dark">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Beschreibung</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Menge</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Einheit</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Einzelpreis</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">MwSt.</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Gesamt</th>
                                <?php if ($invoice['status'] === 'draft'): ?>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Aktion</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-light dark:divide-border-dark">
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark"><?= htmlspecialchars($item['description']) ?></td>
                                    <td class="px-4 py-3 text-sm text-right text-text-light dark:text-text-dark"><?= number_format($item['quantity'], 2, ',', '.') ?></td>
                                    <td class="px-4 py-3 text-sm text-right text-text-muted-light dark:text-text-muted-dark"><?= htmlspecialchars($item['unit'] ?? 'Stück') ?></td>
                                    <td class="px-4 py-3 text-sm text-right text-text-light dark:text-text-dark">€<?= number_format($item['unit_price'], 2, ',', '.') ?></td>
                                    <td class="px-4 py-3 text-sm text-right text-text-muted-light dark:text-text-muted-dark"><?= number_format($item['tax_rate'] ?? 19, 0) ?>%</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-text-light dark:text-text-dark">€<?= number_format($item['total'], 2, ',', '.') ?></td>
                                    <?php if ($invoice['status'] === 'draft'): ?>
                                        <td class="px-4 py-3 text-right">
                                            <form method="POST" action="/invoices/<?= $invoice['id'] ?>/items/<?= $item['id'] ?>/delete" class="inline" onsubmit="return confirm('Position wirklich löschen?')">
                                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Notes -->
        <?php if ($invoice['notes']): ?>
            <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-3">Notizen</h3>
                <p class="text-sm text-text-muted-light dark:text-text-muted-dark whitespace-pre-line"><?= htmlspecialchars($invoice['notes']) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Summary -->
        <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4">Zusammenfassung</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-text-muted-light dark:text-text-muted-dark">Rechnungsdatum:</span>
                    <span class="font-medium text-text-light dark:text-text-dark"><?= date('d.m.Y', strtotime($invoice['invoice_date'])) ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-text-muted-light dark:text-text-muted-dark">Fällig am:</span>
                    <span class="font-medium text-text-light dark:text-text-dark"><?= date('d.m.Y', strtotime($invoice['due_date'])) ?></span>
                </div>
                <?php if ($invoice['paid_at']): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-text-muted-light dark:text-text-muted-dark">Bezahlt am:</span>
                        <span class="font-medium text-green-600 dark:text-green-400"><?= date('d.m.Y', strtotime($invoice['paid_at'])) ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="border-t border-border-light dark:border-border-dark pt-3 mt-3">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-text-muted-light dark:text-text-muted-dark">Nettobetrag:</span>
                        <span class="font-medium text-text-light dark:text-text-dark">€<?= number_format($invoice['subtotal'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-text-muted-light dark:text-text-muted-dark">MwSt. (<?= number_format($invoice['tax_rate'], 0) ?>%):</span>
                        <span class="font-medium text-text-light dark:text-text-dark">€<?= number_format($invoice['tax_amount'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-border-light dark:border-border-dark">
                        <span class="text-text-light dark:text-text-dark">Gesamtbetrag:</span>
                        <span class="text-primary">€<?= number_format($invoice['total'], 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4">Aktionen</h3>
            <div class="space-y-2">
                <a href="/invoices/<?= $invoice['id'] ?>/pdf" target="_blank" class="w-full flex items-center gap-2 px-4 py-2 bg-background-light dark:bg-background-dark hover:bg-border-light dark:hover:bg-border-dark rounded-lg text-sm text-text-light dark:text-text-dark transition-colors">
                    <span class="material-symbols-outlined">picture_as_pdf</span>
                    <span>Als PDF exportieren</span>
                </a>
                <button class="w-full flex items-center gap-2 px-4 py-2 bg-background-light dark:bg-background-dark hover:bg-border-light dark:hover:bg-border-dark rounded-lg text-sm text-text-light dark:text-text-dark transition-colors">
                    <span class="material-symbols-outlined">email</span>
                    <span>Per E-Mail versenden</span>
                </button>
                <form method="POST" action="/invoices/<?= $invoice['id'] ?>/delete" class="w-full" onsubmit="return confirm('Rechnung wirklich löschen?')">
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg text-sm text-red-600 dark:text-red-400 transition-colors">
                        <span class="material-symbols-outlined">delete</span>
                        <span>Rechnung löschen</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div x-show="showAddItemModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div @click="showAddItemModal = false" class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <div class="relative bg-card-light dark:bg-card-dark rounded-lg max-w-3xl w-full p-8 shadow-xl">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-text-light dark:text-text-dark">Position hinzufügen</h3>
                    <p class="text-sm text-text-muted-light dark:text-text-muted-dark mt-1">Fügen Sie eine neue Position zur Rechnung hinzu</p>
                </div>
                <button @click="showAddItemModal = false" class="text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>
            </div>

            <form method="POST" action="/invoices/<?= $invoice['id'] ?>/items" class="space-y-6" x-data="{ 
                description: '', 
                quantity: 1, 
                unit: 'Stück',
                unitPrice: 0,
                taxRate: 19,
                saveAsTemplate: false,
                get subtotal() { return (this.quantity * this.unitPrice); },
                get taxAmount() { return (this.subtotal * this.taxRate / 100); },
                get total() { return (this.subtotal + this.taxAmount).toFixed(2); }
            }">
                <!-- Beschreibung -->
                <div>
                    <label class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2">Beschreibung *</label>
                    <input type="text" name="description" x-model="description" required class="w-full px-4 py-3 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary text-base" placeholder="z.B. Webdesign, Beratung, Hosting">
                </div>

                <!-- Menge, Einheit, Preis -->
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2">Menge *</label>
                        <input type="number" name="quantity" x-model="quantity" step="0.01" min="0" required class="w-full px-4 py-3 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary text-base">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2">Einheit *</label>
                        <select name="unit" x-model="unit" class="w-full px-4 py-3 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary text-base">
                            <option value="Stück">Stück</option>
                            <option value="Stunden">Stunden</option>
                            <option value="Tage">Tage</option>
                            <option value="Monate">Monate</option>
                            <option value="Pauschal">Pauschal</option>
                            <option value="m²">m²</option>
                            <option value="kg">kg</option>
                            <option value="Liter">Liter</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2">Einzelpreis (€) *</label>
                        <input type="number" name="unit_price" x-model="unitPrice" step="0.01" min="0" required class="w-full px-4 py-3 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary text-base">
                    </div>
                </div>

                <!-- MwSt -->
                <div>
                    <label class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2">Mehrwertsteuer (%) *</label>
                    <div class="grid grid-cols-4 gap-3">
                        <button type="button" @click="taxRate = 0" :class="taxRate == 0 ? 'bg-primary text-white' : 'bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark'" class="px-4 py-3 rounded-lg border border-border-light dark:border-border-dark font-semibold transition-colors">
                            0%
                        </button>
                        <button type="button" @click="taxRate = 7" :class="taxRate == 7 ? 'bg-primary text-white' : 'bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark'" class="px-4 py-3 rounded-lg border border-border-light dark:border-border-dark font-semibold transition-colors">
                            7%
                        </button>
                        <button type="button" @click="taxRate = 19" :class="taxRate == 19 ? 'bg-primary text-white' : 'bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark'" class="px-4 py-3 rounded-lg border border-border-light dark:border-border-dark font-semibold transition-colors">
                            19%
                        </button>
                        <input type="number" name="tax_rate" x-model="taxRate" step="0.01" min="0" placeholder="Eigene" class="px-4 py-3 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary text-base">
                    </div>
                </div>

                <!-- Berechnung anzeigen -->
                <div class="bg-gradient-to-br from-primary/10 to-teal-500/10 border-2 border-primary/20 p-6 rounded-lg">
                    <h4 class="text-sm font-semibold text-text-light dark:text-text-dark mb-4">Berechnung</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-text-muted-light dark:text-text-muted-dark">Nettobetrag:</span>
                            <span class="text-base font-semibold text-text-light dark:text-text-dark" x-text="'€' + subtotal.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-text-muted-light dark:text-text-muted-dark">MwSt. (<span x-text="taxRate"></span>%):</span>
                            <span class="text-base font-semibold text-text-light dark:text-text-dark" x-text="'€' + taxAmount.toFixed(2)"></span>
                        </div>
                        <div class="border-t-2 border-primary/20 pt-3 flex justify-between items-center">
                            <span class="text-base font-bold text-text-light dark:text-text-dark">Gesamtbetrag:</span>
                            <span class="text-2xl font-bold text-primary" x-text="'€' + total"></span>
                        </div>
                    </div>
                </div>

                <!-- Als Vorlage speichern -->
                <div class="flex items-center bg-background-light dark:bg-background-dark p-4 rounded-lg">
                    <input type="checkbox" name="save_as_template" x-model="saveAsTemplate" id="save_template" class="h-5 w-5 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="save_template" class="ml-3 block text-sm font-medium text-text-light dark:text-text-dark">
                        Als Vorlage speichern (für spätere Verwendung)
                    </label>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="button" @click="showAddItemModal = false" class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold transition-colors">
                        Abbrechen
                    </button>
                    <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-primary to-teal-600 text-white rounded-lg hover:from-primary/90 hover:to-teal-600/90 font-semibold shadow-lg hover:shadow-xl transition-all">
                        Position hinzufügen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Rechnung ' . $invoice['invoice_number'];
$title = 'Rechnung ' . $invoice['invoice_number'] . ' - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
