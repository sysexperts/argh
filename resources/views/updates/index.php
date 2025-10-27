<!-- System-Updates -->

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-4xl text-primary">system_update</span>
        <h2 class="text-3xl font-bold text-text-light dark:text-text-dark">System-Updates</h2>
    </div>
    <p class="text-text-muted-light dark:text-text-muted-dark">
        Aktuelle Version: <span class="font-semibold text-primary"><?= htmlspecialchars($currentVersion) ?></span>
    </p>
</div>

<!-- Verfügbare Updates -->
<?php if (!empty($availableUpdates)): ?>
<div class="mb-6">
    <h3 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4">Verfügbare Updates</h3>
    
    <div class="space-y-4">
        <?php foreach ($availableUpdates as $update): ?>
        <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-lg border <?= $update['is_security_update'] ? 'border-red-500' : 'border-border-light dark:border-border-dark' ?> p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h4 class="text-lg font-bold text-text-light dark:text-text-dark">
                            Version <?= htmlspecialchars($update['version']) ?>
                        </h4>
                        
                        <?php
                        $typeColors = [
                            'feature' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                            'bugfix' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'security' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                            'hotfix' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                        ];
                        $typeClass = $typeColors[$update['release_type']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $typeClass ?>">
                            <?= ucfirst($update['release_type']) ?>
                        </span>
                        
                        <?php if ($update['is_security_update']): ?>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                            🔒 Sicherheitsupdate
                        </span>
                        <?php endif; ?>
                        
                        <?php if ($update['is_breaking_change']): ?>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                            ⚠️ Breaking Change
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-sm text-text-muted-light dark:text-text-muted-dark mb-2">
                        Veröffentlicht: <?= date('d.m.Y', strtotime($update['release_date'])) ?>
                    </p>
                    
                    <h5 class="font-semibold text-text-light dark:text-text-dark mb-2">
                        <?= htmlspecialchars($update['title']) ?>
                    </h5>
                    
                    <?php if ($update['description']): ?>
                    <p class="text-sm text-text-light dark:text-text-dark mb-3">
                        <?= nl2br(htmlspecialchars($update['description'])) ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($update['changelog']): ?>
                    <details class="mt-3">
                        <summary class="cursor-pointer text-sm font-medium text-primary hover:underline">
                            Changelog anzeigen
                        </summary>
                        <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-sm">
                            <pre class="whitespace-pre-wrap text-text-light dark:text-text-dark"><?= htmlspecialchars($update['changelog']) ?></pre>
                        </div>
                    </details>
                    <?php endif; ?>
                </div>
                
                <div class="ml-4">
                    <?php if ($update['is_security_update']): ?>
                    <form method="POST" action="/updates/install/<?= urlencode($update['version']) ?>">
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined">security</span>
                            Jetzt installieren (Pflicht)
                        </button>
                    </form>
                    <?php else: ?>
                    <form method="POST" action="/updates/install/<?= urlencode($update['version']) ?>">
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-primary to-teal-600 hover:from-primary/90 hover:to-teal-600/90 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined">download</span>
                            Installieren
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 text-center">
    <span class="material-symbols-outlined text-5xl text-green-600 dark:text-green-400 mb-2">check_circle</span>
    <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-2">System ist aktuell!</h3>
    <p class="text-sm text-green-700 dark:text-green-300">
        Sie verwenden die neueste Version. Es sind keine Updates verfügbar.
    </p>
</div>
<?php endif; ?>

<!-- Installierte Updates -->
<div class="mb-6">
    <h3 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4">Update-Historie</h3>
    
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Version</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Typ</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Titel</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Datum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    <?php foreach ($installedUpdates as $update): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-4 py-3 font-mono text-sm text-text-light dark:text-text-dark">
                            <?= htmlspecialchars($update['version']) ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php
                            $typeColors = [
                                'feature' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                'bugfix' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                'security' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                'hotfix' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                            ];
                            $typeClass = $typeColors[$update['release_type']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $typeClass ?>">
                                <?= ucfirst($update['release_type']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark">
                            <?= htmlspecialchars($update['title']) ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-text-muted-light dark:text-text-muted-dark">
                            <?= date('d.m.Y', strtotime($update['release_date'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($installedUpdates)): ?>
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-text-muted-light dark:text-text-muted-dark">
                            Keine Updates installiert
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
