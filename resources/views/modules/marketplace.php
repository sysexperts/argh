<?php
// Marketplace View
ob_start();
?>

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-4xl text-primary">store</span>
        <h2 class="text-3xl font-bold text-text-light dark:text-text-dark">Marketplace</h2>
    </div>
    <p class="text-text-muted-light dark:text-text-muted-dark">
        Erweitern Sie Ihr System mit zusätzlichen Modulen. Nur <span class="font-semibold text-primary">1€ pro Modul pro Monat</span>.
    </p>
</div>

<!-- Error Message -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-2 border-red-500 dark:border-red-600 text-red-800 dark:text-red-200 px-6 py-4 rounded-lg shadow-lg animate-pulse">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-3xl">error</span>
            <div class="flex-1">
                <p class="font-semibold text-base mb-1">⚠️ Zugriff verweigert</p>
                <p class="text-sm"><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-red-600 dark:text-red-400 hover:text-red-800">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

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

<!-- Search & Filter Bar -->
<div class="mb-6 bg-card-light dark:bg-card-dark rounded-lg shadow-lg p-4 border border-border-light dark:border-border-dark">
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center">
        <!-- Search -->
        <div class="w-full md:w-96 relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted-light dark:text-text-muted-dark">search</span>
            <input 
                type="text" 
                id="moduleSearch" 
                placeholder="Module durchsuchen..." 
                class="w-full pl-10 pr-4 py-2.5 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                onkeyup="filterModules()">
        </div>
        
        <!-- Filter Buttons -->
        <div class="flex gap-2 flex-wrap">
            <button onclick="filterByStatus('all')" id="filter-all" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-primary text-white">
                Alle
            </button>
            <button onclick="filterByStatus('active')" id="filter-active" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-gray-200 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-300 dark:hover:bg-gray-600">
                Aktiv
            </button>
            <button onclick="filterByStatus('inactive')" id="filter-inactive" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-gray-200 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-300 dark:hover:bg-gray-600">
                Inaktiv
            </button>
        </div>
    </div>
</div>

<!-- Info Banner -->
<div class="mb-6 bg-gradient-to-r from-primary/10 to-teal-500/10 border border-primary/20 rounded-lg p-4">
    <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-2xl text-primary">info</span>
        <div>
            <h3 class="font-semibold text-text-light dark:text-text-dark mb-1 text-sm">Wie funktioniert der Marketplace?</h3>
            <p class="text-xs text-text-muted-light dark:text-text-muted-dark">
                Wählen Sie Module aus, aktivieren Sie mit einem Klick und bezahlen nur 1€ pro Modul/Monat. Jederzeit kündbar.
            </p>
        </div>
    </div>
</div>

<!-- Module Grid -->
<?php 
// Sortiere Module: Aktive zuerst
foreach ($grouped as $category => &$categoryModules) {
    usort($categoryModules, function($a, $b) {
        $aActive = $a['is_enabled'] == 1;
        $bActive = $b['is_enabled'] == 1;
        if ($aActive === $bActive) return 0;
        return $aActive ? -1 : 1;
    });
}
unset($categoryModules);
?>

<?php 
// Kategorie-Icons
$categoryIcons = [
    'Core' => 'settings',
    'Finanzen' => 'payments',
    'CRM' => 'handshake',
    'Produktivität' => 'productivity',
    'Support' => 'support',
    'Organisation' => 'folder_managed',
];
?>

<?php foreach ($grouped as $category => $categoryModules): ?>
    <div class="mb-8" data-category>
        <!-- Kategorie-Header als Card -->
        <div class="bg-gradient-to-r from-primary/10 via-teal-500/10 to-primary/10 rounded-lg p-4 mb-4 border border-primary/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-xl">
                            <?= $categoryIcons[$category] ?? 'category' ?>
                        </span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-text-light dark:text-text-dark">
                            <?= htmlspecialchars($category) ?>
                        </h3>
                        <p class="text-xs text-text-muted-light dark:text-text-muted-dark">
                            <?= count($categoryModules) ?> Module verfügbar
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <?php 
                    $activeCount = count(array_filter($categoryModules, fn($m) => $m['is_enabled'] == 1));
                    ?>
                    <span class="text-sm font-semibold text-primary">
                        <?= $activeCount ?> / <?= count($categoryModules) ?> aktiv
                    </span>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
            <?php foreach ($categoryModules as $module): ?>
                <?php 
                $isActive = $module['is_enabled'] == 1;
                $hasLicense = $module['license_id'] !== null;
                
                // Modul-spezifische Icons
                $moduleIcons = [
                    'invoices' => 'receipt_long',
                    'customers' => 'group',
                    'time-tracking' => 'schedule',
                    'projects' => 'folder',
                    'documents' => 'description',
                    'helpdesk' => 'support_agent',
                    'calendar' => 'calendar_month',
                ];
                $moduleIcon = $moduleIcons[$module['code']] ?? 'extension';
                ?>
                <div class="group bg-card-light dark:bg-card-dark rounded-lg shadow hover:shadow-lg transition-all duration-200 overflow-hidden border <?= $isActive ? 'border-green-500 dark:border-green-600' : 'border-border-light dark:border-border-dark hover:border-primary' ?>"
                     data-module-card
                     data-module-title="<?= htmlspecialchars($module['name']) ?>"
                     data-module-description="<?= htmlspecialchars($module['description'] ?? '') ?>"
                     data-module-active="<?= $isActive ? '1' : '0' ?>">
                    <!-- Icon Header mit Gradient -->
                    <div class="relative h-24 bg-gradient-to-br from-primary to-teal-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-5xl text-white/90">
                            <?= $moduleIcon ?>
                        </span>
                        
                        <?php if ($isActive): ?>
                        <div class="absolute top-1.5 right-1.5 bg-green-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold shadow flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-xs">check</span>
                            Aktiv
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div class="p-3">
                        <!-- Title -->
                        <h4 class="font-semibold text-sm text-text-light dark:text-text-dark mb-1 line-clamp-1">
                            <?= htmlspecialchars($module['name']) ?>
                        </h4>

                        <!-- Price -->
                        <div class="mb-2">
                            <span class="text-base font-bold text-primary">€<?= number_format($module['price_per_user'], 2, ',', '.') ?></span>
                            <span class="text-[10px] text-text-muted-light dark:text-text-muted-dark">/Monat</span>
                        </div>

                        <!-- Action Button -->
                        <?php if ($isActive): ?>
                            <form method="POST" action="/modules/<?= $module['id'] ?>/deactivate">
                                <button type="submit" class="w-full py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors border border-red-200 dark:border-red-800">
                                    Deaktivieren
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="/modules/<?= $module['id'] ?>/activate">
                                <button type="submit" class="w-full flex items-center justify-center gap-1 bg-gradient-to-r from-primary to-teal-600 hover:from-primary/90 hover:to-teal-600/90 text-white py-1.5 rounded text-xs font-semibold shadow hover:shadow-md transition-all">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                    Aktivieren
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<script>
let currentFilter = 'all';

function filterModules() {
    const searchTerm = document.getElementById('moduleSearch').value.toLowerCase();
    const moduleCards = document.querySelectorAll('[data-module-card]');
    const categories = document.querySelectorAll('[data-category]');
    
    moduleCards.forEach(card => {
        const title = card.getAttribute('data-module-title').toLowerCase();
        const description = card.getAttribute('data-module-description').toLowerCase();
        const isActive = card.getAttribute('data-module-active') === '1';
        
        // Check search match
        const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
        
        // Check filter match
        let matchesFilter = true;
        if (currentFilter === 'active') {
            matchesFilter = isActive;
        } else if (currentFilter === 'inactive') {
            matchesFilter = !isActive;
        }
        
        // Show/hide card
        if (matchesSearch && matchesFilter) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
    
    // Hide empty categories
    categories.forEach(category => {
        const visibleCards = category.querySelectorAll('[data-module-card]:not(.hidden)');
        if (visibleCards.length === 0) {
            category.classList.add('hidden');
        } else {
            category.classList.remove('hidden');
        }
    });
}

function filterByStatus(status) {
    currentFilter = status;
    
    // Update button styles - alle Buttons zurücksetzen
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.className = 'filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-gray-200 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-300 dark:hover:bg-gray-600';
    });
    
    // Aktiven Button highlighten
    const activeBtn = document.getElementById('filter-' + status);
    activeBtn.className = 'filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-primary text-white';
    
    filterModules();
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Marketplace';
$title = 'Marketplace - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
