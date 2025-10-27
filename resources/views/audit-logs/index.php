<?php
ob_start();
?>
<!-- Audit-Logs -->

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-4xl text-primary">shield</span>
        <h2 class="text-3xl font-bold text-text-light dark:text-text-dark">Audit-Logs</h2>
    </div>
    <p class="text-text-muted-light dark:text-text-muted-dark">
        GoBD-konforme, revisionssichere Protokollierung aller Systemaktivitäten
    </p>
</div>

<!-- Filter -->
<div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="/audit-logs" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Aktion</label>
            <select name="action" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg">
                <option value="">Alle</option>
                <option value="create" <?= ($filters['action'] ?? '') === 'create' ? 'selected' : '' ?>>Erstellt</option>
                <option value="update" <?= ($filters['action'] ?? '') === 'update' ? 'selected' : '' ?>>Geändert</option>
                <option value="delete" <?= ($filters['action'] ?? '') === 'delete' ? 'selected' : '' ?>>Gelöscht</option>
                <option value="login_success" <?= ($filters['action'] ?? '') === 'login_success' ? 'selected' : '' ?>>Login</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Entity</label>
            <select name="entity_type" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg">
                <option value="">Alle</option>
                <option value="invoice" <?= ($filters['entity_type'] ?? '') === 'invoice' ? 'selected' : '' ?>>Rechnungen</option>
                <option value="customer" <?= ($filters['entity_type'] ?? '') === 'customer' ? 'selected' : '' ?>>Kunden</option>
                <option value="user" <?= ($filters['entity_type'] ?? '') === 'user' ? 'selected' : '' ?>>Benutzer</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Von</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Bis</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg">
        </div>
        
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Filtern
            </button>
            <a href="/audit-logs" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">Gesamt</div>
        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300"><?= number_format($totalLogs ?? 0) ?></div>
    </div>
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <div class="text-sm text-green-600 dark:text-green-400 mb-1">Heute</div>
        <div class="text-2xl font-bold text-green-700 dark:text-green-300">
            <?= isset($logs) ? count(array_filter($logs, function($l) { return date('Y-m-d', strtotime($l['created_at'])) === date('Y-m-d'); })) : 0 ?>
        </div>
    </div>
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <div class="text-sm text-yellow-600 dark:text-yellow-400 mb-1">Warnungen</div>
        <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">
            <?= isset($logs) ? count(array_filter($logs, function($l) { return $l['severity'] === 'warning'; })) : 0 ?>
        </div>
    </div>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
        <div class="text-sm text-red-600 dark:text-red-400 mb-1">Fehler</div>
        <div class="text-2xl font-bold text-red-700 dark:text-red-300">
            <?= isset($logs) ? count(array_filter($logs, function($l) { return $l['severity'] === 'error'; })) : 0 ?>
        </div>
    </div>
</div>

<!-- Export Button -->
<div class="mb-4 flex justify-end">
    <a href="/audit-logs/export?<?= http_build_query($filters ?? []) ?>" class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
        <span class="material-symbols-outlined">download</span>
        CSV Export
    </a>
</div>

<!-- Logs Table -->
<div class="bg-card-light dark:bg-card-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Zeit</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Benutzer</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Aktion</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Entity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Beschreibung</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Severity</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                <?php if (isset($logs)): foreach ($logs as $log): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark whitespace-nowrap">
                        <?= date('d.m.Y H:i', strtotime($log['created_at'])) ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark">
                        <?= htmlspecialchars($log['user_name']) ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                        $actionColors = [
                            'create' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'update' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                            'delete' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                            'login_success' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                        ];
                        $actionClass = $actionColors[$log['action']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $actionClass ?>">
                            <?= htmlspecialchars($log['action']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark">
                        <?= htmlspecialchars($log['entity_type']) ?>
                        <?php if ($log['entity_id']): ?>
                        <span class="text-text-muted-light dark:text-text-muted-dark">#<?= $log['entity_id'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-text-light dark:text-text-dark">
                        <?= htmlspecialchars($log['description']) ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                        $severityColors = [
                            'info' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
                            'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'error' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                            'critical' => 'bg-red-200 text-red-900 dark:bg-red-900/50 dark:text-red-300',
                        ];
                        $severityClass = $severityColors[$log['severity']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $severityClass ?>">
                            <?= htmlspecialchars($log['severity']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                
                <?php if (!isset($logs) || empty($logs)): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-text-muted-light dark:text-text-muted-dark">
                        Keine Audit-Logs gefunden
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
<div class="mt-6 flex justify-center gap-2">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="/audit-logs?page=<?= $i ?>&<?= http_build_query($filters ?? []) ?>" 
           class="px-4 py-2 rounded-lg <?= $i === ($page ?? 1) ? 'bg-primary text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Audit-Logs';
$title = 'Audit-Logs - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
