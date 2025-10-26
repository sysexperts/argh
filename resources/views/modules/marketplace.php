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

<?php foreach ($grouped as $category => $categoryModules): ?>
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">category</span>
            <?= htmlspecialchars($category) ?>
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($categoryModules as $module): ?>
                <?php 
                $isActive = $module['is_enabled'] == 1;
                $hasLicense = $module['license_id'] !== null;
                ?>
                <div class="group bg-card-light dark:bg-card-dark rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border <?= $isActive ? 'border-green-500 dark:border-green-600' : 'border-border-light dark:border-border-dark hover:border-primary' ?>">
                    <!-- Image/Icon Header -->
                    <div class="h-20 bg-gradient-to-br from-primary to-teal-600 flex items-center justify-center relative">
                        <span class="material-symbols-outlined text-4xl text-white opacity-80">
                            extension
                        </span>
                        <?php if ($isActive): ?>
                        <div class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full font-semibold">
                            Aktiv
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <!-- Title & Price -->
                        <div class="mb-3">
                            <h4 class="font-bold text-base text-text-light dark:text-text-dark mb-1">
                                <?= htmlspecialchars($module['name']) ?>
                            </h4>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-primary">
                                    €<?= number_format($module['price_per_user'], 2, ',', '.') ?>
                                </span>
                                <span class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                    /Monat
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-text-muted-light dark:text-text-muted-dark mb-3 line-clamp-2">
                            <?= htmlspecialchars($module['description'] ?? 'Professionelles Modul für Ihr Business') ?>
                        </p>

                        <!-- Action Button -->
                        <?php if ($isActive): ?>
                            <form method="POST" action="/modules/<?= $module['id'] ?>/deactivate">
                                <button type="submit" class="w-full py-2 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors border border-red-200 dark:border-red-800">
                                    Deaktivieren
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="/modules/<?= $module['id'] ?>/activate">
                                <button type="submit" class="w-full flex items-center justify-center gap-1 bg-gradient-to-r from-primary to-teal-600 hover:from-primary/90 hover:to-teal-600/90 text-white py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow-md transition-all">
                                    <span class="material-symbols-outlined text-base">add</span>
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
$pageTitle = 'Marketplace';
$title = 'Marketplace - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
