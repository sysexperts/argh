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

<!-- Info Banner -->
<div class="mb-6 bg-gradient-to-r from-primary/10 to-teal-500/10 border border-primary/20 rounded-lg p-6">
    <div class="flex items-start gap-4">
        <span class="material-symbols-outlined text-3xl text-primary">info</span>
        <div>
            <h3 class="font-semibold text-text-light dark:text-text-dark mb-2">Wie funktioniert der Marketplace?</h3>
            <ul class="text-sm text-text-muted-light dark:text-text-muted-dark space-y-1">
                <li>✓ Wählen Sie die Module aus, die Sie benötigen</li>
                <li>✓ Aktivieren Sie Module mit einem Klick</li>
                <li>✓ Bezahlen Sie nur für aktive Module (1€ pro Modul pro Monat)</li>
                <li>✓ Deaktivieren Sie Module jederzeit ohne Kündigungsfrist</li>
            </ul>
        </div>
    </div>
</div>

<!-- Module Grid -->
<?php foreach ($grouped as $category => $categoryModules): ?>
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">category</span>
            <?= htmlspecialchars($category) ?>
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($categoryModules as $module): ?>
                <?php 
                $isActive = $module['is_enabled'] == 1;
                $hasLicense = $module['license_id'] !== null;
                ?>
                <div class="group bg-card-light dark:bg-card-dark rounded-lg shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-border-light dark:border-border-dark hover:border-primary">
                    <!-- Image/Icon Header -->
                    <div class="h-32 bg-gradient-to-br from-primary to-teal-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-6xl text-white opacity-80">
                            extension
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <!-- Title & Price -->
                        <div class="mb-4">
                            <h4 class="font-bold text-lg text-text-light dark:text-text-dark mb-2">
                                <?= htmlspecialchars($module['name']) ?>
                            </h4>
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-primary">
                                    €<?= number_format($module['price_per_user'], 2, ',', '.') ?>
                                </span>
                                <span class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                    pro Monat
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="text-sm text-text-muted-light dark:text-text-muted-dark mb-6 line-clamp-3">
                            <?= htmlspecialchars($module['description'] ?? 'Professionelles Modul für Ihr Business') ?>
                        </p>

                        <!-- Features (Mock) -->
                        <ul class="text-xs text-text-muted-light dark:text-text-muted-dark space-y-2 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-green-500">check_circle</span>
                                Vollständig integriert
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-green-500">check_circle</span>
                                Regelmäßige Updates
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-green-500">check_circle</span>
                                Support inklusive
                            </li>
                        </ul>

                        <!-- Action Button -->
                        <?php if ($isActive): ?>
                            <div class="space-y-2">
                                <div class="flex items-center justify-center gap-2 py-3 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-lg font-semibold">
                                    <span class="material-symbols-outlined">check_circle</span>
                                    Aktiv
                                </div>
                                <form method="POST" action="/modules/<?= $module['id'] ?>/deactivate">
                                    <button type="submit" class="w-full py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        Deaktivieren
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="/modules/<?= $module['id'] ?>/activate">
                                <button type="submit" class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-teal-600 hover:from-primary/90 hover:to-teal-600/90 text-white py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all">
                                    <span class="material-symbols-outlined">add_shopping_cart</span>
                                    Jetzt aktivieren
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
$pageTitle = 'Marketplace';
$title = 'Marketplace - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
