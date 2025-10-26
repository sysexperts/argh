<?php
// Module Overview
ob_start();
?>

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-text-light dark:text-text-dark">Meine Module</h2>
        <p class="text-sm text-text-muted-light dark:text-text-muted-dark mt-1">
            Übersicht Ihrer aktivierten Module
        </p>
    </div>
    <a href="/marketplace" class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg shadow-lg hover:shadow-xl transition-all">
        <span class="material-symbols-outlined">shopping_cart</span>
        <span class="font-semibold">Marketplace</span>
    </a>
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

<!-- Module Grid -->
<?php foreach ($grouped as $category => $categoryModules): ?>
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">folder</span>
            <?= htmlspecialchars($category) ?>
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($categoryModules as $module): ?>
                <?php 
                $isActive = $module['is_enabled'] == 1 || $module['is_core'] == 1;
                $isCore = $module['is_core'] == 1;
                ?>
                <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6 border-2 <?= $isActive ? 'border-primary' : 'border-border-light dark:border-border-dark' ?> transition-all hover:shadow-md">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h4 class="font-semibold text-lg text-text-light dark:text-text-dark mb-1">
                                <?= htmlspecialchars($module['name']) ?>
                            </h4>
                            <?php if ($isCore): ?>
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                    <span class="material-symbols-outlined text-sm mr-1">verified</span>
                                    Core-Modul
                                </span>
                            <?php else: ?>
                                <span class="text-sm font-semibold text-primary">
                                    €<?= number_format($module['price_per_user'], 2, ',', '.') ?> / Monat
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($isActive): ?>
                            <span class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                                <span class="material-symbols-outlined">check_circle</span>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <p class="text-sm text-text-muted-light dark:text-text-muted-dark mb-4">
                        <?= htmlspecialchars($module['description'] ?? 'Keine Beschreibung verfügbar') ?>
                    </p>

                    <!-- Status & Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-border-light dark:border-border-dark">
                        <?php if ($isCore): ?>
                            <span class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                Immer aktiv
                            </span>
                        <?php elseif ($isActive): ?>
                            <span class="flex items-center gap-1 text-xs font-medium text-green-600 dark:text-green-400">
                                <span class="material-symbols-outlined text-sm">check</span>
                                Aktiv
                            </span>
                            <form method="POST" action="/modules/<?= $module['id'] ?>/deactivate" class="inline">
                                <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium transition-colors">
                                    Deaktivieren
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                Nicht aktiv
                            </span>
                            <form method="POST" action="/modules/<?= $module['id'] ?>/activate" class="inline">
                                <button type="submit" class="text-sm text-primary hover:text-primary/80 font-medium transition-colors">
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

<?php
$content = ob_get_clean();
$pageTitle = 'Meine Module';
$title = 'Meine Module - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
