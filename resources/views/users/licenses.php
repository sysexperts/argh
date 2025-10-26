<?php
$title = 'Lizenzen verwalten - ' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
ob_start();
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-text-light dark:text-text-dark">Lizenzen verwalten</h2>
            <p class="text-text-muted-light dark:text-text-muted-dark mt-1">
                Benutzer: <span class="font-semibold"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span> (<?= htmlspecialchars($user['email']) ?>)
            </p>
        </div>
        <a href="/users" class="px-4 py-2 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded hover:bg-background-light dark:hover:bg-background-dark text-text-light dark:text-text-dark">
            ← Zurück
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Module nach Kategorie -->
<?php foreach ($grouped as $category => $categoryModules): ?>
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">category</span>
            <?= htmlspecialchars($category) ?>
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($categoryModules as $module): ?>
                <?php 
                $isActive = $module['is_enabled'] == 1;
                $hasLicense = $module['license_id'] !== null;
                ?>
                <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm overflow-hidden border <?= $isActive ? 'border-green-500 dark:border-green-600' : 'border-border-light dark:border-border-dark' ?>">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="font-bold text-base text-text-light dark:text-text-dark mb-1">
                                    <?= htmlspecialchars($module['name']) ?>
                                </h4>
                                <p class="text-xs text-text-muted-light dark:text-text-muted-dark line-clamp-2">
                                    <?= htmlspecialchars($module['description'] ?? '') ?>
                                </p>
                            </div>
                            <?php if ($isActive): ?>
                            <div class="ml-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full font-semibold whitespace-nowrap">
                                Aktiv
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-primary">
                                €<?= number_format($module['price_per_user'], 2, ',', '.') ?>/Monat
                            </span>

                            <?php if ($isActive): ?>
                                <form method="POST" action="/users/<?= $user['id'] ?>/licenses/<?= $module['id'] ?>/revoke">
                                    <button type="submit" class="px-3 py-1 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded border border-red-200 dark:border-red-800 transition-colors">
                                        Entziehen
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="/users/<?= $user['id'] ?>/licenses/<?= $module['id'] ?>/grant">
                                    <button type="submit" class="px-3 py-1 text-xs bg-primary text-white rounded hover:bg-primary/90 transition-colors">
                                        Erteilen
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <?php if ($isActive && $module['licensed_at']): ?>
                        <div class="mt-2 pt-2 border-t border-border-light dark:border-border-dark">
                            <p class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                Seit: <?= date('d.m.Y', strtotime($module['licensed_at'])) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
