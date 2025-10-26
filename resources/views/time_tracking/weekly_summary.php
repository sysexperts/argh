<?php
$pageTitle = 'Wochenübersicht';
?>
<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($pageTitle) ?> - Business Manager</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#008080",
                        "background-dark": "#111827",
                        "surface-dark": "#1f2937",
                        "text-dark": "#e5e7eb",
                    },
                    fontFamily: { display: ["Poppins", "sans-serif"] },
                },
            },
        };
    </script>
</head>
<body class="font-display bg-background-dark text-text-dark">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-surface-dark p-6">
            <h1 class="text-2xl font-bold text-primary mb-8">Business Manager</h1>
            <nav>
                <a href="/dashboard" class="block px-4 py-2 rounded hover:bg-gray-700">Dashboard</a>
                <a href="/time-tracking" class="block px-4 py-2 rounded bg-primary text-white">Zeiterfassung</a>
                <a href="/auth/logout" class="block px-4 py-2 rounded hover:bg-gray-700 mt-4">Abmelden</a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <div class="max-w-6xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold">Wochenübersicht</h2>
                    <a href="/time-tracking" class="px-4 py-2 bg-gray-700 rounded hover:bg-gray-600">← Zurück</a>
                </div>

                <div class="bg-surface-dark p-6 rounded-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">
                            Woche: <?= date('d.m.Y', strtotime($summary['week_start'])) ?> - <?= date('d.m.Y', strtotime($summary['week_end'])) ?>
                        </h3>
                        <div class="flex gap-2">
                            <a href="?week_start=<?= date('Y-m-d', strtotime($summary['week_start'] . ' -7 days')) ?>" class="px-4 py-2 bg-gray-700 rounded hover:bg-gray-600">← Vorherige</a>
                            <a href="?week_start=<?= date('Y-m-d', strtotime($summary['week_start'] . ' +7 days')) ?>" class="px-4 py-2 bg-gray-700 rounded hover:bg-gray-600">Nächste →</a>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-6 mb-6">
                        <div class="bg-gray-800 p-4 rounded">
                            <p class="text-sm text-gray-400">Gesamtstunden</p>
                            <p class="text-3xl font-bold text-primary"><?= number_format($summary['total_hours'], 2) ?> h</p>
                        </div>
                        <div class="bg-gray-800 p-4 rounded">
                            <p class="text-sm text-gray-400">Überstunden</p>
                            <p class="text-3xl font-bold text-yellow-400"><?= number_format($summary['total_overtime'], 2) ?> h</p>
                        </div>
                        <div class="bg-gray-800 p-4 rounded">
                            <p class="text-sm text-gray-400">Arbeitstage</p>
                            <p class="text-3xl font-bold"><?= $summary['days_worked'] ?></p>
                        </div>
                        <div class="bg-gray-800 p-4 rounded">
                            <p class="text-sm text-gray-400">Ø Stunden/Tag</p>
                            <p class="text-3xl font-bold"><?= number_format($summary['average_hours'], 2) ?> h</p>
                        </div>
                    </div>

                    <?php if ($summary['total_hours'] > 48): ?>
                    <div class="p-4 rounded bg-red-900/20 border border-red-800 text-red-200 mb-4">
                        <strong>⚠️ Warnung:</strong> Die wöchentliche Arbeitszeit von 48 Stunden wurde überschritten (§ 3 ArbZG).
                    </div>
                    <?php endif; ?>
                </div>

                <div class="bg-surface-dark p-6 rounded-lg">
                    <h3 class="text-xl font-bold mb-4">Tagesübersicht</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-700">
                                    <th class="text-left py-3 px-4">Datum</th>
                                    <th class="text-left py-3 px-4">Wochentag</th>
                                    <th class="text-left py-3 px-4">Beginn</th>
                                    <th class="text-left py-3 px-4">Ende</th>
                                    <th class="text-left py-3 px-4">Gesamtstunden</th>
                                    <th class="text-left py-3 px-4">Überstunden</th>
                                    <th class="text-left py-3 px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $currentDate = $summary['week_start'];
                                for ($i = 0; $i < 7; $i++) {
                                    $dateStr = date('Y-m-d', strtotime($currentDate . " +$i days"));
                                    $dayEntry = null;
                                    foreach ($summary['entries'] as $entry) {
                                        if ($entry->getDate() === $dateStr) {
                                            $dayEntry = $entry;
                                            break;
                                        }
                                    }
                                    ?>
                                    <tr class="border-b border-gray-700">
                                        <td class="py-3 px-4"><?= date('d.m.Y', strtotime($dateStr)) ?></td>
                                        <td class="py-3 px-4"><?= strftime('%A', strtotime($dateStr)) ?></td>
                                        <?php if ($dayEntry): ?>
                                            <td class="py-3 px-4"><?= date('H:i', strtotime($dayEntry->getStartTime())) ?></td>
                                            <td class="py-3 px-4"><?= $dayEntry->getEndTime() ? date('H:i', strtotime($dayEntry->getEndTime())) : '-' ?></td>
                                            <td class="py-3 px-4 font-bold"><?= $dayEntry->getTotalHours() ? number_format($dayEntry->getTotalHours(), 2) . ' h' : '-' ?></td>
                                            <td class="py-3 px-4 <?= $dayEntry->getOvertimeHours() > 0 ? 'text-yellow-400' : '' ?>">
                                                <?= number_format($dayEntry->getOvertimeHours(), 2) ?> h
                                            </td>
                                            <td class="py-3 px-4">
                                                <?php if ($dayEntry->isCompleted()): ?>
                                                    <span class="px-2 py-1 bg-green-900 text-green-200 rounded text-sm">✓</span>
                                                <?php else: ?>
                                                    <span class="px-2 py-1 bg-blue-900 text-blue-200 rounded text-sm">▶</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php else: ?>
                                            <td class="py-3 px-4 text-gray-500" colspan="5">Kein Eintrag</td>
                                        <?php endif; ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
