<?php
// Helpdesk Ticket-Details
ob_start();
?>

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/helpdesk" class="text-text-muted-light dark:text-text-muted-dark hover:text-primary">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-text-light dark:text-text-dark">
                    <?= htmlspecialchars($ticket['ticket_number']) ?>
                </h2>
                <p class="text-text-muted-light dark:text-text-muted-dark"><?= htmlspecialchars($ticket['title']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-6 py-4 rounded-lg">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Ticket Details -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold mb-4">Beschreibung</h3>
            <div class="prose dark:prose-invert max-w-none">
                <?= nl2br(htmlspecialchars($ticket['description'])) ?>
            </div>
        </div>

        <!-- Comments -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold mb-4">Kommentare (<?= count($comments) ?>)</h3>
            
            <div class="space-y-4 mb-6">
                <?php foreach ($comments as $comment): ?>
                    <div class="border-l-4 border-primary pl-4 py-2">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold"><?= htmlspecialchars($comment['user_name']) ?></span>
                            <span class="text-sm text-text-muted-light dark:text-text-muted-dark">
                                <?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?>
                            </span>
                        </div>
                        <p class="text-sm"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                        <?php if ($comment['is_internal']): ?>
                            <span class="inline-block mt-2 px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 text-xs rounded">
                                Intern
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Add Comment Form -->
            <form method="POST" action="/helpdesk/<?= $ticket['id'] ?>/comments" class="space-y-4">
                <textarea name="comment" rows="4" placeholder="Kommentar hinzufügen..." required 
                          class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg"></textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_internal" class="rounded">
                        <span class="text-sm">Interner Kommentar</span>
                    </label>
                    <button type="submit" class="px-6 py-2 bg-primary hover:bg-teal-600 text-white rounded-lg">
                        Kommentar hinzufügen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Status & Actions -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold mb-4">Status</h3>
            
            <form method="POST" action="/helpdesk/<?= $ticket['id'] ?>/status" class="space-y-4">
                <select name="status" class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
                    <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Offen</option>
                    <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Bearbeitung</option>
                    <option value="waiting" <?= $ticket['status'] === 'waiting' ? 'selected' : '' ?>>Warten auf Kunde</option>
                    <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>Gelöst</option>
                    <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Geschlossen</option>
                </select>
                <button type="submit" class="w-full px-4 py-2 bg-primary hover:bg-teal-600 text-white rounded-lg">
                    Status aktualisieren
                </button>
            </form>
        </div>

        <!-- Assignment -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold mb-4">Zuweisung</h3>
            
            <form method="POST" action="/helpdesk/<?= $ticket['id'] ?>/assign" class="space-y-4">
                <select name="assigned_to" class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
                    <option value="">Nicht zugewiesen</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $ticket['assigned_to'] == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="w-full px-4 py-2 bg-primary hover:bg-teal-600 text-white rounded-lg">
                    Zuweisen
                </button>
            </form>
        </div>

        <!-- Info -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold mb-4">Informationen</h3>
            
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-text-muted-light dark:text-text-muted-dark">Priorität:</span>
                    <span class="font-semibold"><?= ucfirst($ticket['priority']) ?></span>
                </div>
                <div>
                    <span class="text-text-muted-light dark:text-text-muted-dark">Kategorie:</span>
                    <span class="font-semibold"><?= $ticket['category'] ?? '-' ?></span>
                </div>
                <div>
                    <span class="text-text-muted-light dark:text-text-muted-dark">Kunde:</span>
                    <span class="font-semibold"><?= $ticket['customer_name'] ?? '-' ?></span>
                </div>
                <div>
                    <span class="text-text-muted-light dark:text-text-muted-dark">Erstellt von:</span>
                    <span class="font-semibold"><?= htmlspecialchars($ticket['creator_name']) ?></span>
                </div>
                <div>
                    <span class="text-text-muted-light dark:text-text-muted-dark">Erstellt am:</span>
                    <span class="font-semibold"><?= date('d.m.Y H:i', strtotime($ticket['created_at'])) ?></span>
                </div>
                <?php if ($ticket['resolved_at']): ?>
                <div>
                    <span class="text-text-muted-light dark:text-text-muted-dark">Gelöst am:</span>
                    <span class="font-semibold"><?= date('d.m.Y H:i', strtotime($ticket['resolved_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Ticket ' . $ticket['ticket_number'];
$title = 'Ticket ' . $ticket['ticket_number'] . ' - Helpdesk';
require __DIR__ . '/../layouts/app.php';
?>
