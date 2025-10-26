<?php
// Modernes Dashboard mit echten Daten
ob_start();
?>

<!-- Error Message -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-2 border-red-500 dark:border-red-600 text-red-800 dark:text-red-200 px-6 py-4 rounded-lg shadow-lg">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-3xl">error</span>
            <div class="flex-1">
                <p class="font-semibold text-base mb-1">⚠️ Zugriff verweigert</p>
                <p class="text-sm"><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-red-600 dark:text-red-400 hover:text-red-800">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Welcome Header mit Gradient -->
<div class="mb-8">
    <div class="bg-gradient-to-r from-primary via-teal-500 to-cyan-500 rounded-2xl p-8 shadow-2xl">
        <div class="flex items-center justify-between">
            <div class="text-white">
                <h1 class="text-3xl font-bold mb-2">Willkommen zurück, <?= htmlspecialchars($user['first_name'] ?? 'User') ?>! 👋</h1>
                <p class="text-white/90 text-lg">Hier ist Ihre Übersicht für heute - <?= date('d.m.Y') ?></p>
            </div>
            <div class="hidden lg:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                    <span class="material-symbols-outlined text-6xl text-white">dashboard</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid - Moderne Cards mit Gradients -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Umsatz Card -->
    <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <span class="material-symbols-outlined text-4xl text-white/80">payments</span>
                <span class="text-xs font-semibold text-white/70 bg-white/20 px-2 py-1 rounded-full">Bezahlt</span>
            </div>
            <p class="text-white/80 text-sm font-medium mb-1">Gesamtumsatz</p>
            <p class="text-white text-3xl font-bold">€<?= number_format($stats['total_revenue'], 2, ',', '.') ?></p>
            <p class="text-white/70 text-xs mt-2"><?= $stats['total_invoices'] ?> Rechnungen gesamt</p>
        </div>
    </div>

    <!-- Kunden Card -->
    <div class="group relative overflow-hidden bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <span class="material-symbols-outlined text-4xl text-white/80">groups</span>
                <span class="text-xs font-semibold text-white/70 bg-white/20 px-2 py-1 rounded-full">Aktiv</span>
            </div>
            <p class="text-white/80 text-sm font-medium mb-1">Kunden</p>
            <p class="text-white text-3xl font-bold"><?= $stats['total_customers'] ?></p>
            <p class="text-white/70 text-xs mt-2">Kundenstamm</p>
        </div>
    </div>

    <!-- Offene Rechnungen Card -->
    <div class="group relative overflow-hidden bg-gradient-to-br from-orange-500 to-red-600 rounded-xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <span class="material-symbols-outlined text-4xl text-white/80">receipt_long</span>
                <span class="text-xs font-semibold text-white/70 bg-white/20 px-2 py-1 rounded-full">Offen</span>
            </div>
            <p class="text-white/80 text-sm font-medium mb-1">Offene Rechnungen</p>
            <p class="text-white text-3xl font-bold"><?= $stats['pending_invoices'] ?></p>
            <p class="text-white/70 text-xs mt-2">€<?= number_format($stats['pending_amount'], 2, ',', '.') ?> ausstehend</p>
        </div>
    </div>

    <!-- Module Card -->
    <div class="group relative overflow-hidden bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <span class="material-symbols-outlined text-4xl text-white/80">extension</span>
                <span class="text-xs font-semibold text-white/70 bg-white/20 px-2 py-1 rounded-full">Lizenziert</span>
            </div>
            <p class="text-white/80 text-sm font-medium mb-1">Aktive Module</p>
            <p class="text-white text-3xl font-bold"><?= $stats['active_modules'] ?></p>
            <p class="text-white/70 text-xs mt-2"><?= $stats['total_users'] ?> Benutzer</p>
        </div>
    </div>
</div>

<!-- Schnellzugriffe - Moderne 2-Spalten mit Hover-Effekten -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Linke Spalte: Schnellaktionen -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-primary/10 p-2 rounded-lg">
                <span class="material-symbols-outlined text-2xl text-primary">bolt</span>
            </div>
            <h3 class="text-xl font-bold text-text-light dark:text-text-dark">Schnellaktionen</h3>
        </div>
        
        <div class="space-y-3">
            <!-- Neue Rechnung -->
            <a href="/invoices" class="group flex items-center justify-between p-4 bg-gradient-to-r from-primary/5 to-teal-500/5 hover:from-primary/10 hover:to-teal-500/10 rounded-lg border border-primary/20 hover:border-primary/40 transition-all duration-200">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/20 p-2 rounded-lg group-hover:bg-primary/30 transition-colors">
                        <span class="material-symbols-outlined text-primary">receipt_long</span>
                    </div>
                    <div>
                        <p class="font-semibold text-text-light dark:text-text-dark">Neue Rechnung</p>
                        <p class="text-xs text-text-muted-light dark:text-text-muted-dark">Kunde abrechnen</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-primary opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </a>

            <!-- Neuer Kunde -->
            <a href="/customers" class="group flex items-center justify-between p-4 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 hover:from-blue-500/10 hover:to-indigo-500/10 rounded-lg border border-blue-500/20 hover:border-blue-500/40 transition-all duration-200">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-500/20 p-2 rounded-lg group-hover:bg-blue-500/30 transition-colors">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">person_add</span>
                    </div>
                    <div>
                        <p class="font-semibold text-text-light dark:text-text-dark">Neuer Kunde</p>
                        <p class="text-xs text-text-muted-light dark:text-text-muted-dark">Kunde anlegen</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </a>

            <!-- Zeit erfassen -->
            <a href="/time-tracking" class="group flex items-center justify-between p-4 bg-gradient-to-r from-purple-500/5 to-pink-500/5 hover:from-purple-500/10 hover:to-pink-500/10 rounded-lg border border-purple-500/20 hover:border-purple-500/40 transition-all duration-200">
                <div class="flex items-center gap-3">
                    <div class="bg-purple-500/20 p-2 rounded-lg group-hover:bg-purple-500/30 transition-colors">
                        <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">schedule</span>
                    </div>
                    <div>
                        <p class="font-semibold text-text-light dark:text-text-dark">Zeit erfassen</p>
                        <p class="text-xs text-text-muted-light dark:text-text-muted-dark">Stunden buchen</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-purple-600 dark:text-purple-400 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </a>

            <!-- Marketplace -->
            <a href="/marketplace" class="group flex items-center justify-between p-4 bg-gradient-to-r from-orange-500/5 to-red-500/5 hover:from-orange-500/10 hover:to-red-500/10 rounded-lg border border-orange-500/20 hover:border-orange-500/40 transition-all duration-200">
                <div class="flex items-center gap-3">
                    <div class="bg-orange-500/20 p-2 rounded-lg group-hover:bg-orange-500/30 transition-colors">
                        <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">store</span>
                    </div>
                    <div>
                        <p class="font-semibold text-text-light dark:text-text-dark">Marketplace</p>
                        <p class="text-xs text-text-muted-light dark:text-text-muted-dark">Module aktivieren</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-orange-600 dark:text-orange-400 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </a>
        </div>
    </div>

    <!-- Rechte Spalte: Letzte Aktivitäten / Tipps -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-primary/10 p-2 rounded-lg">
                <span class="material-symbols-outlined text-2xl text-primary">tips_and_updates</span>
            </div>
            <h3 class="text-xl font-bold text-text-light dark:text-text-dark">Tipps & Hinweise</h3>
        </div>
        
        <div class="space-y-4">
            <?php if ($stats['pending_invoices'] > 0): ?>
            <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">info</span>
                    <div>
                        <p class="font-semibold text-sm text-orange-800 dark:text-orange-200">Offene Rechnungen</p>
                        <p class="text-xs text-orange-700 dark:text-orange-300 mt-1">Sie haben <?= $stats['pending_invoices'] ?> offene Rechnung(en) im Wert von €<?= number_format($stats['pending_amount'], 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($stats['active_modules'] < 3): ?>
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">lightbulb</span>
                    <div>
                        <p class="font-semibold text-sm text-blue-800 dark:text-blue-200">Mehr Module entdecken</p>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">Erweitern Sie Ihr System mit zusätzlichen Modulen im Marketplace!</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="p-4 bg-primary/5 border border-primary/20 rounded-lg">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary">celebration</span>
                    <div>
                        <p class="font-semibold text-sm text-text-light dark:text-text-dark">System bereit!</p>
                        <p class="text-xs text-text-muted-light dark:text-text-muted-dark mt-1">Alle Systeme laufen einwandfrei. Viel Erfolg!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Dashboard';
$title = 'Dashboard - Business Manager';
require __DIR__ . '/layouts/app.php';
?>
