<?php
// Customers View
ob_start();
?>

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-text-light dark:text-text-dark">Kunden</h2>
        <p class="text-sm text-text-muted-light dark:text-text-muted-dark mt-1">
            Verwalten Sie Ihre Kundendaten
        </p>
    </div>
    <button @click="showCreateModal = true" class="flex items-center gap-2 bg-gradient-to-r from-primary to-teal-600 hover:from-primary/90 hover:to-teal-600/90 text-white px-4 py-2.5 rounded-lg shadow-lg hover:shadow-xl transition-all">
        <span class="material-symbols-outlined">add</span>
        <span class="font-semibold">Neuer Kunde</span>
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

<!-- Customers Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if (empty($customers)): ?>
        <div class="col-span-full text-center py-12 bg-card-light dark:bg-card-dark rounded-lg">
            <span class="material-symbols-outlined text-6xl mb-4 block opacity-20 text-text-muted-light dark:text-text-muted-dark">business</span>
            <p class="text-lg font-medium text-text-light dark:text-text-dark mb-2">Keine Kunden vorhanden</p>
            <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Erstellen Sie Ihren ersten Kunden</p>
        </div>
    <?php else: ?>
        <?php foreach ($customers as $customer): ?>
            <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-lg">
                            <?= strtoupper(substr($customer['company_name'], 0, 2)) ?>
                        </div>
                        <div>
                            <h3 class="font-semibold text-text-light dark:text-text-dark"><?= htmlspecialchars($customer['company_name']) ?></h3>
                            <p class="text-xs text-text-muted-light dark:text-text-muted-dark"><?= htmlspecialchars($customer['customer_number']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    <?php if ($customer['contact_person']): ?>
                        <div class="flex items-center gap-2 text-text-muted-light dark:text-text-muted-dark">
                            <span class="material-symbols-outlined text-sm">person</span>
                            <span><?= htmlspecialchars($customer['contact_person']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($customer['email']): ?>
                        <div class="flex items-center gap-2 text-text-muted-light dark:text-text-muted-dark">
                            <span class="material-symbols-outlined text-sm">email</span>
                            <span><?= htmlspecialchars($customer['email']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($customer['phone']): ?>
                        <div class="flex items-center gap-2 text-text-muted-light dark:text-text-muted-dark">
                            <span class="material-symbols-outlined text-sm">phone</span>
                            <span><?= htmlspecialchars($customer['phone']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($customer['city']): ?>
                        <div class="flex items-center gap-2 text-text-muted-light dark:text-text-muted-dark">
                            <span class="material-symbols-outlined text-sm">location_on</span>
                            <span><?= htmlspecialchars($customer['zip'] . ' ' . $customer['city']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex gap-2 mt-4 pt-4 border-t border-border-light dark:border-border-dark">
                    <button @click="editCustomer(<?= htmlspecialchars(json_encode($customer)) ?>)" class="flex-1 text-sm text-primary hover:text-primary/80 font-medium transition-colors">
                        Bearbeiten
                    </button>
                    <form method="POST" action="/customers/<?= $customer['id'] ?>/delete" class="flex-1" onsubmit="return confirm('Kunde wirklich deaktivieren?')">
                        <button type="submit" class="w-full text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium transition-colors">
                            Deaktivieren
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Create Customer Modal -->
<div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div @click="showCreateModal = false" class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <div class="relative bg-card-light dark:bg-card-dark rounded-lg max-w-2xl w-full p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Neuer Kunde</h3>
                <button @click="showCreateModal = false" class="text-text-muted-light dark:text-text-muted-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="/customers" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Firmenname *</label>
                        <input type="text" name="company_name" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Ansprechpartner</label>
                        <input type="text" name="contact_person" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">E-Mail</label>
                        <input type="email" name="email" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Telefon</label>
                        <input type="text" name="phone" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Website</label>
                        <input type="text" name="website" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Straße</label>
                        <input type="text" name="street" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">PLZ</label>
                        <input type="text" name="zip" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Stadt</label>
                        <input type="text" name="city" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Land</label>
                        <input type="text" name="country" value="Deutschland" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Steuernummer</label>
                        <input type="text" name="tax_id" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">USt-IdNr.</label>
                        <input type="text" name="vat_id" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

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

<!-- Edit Customer Modal -->
<div x-show="showEditCustomerModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div @click="showEditCustomerModal = false" class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <div class="relative bg-card-light dark:bg-card-dark rounded-lg max-w-2xl w-full p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Kunde bearbeiten</h3>
                <button @click="showEditCustomerModal = false" class="text-text-muted-light dark:text-text-muted-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" :action="`/customers/${editingCustomer.id}/update`" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Firmenname *</label>
                        <input type="text" name="company_name" x-model="editingCustomer.company_name" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Ansprechpartner</label>
                        <input type="text" name="contact_person" x-model="editingCustomer.contact_person" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">E-Mail</label>
                        <input type="email" name="email" x-model="editingCustomer.email" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Telefon</label>
                        <input type="text" name="phone" x-model="editingCustomer.phone" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Website</label>
                        <input type="text" name="website" x-model="editingCustomer.website" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Straße</label>
                        <input type="text" name="street" x-model="editingCustomer.street" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">PLZ</label>
                        <input type="text" name="zip" x-model="editingCustomer.zip" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Stadt</label>
                        <input type="text" name="city" x-model="editingCustomer.city" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Land</label>
                        <input type="text" name="country" x-model="editingCustomer.country" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Steuernummer</label>
                        <input type="text" name="tax_id" x-model="editingCustomer.tax_id" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">USt-IdNr.</label>
                        <input type="text" name="vat_id" x-model="editingCustomer.vat_id" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Notizen</label>
                    <textarea name="notes" x-model="editingCustomer.notes" rows="3" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary"></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showEditCustomerModal = false" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                        Abbrechen
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                        Speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Kunden';
$title = 'Kunden - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
