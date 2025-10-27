<?php
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="bg-gradient-to-r from-primary via-blue-500 to-indigo-500 rounded-2xl p-8 shadow-2xl">
        <div class="flex items-center justify-between">
            <div class="text-white">
                <h1 class="text-3xl font-bold mb-2">Benutzerverwaltung 👥</h1>
                <p class="text-white/90 text-lg">Verwalten Sie die Benutzer Ihres Unternehmens</p>
            </div>
            <div class="hidden lg:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                    <span class="material-symbols-outlined text-6xl text-white">group</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-primary/10 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl text-primary">group</span>
            </div>
            <div>
                <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Gesamt</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark"><?= count($users) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-green-500/10 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl text-green-600">check_circle</span>
            </div>
            <div>
                <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Aktiv</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">
                    <?= count(array_filter($users, fn($u) => $u['is_active'])) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-purple-500/10 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl text-purple-600">admin_panel_settings</span>
            </div>
            <div>
                <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Admins</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">
                    <?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-blue-500/10 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl text-blue-600">person</span>
            </div>
            <div>
                <p class="text-sm text-text-muted-light dark:text-text-muted-dark">Benutzer</p>
                <p class="text-2xl font-bold text-text-light dark:text-text-dark">
                    <?= count(array_filter($users, fn($u) => $u['role'] === 'user')) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <input type="text" 
               id="searchInput" 
               placeholder="Benutzer suchen..." 
               class="px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-card-light dark:bg-card-dark text-text-light dark:text-text-dark">
    </div>
    <button onclick="alert('Benutzer erstellen - Feature kommt bald!')" 
            class="bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary/90 transition flex items-center gap-2">
        <span class="material-symbols-outlined">add</span>
        Neuer Benutzer
    </button>
</div>

<!-- Users Table -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-background-light dark:bg-background-dark">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Benutzer
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        E-Mail
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Rolle
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Letzter Login
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Aktionen
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                <?php foreach ($users as $u): ?>
                <tr class="hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                                <?= strtoupper(substr($u['first_name'] ?? $u['email'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-semibold text-text-light dark:text-text-dark">
                                    <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
                                </p>
                                <p class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                    ID: <?= $u['id'] ?>
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-text-light dark:text-text-dark">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($u['role'] === 'admin'): ?>
                            <span class="px-3 py-1 bg-purple-500 text-white text-xs font-semibold rounded-full">
                                Admin
                            </span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-full">
                                Benutzer
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($u['is_active']): ?>
                            <span class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                Aktiv
                            </span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-gray-500 text-white text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                <span class="material-symbols-outlined text-sm">cancel</span>
                                Inaktiv
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-text-muted-light dark:text-text-muted-dark">
                        <?= $u['last_login'] ? date('d.m.Y H:i', strtotime($u['last_login'])) : 'Nie' ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button onclick="alert('Bearbeiten - Feature kommt bald!')" 
                                    class="p-2 text-primary hover:bg-primary/10 rounded-lg transition">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                            <button onclick="alert('Löschen - Feature kommt bald!')" 
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Simple search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Benutzerverwaltung';
$title = 'Benutzerverwaltung - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
