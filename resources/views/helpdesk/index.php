<?php
// Helpdesk Ticket-Übersicht
ob_start();
?>

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-4xl text-primary">support_agent</span>
            <div>
                <h2 class="text-3xl font-bold text-text-light dark:text-text-dark">Helpdesk</h2>
                <p class="text-text-muted-light dark:text-text-muted-dark">Ticket-Verwaltung & Support</p>
            </div>
        </div>
        <button onclick="document.getElementById('newTicketModal').classList.remove('hidden')" 
                class="flex items-center gap-2 bg-primary hover:bg-teal-600 text-white px-6 py-3 rounded-lg transition-colors shadow-lg">
            <span class="material-symbols-outlined">add</span>
            <span>Neues Ticket</span>
        </button>
    </div>
</div>

<!-- Error/Success Messages -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-2 border-red-500 dark:border-red-600 text-red-800 dark:text-red-200 px-6 py-4 rounded-lg">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-3xl">error</span>
            <p class="flex-1"><?= htmlspecialchars($_SESSION['error']) ?></p>
            <button onclick="this.parentElement.parentElement.remove()" class="text-red-600 dark:text-red-400">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-6 py-4 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                <p><?= htmlspecialchars($_SESSION['success']) ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-green-600 dark:text-green-400">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-2">
            <span class="material-symbols-outlined text-3xl opacity-80">inbox</span>
            <span class="text-2xl font-bold"><?= $stats['open'] ?></span>
        </div>
        <p class="text-sm opacity-90">Offen</p>
    </div>
    
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-2">
            <span class="material-symbols-outlined text-3xl opacity-80">pending</span>
            <span class="text-2xl font-bold"><?= $stats['in_progress'] ?></span>
        </div>
        <p class="text-sm opacity-90">In Bearbeitung</p>
    </div>
    
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-2">
            <span class="material-symbols-outlined text-3xl opacity-80">schedule</span>
            <span class="text-2xl font-bold"><?= $stats['waiting'] ?></span>
        </div>
        <p class="text-sm opacity-90">Warten auf Kunde</p>
    </div>
    
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-2">
            <span class="material-symbols-outlined text-3xl opacity-80">check_circle</span>
            <span class="text-2xl font-bold"><?= $stats['resolved'] ?></span>
        </div>
        <p class="text-sm opacity-90">Gelöst</p>
    </div>
</div>

<!-- Filter -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg p-6 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
                <option value="all">Alle</option>
                <option value="open" <?= ($status ?? 'all') === 'open' ? 'selected' : '' ?>>Offen</option>
                <option value="in_progress" <?= ($status ?? 'all') === 'in_progress' ? 'selected' : '' ?>>In Bearbeitung</option>
                <option value="waiting" <?= ($status ?? 'all') === 'waiting' ? 'selected' : '' ?>>Warten auf Kunde</option>
                <option value="resolved" <?= ($status ?? 'all') === 'resolved' ? 'selected' : '' ?>>Gelöst</option>
                <option value="closed" <?= ($status ?? 'all') === 'closed' ? 'selected' : '' ?>>Geschlossen</option>
            </select>
        </div>
        
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Priorität</label>
            <select name="priority" class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
                <option value="all">Alle</option>
                <option value="critical" <?= ($priority ?? 'all') === 'critical' ? 'selected' : '' ?>>Kritisch</option>
                <option value="high" <?= ($priority ?? 'all') === 'high' ? 'selected' : '' ?>>Hoch</option>
                <option value="normal" <?= ($priority ?? 'all') === 'normal' ? 'selected' : '' ?>>Normal</option>
                <option value="low" <?= ($priority ?? 'all') === 'low' ? 'selected' : '' ?>>Niedrig</option>
            </select>
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="px-6 py-2 bg-primary hover:bg-teal-600 text-white rounded-lg transition-colors">
                Filtern
            </button>
        </div>
    </form>
</div>

<!-- Tickets-Liste -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Ticket</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Titel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Kunde</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Priorität</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Zugewiesen</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Erstellt</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase">Aktionen</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-text-muted-light dark:text-text-muted-dark">
                        <span class="material-symbols-outlined text-6xl mb-4 opacity-50">inbox</span>
                        <p class="text-lg">Keine Tickets gefunden</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-semibold text-primary"><?= htmlspecialchars($ticket['ticket_number']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="/helpdesk/<?= $ticket['id'] ?>" class="text-text-light dark:text-text-dark hover:text-primary font-medium">
                                <?= htmlspecialchars($ticket['title']) ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?= $ticket['customer_name'] ? htmlspecialchars($ticket['customer_name']) : '-' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusColors = [
                                'open' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                'in_progress' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                'waiting' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            ];
                            $statusLabels = [
                                'open' => 'Offen',
                                'in_progress' => 'In Bearbeitung',
                                'waiting' => 'Wartet',
                                'resolved' => 'Gelöst',
                                'closed' => 'Geschlossen',
                            ];
                            $statusClass = $statusColors[$ticket['status']] ?? $statusColors['open'];
                            $statusLabel = $statusLabels[$ticket['status']] ?? $ticket['status'];
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $priorityColors = [
                                'critical' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                'normal' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                'low' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            ];
                            $priorityLabels = [
                                'critical' => 'Kritisch',
                                'high' => 'Hoch',
                                'normal' => 'Normal',
                                'low' => 'Niedrig',
                            ];
                            $priorityClass = $priorityColors[$ticket['priority']] ?? $priorityColors['normal'];
                            $priorityLabel = $priorityLabels[$ticket['priority']] ?? $ticket['priority'];
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $priorityClass ?>">
                                <?= $priorityLabel ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?= $ticket['assigned_name'] ?? '-' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted-light dark:text-text-muted-dark">
                            <?= date('d.m.Y H:i', strtotime($ticket['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="/helpdesk/<?= $ticket['id'] ?>" class="text-primary hover:text-teal-600">
                                <span class="material-symbols-outlined">visibility</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Neues Ticket -->
<div id="newTicketModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-2xl font-bold text-text-light dark:text-text-dark">Neues Ticket erstellen</h3>
            <button onclick="document.getElementById('newTicketModal').classList.add('hidden')" class="text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form method="POST" action="/helpdesk" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Titel *</label>
                <input type="text" name="title" required class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Beschreibung *</label>
                <textarea name="description" required rows="6" class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Priorität</label>
                    <select name="priority" class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
                        <option value="low">Niedrig</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">Hoch</option>
                        <option value="critical">Kritisch</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Kategorie</label>
                    <input type="text" name="category" placeholder="z.B. Technisch, Abrechnung..." class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="document.getElementById('newTicketModal').classList.add('hidden')" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-text-light dark:text-text-dark rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Abbrechen
                </button>
                <button type="submit" class="px-6 py-2 bg-primary hover:bg-teal-600 text-white rounded-lg">
                    Ticket erstellen
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Helpdesk';
$title = 'Helpdesk - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
