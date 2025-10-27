<?php
$title = 'Zeiteintrag Details';
ob_start();
?>
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-text-light dark:text-text-dark">Zeiteintrag Details</h2>
    <a href="/time-tracking" class="px-4 py-2 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded hover:bg-background-light dark:hover:bg-background-dark text-text-light dark:text-text-dark">← Zurück</a>
</div>

<?php if (!empty($violations)): ?>
<div class="mb-6 space-y-2">
    <?php foreach ($violations as $violation): ?>
    <div class="p-4 rounded <?= $violation['severity'] === 'error' ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200' : 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200' ?>">
        <strong>⚠️ <?= $violation['severity'] === 'error' ? 'Verstoß' : 'Warnung' ?>:</strong>
        <?= htmlspecialchars($violation['message']) ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="bg-card-light dark:bg-card-dark p-6 rounded-lg mb-6 shadow-sm">
    <h3 class="text-xl font-bold mb-4 text-text-light dark:text-text-dark">Übersicht</h3>
    <div class="grid grid-cols-2 gap-6">
        <div>
            <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Datum</p>
            <p class="text-lg font-bold text-text-light dark:text-text-dark"><?= date('d.m.Y', strtotime($entry->getDate())) ?></p>
        </div>
        <div>
            <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Status</p>
            <p class="text-lg font-bold">
                <?php if ($entry->isCompleted()): ?>
                    <span class="text-green-500">✓ Abgeschlossen</span>
                <?php elseif ($entry->isPaused()): ?>
                    <span class="text-yellow-500">⏸ Pausiert</span>
                <?php else: ?>
                    <span class="text-blue-500">▶ Aktiv</span>
                <?php endif; ?>
            </p>
        </div>
        <div>
            <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Arbeitsbeginn</p>
            <p class="text-lg font-bold text-text-light dark:text-text-dark"><?= date('H:i', strtotime($entry->getStartTime())) ?> Uhr</p>
        </div>
        <div>
            <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Arbeitsende</p>
            <p class="text-lg font-bold text-text-light dark:text-text-dark"><?= $entry->getEndTime() ? date('H:i', strtotime($entry->getEndTime())) . ' Uhr' : '-' ?></p>
        </div>
        <div>
            <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Gesamtstunden</p>
            <p class="text-2xl font-bold text-primary"><?= $entry->getTotalHours() ? number_format($entry->getTotalHours(), 2) . ' h' : '-' ?></p>
        </div>
        <div>
            <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Überstunden</p>
            <p class="text-2xl font-bold <?= $entry->getOvertimeHours() > 0 ? 'text-yellow-500' : 'text-text-muted-light dark:text-text-muted-dark' ?>">
                <?= number_format($entry->getOvertimeHours(), 2) ?> h
            </p>
        </div>
    </div>

    <?php if ($entry->getNotes()): ?>
    <div class="mt-6">
        <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Notizen</p>
        <p class="mt-1 text-text-light dark:text-text-dark"><?= nl2br(htmlspecialchars($entry->getNotes())) ?></p>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($entry->getBreaks())): ?>
<div class="bg-card-light dark:bg-card-dark p-6 rounded-lg shadow-sm">
    <h3 class="text-xl font-bold mb-4 text-text-light dark:text-text-dark">Pausen</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-border-light dark:border-border-dark">
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Start</th>
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Ende</th>
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Dauer</th>
                    <th class="text-left py-3 px-4 text-text-muted-light dark:text-text-muted-dark">Typ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entry->getBreaks() as $break): ?>
                <tr class="border-b border-border-light dark:border-border-dark">
                    <td class="py-3 px-4 text-text-light dark:text-text-dark"><?= date('H:i', strtotime($break['start_time'])) ?></td>
                    <td class="py-3 px-4 text-text-light dark:text-text-dark"><?= $break['end_time'] ? date('H:i', strtotime($break['end_time'])) : 'Läuft...' ?></td>
                    <td class="py-3 px-4 text-text-light dark:text-text-dark"><?= $break['duration_minutes'] ? $break['duration_minutes'] . ' min' : '-' ?></td>
                    <td class="py-3 px-4 text-text-light dark:text-text-dark">
                        <?php
                        $typeLabel = match($break['break_type']) {
                            'lunch' => 'Mittagspause',
                            'regular' => 'Pause',
                            default => $break['break_type']
                        };
                        echo htmlspecialchars($typeLabel);
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
