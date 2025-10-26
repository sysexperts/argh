<?php
$title = 'Zeiterfassung';
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
ob_start();
?>
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-text-light dark:text-text-dark">Zeiterfassung</h2>
                    <div class="flex gap-2">
        <a href="/time-tracking/weekly-summary" class="px-4 py-2 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded hover:bg-background-light dark:hover:bg-background-dark text-text-light dark:text-text-dark">Wochenübersicht</a>
        <a href="/time-tracking/export/csv?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="px-4 py-2 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded hover:bg-background-light dark:hover:bg-background-dark text-text-light dark:text-text-dark">CSV Export</a>
        <a href="/time-tracking/export/pdf?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="px-4 py-2 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded hover:bg-background-light dark:hover:bg-background-dark text-text-light dark:text-text-dark">PDF Export</a>
    </div>
</div>

<?php if ($success): ?>
<div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded">
    <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if ($activeEntry): ?>
<div class="bg-card-light dark:bg-card-dark p-6 rounded-lg mb-6 border-2 border-primary shadow-sm">
    <h3 class="text-xl font-bold mb-4 text-text-light dark:text-text-dark">Aktive Arbeitszeit</h3>
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div>
            <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Arbeitsbeginn</p>
            <p class="text-lg font-bold text-text-light dark:text-text-dark"><?= date('H:i', strtotime($activeEntry->getStartTime())) ?> Uhr</p>
        </div>
        <div>
            <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Status</p>
            <p class="text-lg font-bold">
                <?php if ($activeEntry->isPaused()): ?>
                    <span class="text-yellow-500">⏸ Pausiert</span>
                <?php else: ?>
                    <span class="text-green-500">▶ Aktiv</span>
                <?php endif; ?>
            </p>
        </div>
        <div>
            <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Pausen</p>
            <p class="text-lg font-bold text-text-light dark:text-text-dark"><?= count($activeEntry->getBreaks()) ?></p>
        </div>
    </div>
    <div class="flex gap-2">
        <?php if ($activeEntry->isPaused()): ?>
        <?php
        $activeBreak = null;
        foreach ($activeEntry->getBreaks() as $break) {
            if (!$break['end_time']) {
                $activeBreak = $break;
                break;
            }
        }
        ?>
        <?php if ($activeBreak): ?>
        <form method="POST" action="/time-tracking/<?= $activeEntry->getId() ?>/break/<?= $activeBreak['id'] ?>/end">
            <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded hover:bg-green-700 font-bold shadow-sm">
                Pause beenden
            </button>
        </form>
        <?php endif; ?>
        <?php else: ?>
        <form method="POST" action="/time-tracking/<?= $activeEntry->getId() ?>/break/start">
            <button type="submit" class="px-6 py-3 bg-yellow-600 text-white rounded hover:bg-yellow-700 font-bold shadow-sm">
                Pause starten
            </button>
        </form>
        <form method="POST" action="/time-tracking/<?= $activeEntry->getId() ?>/end">
            <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded hover:bg-red-700 font-bold shadow-sm">
                Arbeitsende erfassen
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="bg-card-light dark:bg-card-dark p-6 rounded-lg mb-6 shadow-sm">
    <h3 class="text-xl font-bold mb-4 text-text-light dark:text-text-dark">Arbeitsbeginn erfassen</h3>
    <form method="POST" action="/time-tracking/start" class="flex gap-4">
        <input type="text" name="notes" placeholder="Notizen (optional)" class="flex-1 px-4 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark focus:border-primary focus:outline-none text-text-light dark:text-text-dark"/>
        <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-opacity-90 font-bold shadow-sm">
            Arbeit starten
        </button>
    </form>
</div>
<?php endif; ?>

<div class="bg-card-light dark:bg-card-dark p-6 rounded-lg shadow-sm">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-text-light dark:text-text-dark">Zeiteinträge</h3>
        <form method="GET" class="flex gap-2">
            <input type="date" name="start_date" value="<?= $startDate ?>" class="px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark text-text-light dark:text-text-dark"/>
            <input type="date" name="end_date" value="<?= $endDate ?>" class="px-3 py-2 bg-background-light dark:bg-background-dark rounded border border-border-light dark:border-border-dark text-text-light dark:text-text-dark"/>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-opacity-90 shadow-sm">Filtern</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-border-light dark:border-border-dark">
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Datum</th>
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Beginn</th>
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Ende</th>
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Pausen</th>
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Gesamtstunden</th>
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Überstunden</th>
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Status</th>
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entries)): ?>
                <tr>
                    <td colspan="8" class="text-center py-8 text-text-muted-light dark:text-text-muted-dark">Keine Einträge vorhanden</td>
                </tr>
                <?php else: ?>
                <?php foreach ($entries as $entry): ?>
                <tr class="border-b border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark">
                    <td class="py-3 px-4 text-text-light dark:text-text-dark"><?= date('d.m.Y', strtotime($entry->getDate())) ?></td>
                    <td class="py-3 px-4 text-text-light dark:text-text-dark"><?= date('H:i', strtotime($entry->getStartTime())) ?></td>
                    <td class="py-3 px-4 text-text-light dark:text-text-dark"><?= $entry->getEndTime() ? date('H:i', strtotime($entry->getEndTime())) : '-' ?></td>
                    <td class="py-3 px-4 text-text-light dark:text-text-dark"><?= count($entry->getBreaks()) ?></td>
                    <td class="py-3 px-4 font-bold text-text-light dark:text-text-dark"><?= $entry->getTotalHours() ? number_format($entry->getTotalHours(), 2) . ' h' : '-' ?></td>
                    <td class="py-3 px-4 text-text-light dark:text-text-dark <?= $entry->getOvertimeHours() > 0 ? 'text-yellow-500' : '' ?>">
                        <?= number_format($entry->getOvertimeHours(), 2) ?> h
                    </td>
                    <td class="py-3 px-4">
                        <?php if ($entry->isCompleted()): ?>
                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-sm">Abgeschlossen</span>
                        <?php elseif ($entry->isPaused()): ?>
                            <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded text-sm">Pausiert</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-sm">Aktiv</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-4">
                        <a href="/time-tracking/<?= $entry->getId() ?>" class="text-primary hover:underline">Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
