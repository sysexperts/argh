<?php
// Dashboard Content (wird in Layout eingebettet)
ob_start();
?>
<!-- Mobile Header -->
<div class="lg:hidden mb-4">
    <h1 class="text-2xl font-semibold text-text-light dark:text-text-dark">
        Willkommen, <?= htmlspecialchars($user['name'] ?? 'User') ?>!
    </h1>
    <p class="text-sm text-text-muted-light dark:text-text-muted-dark">
        Hier ist Ihre Business-Übersicht für heute.
    </p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Revenue Card -->
    <div class="bg-card-light dark:bg-card-dark p-6 rounded shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-text-muted-light dark:text-text-muted-dark">Gesamtumsatz</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">€45.231,89</p>
            </div>
            <div class="bg-primary/20 text-primary p-3 rounded-full">
                <span class="material-symbols-outlined">payments</span>
            </div>
        </div>
        <p class="text-xs text-green-500 mt-2 flex items-center">
            <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
            +20,1% zum Vormonat
        </p>
    </div>

    <!-- New Clients Card -->
    <div class="bg-card-light dark:bg-card-dark p-6 rounded shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-text-muted-light dark:text-text-muted-dark">Neue Kunden</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">+<?= $stats['users'] ?? 12 ?></p>
            </div>
            <div class="bg-primary/20 text-primary p-3 rounded-full">
                <span class="material-symbols-outlined">person_add</span>
            </div>
        </div>
        <p class="text-xs text-green-500 mt-2 flex items-center">
            <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
            +5 zum Vormonat
        </p>
    </div>

    <!-- Open Tickets Card -->
    <div class="bg-card-light dark:bg-card-dark p-6 rounded shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-text-muted-light dark:text-text-muted-dark">Offene Tickets</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">8</p>
            </div>
            <div class="bg-primary/20 text-primary p-3 rounded-full">
                <span class="material-symbols-outlined">support_agent</span>
            </div>
        </div>
        <p class="text-xs text-red-500 mt-2 flex items-center">
            <span class="material-symbols-outlined text-sm mr-1">trending_down</span>
            -2 seit gestern
        </p>
    </div>

    <!-- Pending Invoices Card -->
    <div class="bg-card-light dark:bg-card-dark p-6 rounded shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-text-muted-light dark:text-text-muted-dark">Offene Rechnungen</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">21</p>
            </div>
            <div class="bg-primary/20 text-primary p-3 rounded-full">
                <span class="material-symbols-outlined">request_quote</span>
            </div>
        </div>
        <p class="text-xs text-text-muted-light dark:text-text-muted-dark mt-2 flex items-center">
            <span class="material-symbols-outlined text-sm mr-1">hourglass_top</span>
            €12.500 ausstehend
        </p>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-card-light dark:bg-card-dark p-6 rounded shadow-sm">
    <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-6">Schnellzugriffe</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Neue Rechnung -->
        <button class="group relative overflow-hidden bg-gradient-to-br from-primary to-teal-600 hover:from-primary/90 hover:to-teal-600/90 text-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="text-left">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-3xl">receipt_long</span>
                        <h4 class="font-semibold text-lg">Neue Rechnung</h4>
                    </div>
                    <p class="text-sm text-white/80">Kunde abrechnen</p>
                </div>
                <span class="material-symbols-outlined text-2xl opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </div>
        </button>

        <!-- Ausgabe erfassen -->
        <button class="group relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-500/90 hover:to-blue-600/90 text-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="text-left">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-3xl">credit_card</span>
                        <h4 class="font-semibold text-lg">Ausgabe</h4>
                    </div>
                    <p class="text-sm text-white/80">Kosten erfassen</p>
                </div>
                <span class="material-symbols-outlined text-2xl opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </div>
        </button>

        <!-- Zeit erfassen -->
        <button class="group relative overflow-hidden bg-gradient-to-br from-purple-500 to-purple-600 hover:from-purple-500/90 hover:to-purple-600/90 text-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="text-left">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-3xl">schedule</span>
                        <h4 class="font-semibold text-lg">Zeit erfassen</h4>
                    </div>
                    <p class="text-sm text-white/80">Stunden buchen</p>
                </div>
                <span class="material-symbols-outlined text-2xl opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </div>
        </button>

        <!-- Neuer Kunde -->
        <button class="group relative overflow-hidden bg-gradient-to-br from-orange-500 to-orange-600 hover:from-orange-500/90 hover:to-orange-600/90 text-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="text-left">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-3xl">person_add</span>
                        <h4 class="font-semibold text-lg">Neuer Kunde</h4>
                    </div>
                    <p class="text-sm text-white/80">Kunde anlegen</p>
                </div>
                <span class="material-symbols-outlined text-2xl opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </div>
        </button>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Dashboard';
$title = 'Dashboard - Business Manager';
require __DIR__ . '/layouts/app.php';
?>
