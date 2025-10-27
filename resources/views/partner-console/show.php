<!-- Mandanten-Details -->

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="/partner-console" class="text-sm text-primary hover:underline mb-2 inline-block">← Zurück zur Übersicht</a>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-2xl">business</span>
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-text-light dark:text-text-dark"><?= htmlspecialchars($tenant['company_name']) ?></h2>
                    <p class="text-sm text-text-muted-light dark:text-text-muted-dark"><?= htmlspecialchars($tenant['domain']) ?></p>
                </div>
            </div>
        </div>
        
        <div class="flex gap-2">
            <?php
            $statusColors = [
                'active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'trial' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
            ];
            $statusClass = $statusColors[$tenant['tenant_status']] ?? 'bg-gray-100 text-gray-800';
            ?>
            <span class="px-4 py-2 rounded-lg text-sm font-semibold <?= $statusClass ?>">
                <?= ucfirst($tenant['tenant_status']) ?>
            </span>
        </div>
    </div>
</div>

<!-- Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-card-light dark:bg-card-dark rounded-lg p-4 border border-border-light dark:border-border-dark">
        <div class="text-sm text-text-muted-light dark:text-text-muted-dark mb-1">Kontakt</div>
        <div class="font-semibold text-text-light dark:text-text-dark"><?= htmlspecialchars($tenant['contact_email']) ?></div>
        <?php if ($tenant['contact_phone']): ?>
        <div class="text-sm text-text-muted-light dark:text-text-muted-dark"><?= htmlspecialchars($tenant['contact_phone']) ?></div>
        <?php endif; ?>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-lg p-4 border border-border-light dark:border-border-dark">
        <div class="text-sm text-text-muted-light dark:text-text-muted-dark mb-1">Server</div>
        <div class="font-semibold text-text-light dark:text-text-dark"><?= htmlspecialchars($tenant['server_host'] ?? 'N/A') ?></div>
        <div class="text-sm text-text-muted-light dark:text-text-muted-dark"><?= htmlspecialchars($tenant['server_ip'] ?? 'N/A') ?></div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-lg p-4 border border-border-light dark:border-border-dark">
        <div class="text-sm text-text-muted-light dark:text-text-muted-dark mb-1">Version</div>
        <div class="font-semibold text-text-light dark:text-text-dark"><?= htmlspecialchars($tenant['installed_version'] ?? 'N/A') ?></div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-lg p-4 border border-border-light dark:border-border-dark">
        <div class="text-sm text-text-muted-light dark:text-text-muted-dark mb-1">Erstellt</div>
        <div class="font-semibold text-text-light dark:text-text-dark"><?= date('d.m.Y', strtotime($tenant['created_at'])) ?></div>
    </div>
</div>

<!-- Tabs -->
<div class="mb-6">
    <div class="border-b border-border-light dark:border-border-dark">
        <nav class="flex gap-4">
            <button onclick="switchTab('licenses')" id="tab-licenses" class="tab-btn active px-4 py-2 border-b-2 border-primary text-primary font-medium">
                Lizenzen
            </button>
            <button onclick="switchTab('versions')" id="tab-versions" class="tab-btn px-4 py-2 border-b-2 border-transparent text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark">
                Versionen
            </button>
            <button onclick="switchTab('heartbeats')" id="tab-heartbeats" class="tab-btn px-4 py-2 border-b-2 border-transparent text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark">
                Monitoring
            </button>
            <button onclick="switchTab('notes')" id="tab-notes" class="tab-btn px-4 py-2 border-b-2 border-transparent text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark">
                Notizen
            </button>
        </nav>
    </div>
</div>

<!-- Tab Content: Lizenzen -->
<div id="content-licenses" class="tab-content">
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark">
        <div class="p-4 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Modul-Lizenzen</h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($licenses as $license): ?>
                <div class="border border-border-light dark:border-border-dark rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-text-light dark:text-text-dark"><?= htmlspecialchars($license['module_name']) ?></h4>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $license['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' ?>">
                            <?= $license['is_active'] ? 'Aktiv' : 'Inaktiv' ?>
                        </span>
                    </div>
                    <div class="text-sm text-text-muted-light dark:text-text-muted-dark">
                        <div>User: <?= $license['user_count'] ?></div>
                        <div>Preis: €<?= number_format($license['price_per_user'] * $license['user_count'], 2, ',', '.') ?>/Monat</div>
                        <?php if ($license['activated_at']): ?>
                        <div>Aktiviert: <?= date('d.m.Y', strtotime($license['activated_at'])) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($licenses)): ?>
                <div class="col-span-3 text-center py-8 text-text-muted-light dark:text-text-muted-dark">
                    Keine Lizenzen vorhanden
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tab Content: Versionen -->
<div id="content-versions" class="tab-content hidden">
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark">
        <div class="p-4 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Deployment-Historie</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Modul</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Version</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Datum</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Von</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    <?php foreach ($versions as $version): ?>
                    <tr>
                        <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark"><?= htmlspecialchars($version['module_code']) ?></td>
                        <td class="px-4 py-3 text-sm font-mono text-text-light dark:text-text-dark"><?= htmlspecialchars($version['version']) ?></td>
                        <td class="px-4 py-3 text-sm text-text-muted-light dark:text-text-muted-dark"><?= date('d.m.Y H:i', strtotime($version['deployed_at'])) ?></td>
                        <td class="px-4 py-3 text-sm text-text-muted-light dark:text-text-muted-dark"><?= htmlspecialchars($version['deployed_by'] ?? 'System') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($versions)): ?>
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-text-muted-light dark:text-text-muted-dark">
                            Keine Versionen vorhanden
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Content: Monitoring -->
<div id="content-heartbeats" class="tab-content hidden">
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark">
        <div class="p-4 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Heartbeat-Historie</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Zeitpunkt</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">PHP</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">DB Size</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Memory</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Response</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    <?php foreach ($heartbeats as $hb): ?>
                    <tr>
                        <td class="px-4 py-3 text-sm text-text-muted-light dark:text-text-muted-dark"><?= date('d.m.Y H:i:s', strtotime($hb['created_at'])) ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $hb['heartbeat_status'] === 'online' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' ?>">
                                <?= htmlspecialchars($hb['heartbeat_status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark"><?= htmlspecialchars($hb['php_version'] ?? 'N/A') ?></td>
                        <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark"><?= $hb['database_size'] ? number_format($hb['database_size'] / 1024, 2) . ' MB' : 'N/A' ?></td>
                        <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark"><?= $hb['memory_usage'] ? $hb['memory_usage'] . ' MB' : 'N/A' ?></td>
                        <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark"><?= $hb['response_time'] ? $hb['response_time'] . ' ms' : 'N/A' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($heartbeats)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-text-muted-light dark:text-text-muted-dark">
                            Keine Heartbeats vorhanden
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Content: Notizen -->
<div id="content-notes" class="tab-content hidden">
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark">
        <div class="p-4 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Support-Notizen</h3>
        </div>
        <div class="p-4 space-y-4">
            <?php foreach ($notes as $note): ?>
            <div class="border border-border-light dark:border-border-dark rounded-lg p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <span class="font-semibold text-text-light dark:text-text-dark"><?= htmlspecialchars($note['author']) ?></span>
                        <span class="text-sm text-text-muted-light dark:text-text-muted-dark ml-2"><?= date('d.m.Y H:i', strtotime($note['created_at'])) ?></span>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                        <?= htmlspecialchars($note['note_type']) ?>
                    </span>
                </div>
                <p class="text-sm text-text-light dark:text-text-dark whitespace-pre-wrap"><?= htmlspecialchars($note['note']) ?></p>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($notes)): ?>
            <div class="text-center py-8 text-text-muted-light dark:text-text-muted-dark">
                Keine Notizen vorhanden
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('active', 'border-primary', 'text-primary');
        el.classList.add('border-transparent', 'text-text-muted-light', 'dark:text-text-muted-dark');
    });
    
    // Show selected tab
    document.getElementById('content-' + tabName).classList.remove('hidden');
    const btn = document.getElementById('tab-' + tabName);
    btn.classList.add('active', 'border-primary', 'text-primary');
    btn.classList.remove('border-transparent', 'text-text-muted-light', 'dark:text-text-muted-dark');
}
</script>
