<!-- Partner Console Dashboard -->

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-2xl">admin_panel_settings</span>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-text-light dark:text-text-dark">Partner Console</h2>
                <p class="text-sm text-text-muted-light dark:text-text-muted-dark">sys-experts.de Admin-Bereich</p>
            </div>
        </div>
        <a href="/partner-console/tenants/create" class="flex items-center gap-2 bg-gradient-to-r from-primary to-teal-600 text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all">
            <span class="material-symbols-outlined">add</span>
            Neuer Mandant
        </a>
    </div>
</div>

<!-- Statistiken -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-card-light dark:bg-card-dark rounded-lg p-4 border border-border-light dark:border-border-dark">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-text-muted-light dark:text-text-muted-dark">Mandanten</span>
            <span class="material-symbols-outlined text-primary">business</span>
        </div>
        <div class="text-2xl font-bold text-text-light dark:text-text-dark"><?= $stats['total_tenants'] ?></div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-lg p-4 border border-border-light dark:border-border-dark">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-text-muted-light dark:text-text-muted-dark">Aktiv</span>
            <span class="material-symbols-outlined text-green-500">check_circle</span>
        </div>
        <div class="text-2xl font-bold text-green-500"><?= $stats['active_tenants'] ?></div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-lg p-4 border border-border-light dark:border-border-dark">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-text-muted-light dark:text-text-muted-dark">Testphase</span>
            <span class="material-symbols-outlined text-yellow-500">schedule</span>
        </div>
        <div class="text-2xl font-bold text-yellow-500"><?= $stats['trial_tenants'] ?></div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-lg p-4 border border-border-light dark:border-border-dark">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-text-muted-light dark:text-text-muted-dark">Benutzer</span>
            <span class="material-symbols-outlined text-primary">group</span>
        </div>
        <div class="text-2xl font-bold text-text-light dark:text-text-dark"><?= $stats['total_users'] ?></div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-lg p-4 border border-border-light dark:border-border-dark">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-text-muted-light dark:text-text-muted-dark">Lizenzen</span>
            <span class="material-symbols-outlined text-primary">extension</span>
        </div>
        <div class="text-2xl font-bold text-text-light dark:text-text-dark"><?= $stats['total_licenses'] ?></div>
    </div>
</div>

<!-- Mandanten-Liste -->
<div class="bg-card-light dark:bg-card-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark">
    <div class="p-4 border-b border-border-light dark:border-border-dark">
        <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Alle Mandanten</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Firma</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Domain</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Module</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Heartbeat</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Aktionen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                <?php foreach ($tenants as $tenant): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <td class="px-4 py-3">
                        <div>
                            <div class="font-semibold text-text-light dark:text-text-dark"><?= htmlspecialchars($tenant['company_name']) ?></div>
                            <div class="text-xs text-text-muted-light dark:text-text-muted-dark"><?= htmlspecialchars($tenant['contact_email']) ?></div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <a href="https://<?= htmlspecialchars($tenant['domain']) ?>" target="_blank" class="text-primary hover:underline text-sm">
                            <?= htmlspecialchars($tenant['domain']) ?>
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                        $statusColors = [
                            'active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'trial' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                            'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
                        ];
                        $statusClass = $statusColors[$tenant['tenant_status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $statusClass ?>">
                            <?= ucfirst($tenant['tenant_status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark">
                        <?= $tenant['user_count'] ?? 0 ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark">
                        <?= $tenant['active_modules'] ?? 0 ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($tenant['last_heartbeat_time']): ?>
                            <?php
                            $heartbeatTime = strtotime($tenant['last_heartbeat_time']);
                            $diff = time() - $heartbeatTime;
                            $isOnline = $diff < 600; // 10 Minuten
                            ?>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full <?= $isOnline ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                                <span class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                    <?= date('d.m.Y H:i', $heartbeatTime) ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <span class="text-xs text-text-muted-light dark:text-text-muted-dark">Nie</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <a href="/partner-console/tenants/<?= $tenant['id'] ?>" class="text-primary hover:text-primary/80 text-sm font-medium">
                            Details →
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($tenants)): ?>
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-text-muted-light dark:text-text-muted-dark">
                        Noch keine Mandanten vorhanden.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
