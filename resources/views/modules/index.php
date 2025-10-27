<?php
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="bg-gradient-to-r from-primary via-purple-500 to-pink-500 rounded-2xl p-8 shadow-2xl">
        <div class="flex items-center justify-between">
            <div class="text-white">
                <h1 class="text-3xl font-bold mb-2">Marketplace 🛒</h1>
                <p class="text-white/90 text-lg">Erweitern Sie Ihr System mit zusätzlichen Modulen</p>
            </div>
            <div class="hidden lg:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                    <span class="material-symbols-outlined text-6xl text-white">store</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Module Grid -->
<?php foreach ($modulesByCategory as $category => $modules): ?>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-text-light dark:text-text-dark mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">category</span>
            <?= htmlspecialchars($category) ?>
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($modules as $module): ?>
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg overflow-hidden border border-border-light dark:border-border-dark hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <!-- Module Header -->
                    <div class="p-6 bg-gradient-to-br from-primary/10 to-primary/5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <span class="text-4xl"><?= $module['icon'] ?? '📦' ?></span>
                                <div>
                                    <h3 class="text-lg font-bold text-text-light dark:text-text-dark">
                                        <?= htmlspecialchars($module['name']) ?>
                                    </h3>
                                    <p class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                        <?= htmlspecialchars($module['code']) ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($module['is_enabled']): ?>
                                <span class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">
                                    Aktiviert
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-full">
                                    Inaktiv
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Module Body -->
                    <div class="p-6">
                        <p class="text-sm text-text-muted-light dark:text-text-muted-dark mb-4">
                            <?= htmlspecialchars($module['description']) ?>
                        </p>
                        
                        <!-- Pricing -->
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-border-light dark:border-border-dark">
                            <div>
                                <p class="text-xs text-text-muted-light dark:text-text-muted-dark">Preis pro Benutzer</p>
                                <p class="text-2xl font-bold text-primary">
                                    €<?= number_format($module['price_per_user'], 2, ',', '.') ?>
                                    <span class="text-sm font-normal text-text-muted-light dark:text-text-muted-dark">/Monat</span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex gap-2">
                            <?php if ($module['is_enabled']): ?>
                                <a href="<?= htmlspecialchars($module['url']) ?>" 
                                   class="flex-1 bg-primary text-white px-4 py-2 rounded-lg text-center font-semibold hover:bg-primary/90 transition">
                                    Öffnen
                                </a>
                                <button onclick="deactivateModule(<?= $module['id'] ?>)"
                                        class="px-4 py-2 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 transition">
                                    Deaktivieren
                                </button>
                            <?php else: ?>
                                <button onclick="activateModule(<?= $module['id'] ?>)"
                                        class="flex-1 bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-primary/90 transition">
                                    Jetzt aktivieren
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<script>
function activateModule(moduleId) {
    if (confirm('Möchten Sie dieses Modul wirklich aktivieren?')) {
        fetch(`/api/modules/${moduleId}/activate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
            }
        })
        .catch(error => {
            alert('Fehler beim Aktivieren des Moduls');
            console.error(error);
        });
    }
}

function deactivateModule(moduleId) {
    if (confirm('Möchten Sie dieses Modul wirklich deaktivieren?')) {
        fetch(`/api/modules/${moduleId}/deactivate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
            }
        })
        .catch(error => {
            alert('Fehler beim Deaktivieren des Moduls');
            console.error(error);
        });
    }
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Marketplace';
$title = 'Marketplace - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
