<?php
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="bg-gradient-to-r from-primary via-purple-500 to-pink-500 rounded-2xl p-8 shadow-2xl">
        <div class="flex items-center justify-between">
            <div class="text-white">
                <h1 class="text-3xl font-bold mb-2">Zeiterfassung ⏱️</h1>
                <p class="text-white/90 text-lg">Erfassen Sie Ihre Arbeitszeiten</p>
            </div>
            <div class="hidden lg:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                    <span class="material-symbols-outlined text-6xl text-white">schedule</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Entry -->
<?php if ($activeEntry): ?>
<div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-500 rounded-xl p-6 mb-6">
    <h3 class="text-xl font-bold text-green-800 dark:text-green-200 mb-4">⏱️ Aktive Zeiterfassung</h3>
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <p class="text-sm text-green-700 dark:text-green-300">Beginn</p>
            <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                <?= date('H:i', strtotime($activeEntry['start_time'])) ?> Uhr
            </p>
        </div>
        <div>
            <p class="text-sm text-green-700 dark:text-green-300">Laufzeit</p>
            <p class="text-2xl font-bold text-green-900 dark:text-green-100" id="activeTimer">00:00</p>
        </div>
    </div>
    <button onclick="stopTracking()" class="w-full bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
        Zeiterfassung beenden
    </button>
</div>
<?php else: ?>
<div class="bg-card-light dark:bg-card-dark rounded-xl p-6 mb-6">
    <h3 class="text-xl font-bold text-text-light dark:text-text-dark mb-4">Zeiterfassung starten</h3>
    <div class="flex gap-4">
        <button onclick="startTracking()" class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
            ▶ Jetzt starten
        </button>
        <button onclick="openManualModal()" class="flex-1 bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary/90 transition">
            📝 Manuell eintragen
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Filter & Export -->
<div class="bg-card-light dark:bg-card-dark rounded-xl p-6 mb-6">
    <div class="flex justify-between items-center">
        <div class="flex gap-4">
            <input type="date" id="startDate" value="<?= $startDate ?>" 
                   class="px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
            <input type="date" id="endDate" value="<?= $endDate ?>"
                   class="px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
            <button onclick="filterEntries()" class="bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary/90 transition">
                Filtern
            </button>
        </div>
        <div class="flex gap-2">
            <button onclick="exportCSV()" class="bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-700 transition">
                CSV Export
            </button>
            <button onclick="exportPDF()" class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                PDF Export
            </button>
        </div>
    </div>
</div>

<!-- Entries Table -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-background-light dark:bg-background-dark">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase">Datum</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase">Beginn</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase">Ende</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase">Dauer</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase">Beschreibung</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase">Aktionen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                <?php if (empty($entries)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-text-muted-light dark:text-text-muted-dark">
                        Keine Zeiteinträge vorhanden
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($entries as $entry): ?>
                    <tr class="hover:bg-background-light dark:hover:bg-background-dark transition">
                        <td class="px-6 py-4 text-sm text-text-light dark:text-text-dark">
                            <?= isset($entry['date']) ? date('d.m.Y', strtotime($entry['date'])) : date('d.m.Y', strtotime($entry['start_time'])) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-light dark:text-text-dark">
                            <?= date('H:i', strtotime($entry['start_time'])) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-light dark:text-text-dark">
                            <?= $entry['end_time'] ? date('H:i', strtotime($entry['end_time'])) : '-' ?>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-text-light dark:text-text-dark">
                            <?php if ($entry['end_time']): ?>
                                <?php
                                try {
                                    $start = new DateTime($entry['start_time']);
                                    $end = new DateTime($entry['end_time']);
                                    $diff = $start->diff($end);
                                    echo $diff->format('%H:%I');
                                } catch (Exception $e) {
                                    echo '-';
                                }
                                ?>
                            <?php else: ?>
                                <span class="text-green-600">Läuft...</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-muted-light dark:text-text-muted-dark">
                            <?= htmlspecialchars($entry['notes'] ?? $entry['description'] ?? '-') ?>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="deleteEntry(<?= $entry['id'] ?>)" 
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Manual Entry Modal -->
<div id="manualModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-text-light dark:text-text-dark mb-6">Manueller Zeiteintrag</h3>
        <form id="manualForm" onsubmit="createManualEntry(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Datum</label>
                    <input type="date" name="date" required max="<?= date('Y-m-d') ?>"
                           class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Von</label>
                        <input type="time" name="start_time" required
                               class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Bis</label>
                        <input type="time" name="end_time" required
                               class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Beschreibung (optional)</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark"></textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-primary/90 transition">
                    Erstellen
                </button>
                <button type="button" onclick="closeManualModal()" class="flex-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                    Abbrechen
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Timer für aktive Zeiterfassung
<?php if ($activeEntry): ?>
setInterval(() => {
    const start = new Date('<?= $activeEntry['start_time'] ?>');
    const now = new Date();
    const diff = now - start;
    const hours = Math.floor(diff / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);
    document.getElementById('activeTimer').textContent = 
        String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
}, 1000);
<?php endif; ?>

// Start tracking
async function startTracking() {
    try {
        const response = await fetch('/api/time-tracking/start', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({description: ''})
        });
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Fehler: ' + result.message);
        }
    } catch (error) {
        alert('Fehler beim Starten der Zeiterfassung');
    }
}

// Stop tracking
async function stopTracking() {
    try {
        const response = await fetch('/api/time-tracking/stop', {
            method: 'POST'
        });
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Fehler: ' + result.message);
        }
    } catch (error) {
        alert('Fehler beim Beenden der Zeiterfassung');
    }
}

// Manual entry modal
function openManualModal() {
    document.getElementById('manualModal').classList.remove('hidden');
}

function closeManualModal() {
    document.getElementById('manualModal').classList.add('hidden');
    document.getElementById('manualForm').reset();
}

// Create manual entry
async function createManualEntry(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {
        start_time: formData.get('date') + ' ' + formData.get('start_time'),
        end_time: formData.get('date') + ' ' + formData.get('end_time'),
        description: formData.get('description')
    };
    
    try {
        const response = await fetch('/api/time-tracking/manual', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Fehler: ' + result.message);
        }
    } catch (error) {
        alert('Fehler beim Erstellen des Eintrags');
    }
}

// Delete entry
async function deleteEntry(id) {
    if (!confirm('Möchten Sie diesen Eintrag wirklich löschen?')) return;
    
    try {
        const response = await fetch(`/api/time-tracking/${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Fehler: ' + result.message);
        }
    } catch (error) {
        alert('Fehler beim Löschen');
    }
}

// Filter
function filterEntries() {
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    window.location.href = `/time-tracking?start_date=${start}&end_date=${end}`;
}

// Export CSV
function exportCSV() {
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    window.location.href = `/time-tracking/export/csv?start_date=${start}&end_date=${end}`;
}

// Export PDF
function exportPDF() {
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    window.location.href = `/time-tracking/export/pdf?start_date=${start}&end_date=${end}`;
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Zeiterfassung';
$title = 'Zeiterfassung - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
