<?php
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="bg-gradient-to-r from-primary via-cyan-500 to-blue-500 rounded-2xl p-8 shadow-2xl">
        <div class="flex items-center justify-between">
            <div class="text-white">
                <h1 class="text-3xl font-bold mb-2">Kundenverwaltung 🏢</h1>
                <p class="text-white/90 text-lg">Verwalten Sie Ihre Kunden und Kontakte</p>
            </div>
            <div class="hidden lg:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                    <span class="material-symbols-outlined text-6xl text-white">business</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-primary/10 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl text-primary">business</span>
            </div>
            <div>
                <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Gesamt</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark"><?= count($customers) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-green-500/10 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl text-green-600">check_circle</span>
            </div>
            <div>
                <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Aktiv</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">
                    <?= count(array_filter($customers, fn($c) => $c['is_active'])) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-blue-500/10 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl text-blue-600">person</span>
            </div>
            <div>
                <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Privatkunden</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">
                    <?= count(array_filter($customers, fn($c) => ($c['type'] ?? '') === 'private')) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-purple-500/10 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl text-purple-600">corporate_fare</span>
            </div>
            <div>
                <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Geschäftskunden</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">
                    <?= count(array_filter($customers, fn($c) => ($c['type'] ?? '') === 'business')) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <input type="text" 
               id="searchInput" 
               placeholder="Kunden suchen..." 
               class="px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-card-light dark:bg-card-dark text-text-light dark:text-text-dark">
    </div>
    <button onclick="alert('Kunde erstellen - Feature kommt bald!')" 
            class="bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary/90 transition flex items-center gap-2">
        <span class="material-symbols-outlined">add</span>
        Neuer Kunde
    </button>
</div>

<!-- Customers Table -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-background-light dark:bg-background-dark">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Kunde
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Typ
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Kontakt
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Erstellt
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Aktionen
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <span class="material-symbols-outlined text-6xl text-text-muted-light dark:text-text-muted-dark opacity-50">
                                business_center
                            </span>
                            <p class="text-text-muted-light dark:text-text-muted-dark">
                                Noch keine Kunden vorhanden. Legen Sie Ihren ersten Kunden an!
                            </p>
                            <button onclick="alert('Kunde erstellen - Feature kommt bald!')" 
                                    class="bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary/90 transition">
                                Ersten Kunden anlegen
                            </button>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                    <tr class="hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                                    <?php if (($customer['type'] ?? '') === 'business'): ?>
                                        <span class="material-symbols-outlined">business</span>
                                    <?php else: ?>
                                        <?= strtoupper(substr($customer['first_name'] ?? $customer['last_name'] ?? $customer['company_name'] ?? 'K', 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-text-light dark:text-text-dark">
                                        <?php if (($customer['type'] ?? '') === 'business'): ?>
                                            <?= htmlspecialchars($customer['company_name'] ?? 'Unbekannt') ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars(trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')) ?: 'Unbekannt') ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                        Kunden-Nr: <?= $customer['customer_number'] ?? $customer['id'] ?>
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (($customer['type'] ?? '') === 'business'): ?>
                                <span class="px-3 py-1 bg-purple-500 text-white text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                    <span class="material-symbols-outlined text-sm">corporate_fare</span>
                                    Geschäft
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                    <span class="material-symbols-outlined text-sm">person</span>
                                    Privat
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <p class="text-text-light dark:text-text-dark"><?= htmlspecialchars($customer['email'] ?? '-') ?></p>
                                <p class="text-text-muted-light dark:text-text-muted-dark"><?= htmlspecialchars($customer['phone'] ?? '-') ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($customer['is_active']): ?>
                                <span class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    Aktiv
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-gray-500 text-white text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                    <span class="material-symbols-outlined text-sm">cancel</span>
                                    Inaktiv
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-muted-light dark:text-text-muted-dark">
                            <?= date('d.m.Y', strtotime($customer['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <button onclick="alert('Details anzeigen - Feature kommt bald!')" 
                                        class="p-2 text-primary hover:bg-primary/10 rounded-lg transition">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                </button>
                                <button onclick="alert('Bearbeiten - Feature kommt bald!')" 
                                        class="p-2 text-primary hover:bg-primary/10 rounded-lg transition">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button onclick="alert('Löschen - Feature kommt bald!')" 
                                        class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Simple search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Kundenverwaltung';
$title = 'Kundenverwaltung - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
