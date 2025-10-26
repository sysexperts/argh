<?php
// User Management View
ob_start();
?>

<!-- Header mit Button -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-text-light dark:text-text-dark">Benutzerverwaltung</h2>
        <p class="text-sm text-text-muted-light dark:text-text-muted-dark mt-1">
            Verwalten Sie alle Benutzer Ihres Systems
        </p>
    </div>
    <button @click="showCreateModal = true" class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg shadow-lg hover:shadow-xl transition-all">
        <span class="material-symbols-outlined">person_add</span>
        <span class="font-semibold">Neuer Benutzer</span>
    </button>
</div>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            <p class="text-sm"><?= htmlspecialchars($_SESSION['success']) ?></p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-600 dark:text-green-400 hover:text-green-800">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['errors'])): ?>
    <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined">error</span>
            <p class="font-semibold text-sm">Fehler:</p>
        </div>
        <ul class="list-disc list-inside text-sm">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<!-- Users Table -->
<div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm overflow-hidden">
    <!-- Table Header -->
    <div class="px-6 py-4 border-b border-border-light dark:border-border-dark">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-text-muted-light dark:text-text-muted-dark">
                    <?= count($users) ?> Benutzer
                </span>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" placeholder="Suchen..." class="px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-background-light dark:bg-background-dark">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Benutzer
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        E-Mail
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Rolle
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Erstellt am
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Lizenzen
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-text-muted-light dark:text-text-muted-dark uppercase tracking-wider">
                        Aktionen
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                                        <?= strtoupper(substr($u['first_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-text-light dark:text-text-dark">
                                        <?= htmlspecialchars(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?>
                                    </div>
                                    <div class="text-xs text-text-muted-light dark:text-text-muted-dark">
                                        ID: <?= $u['id'] ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-light dark:text-text-dark">
                                <?= htmlspecialchars($u['email']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $u['role'] === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($u['is_active']): ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    <span class="material-symbols-outlined text-sm mr-1">check_circle</span>
                                    Aktiv
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                    <span class="material-symbols-outlined text-sm mr-1">cancel</span>
                                    Inaktiv
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted-light dark:text-text-muted-dark">
                            <?= date('d.m.Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                <?= $u['active_licenses'] ?? 0 ?> Lizenzen
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="/users/<?= $u['id'] ?>/licenses" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" title="Lizenzen verwalten">
                                    <span class="material-symbols-outlined">key</span>
                                </a>
                                <button @click="editUser(<?= htmlspecialchars(json_encode($u)) ?>)" class="text-primary hover:text-primary/80 transition-colors" title="Bearbeiten">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                <?php if ($u['is_active']): ?>
                                    <form method="POST" action="/users/<?= $u['id'] ?>/delete" class="inline" onsubmit="return confirm('Benutzer wirklich deaktivieren?')">
                                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Deaktivieren">
                                            <span class="material-symbols-outlined">person_off</span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="/users/<?= $u['id'] ?>/activate" class="inline">
                                        <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition-colors" title="Aktivieren">
                                            <span class="material-symbols-outlined">person_check</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create User Modal -->
<div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div @click="showCreateModal = false" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <div class="relative bg-card-light dark:bg-card-dark rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Neuer Benutzer</h3>
                <button @click="showCreateModal = false" class="text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="/users" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Vorname</label>
                    <input type="text" name="first_name" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Nachname</label>
                    <input type="text" name="last_name" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">E-Mail</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Passwort</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Rolle</label>
                    <select name="role" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="user">Benutzer</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" checked class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-text-light dark:text-text-dark">
                        Benutzer ist aktiv
                    </label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showCreateModal = false" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Abbrechen
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Erstellen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div @click="showEditModal = false" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <div class="relative bg-card-light dark:bg-card-dark rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">Benutzer bearbeiten</h3>
                <button @click="showEditModal = false" class="text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" :action="`/users/${editingUser.id}/update`" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Vorname</label>
                    <input type="text" name="first_name" x-model="editingUser.first_name" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Nachname</label>
                    <input type="text" name="last_name" x-model="editingUser.last_name" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">E-Mail</label>
                    <input type="email" name="email" x-model="editingUser.email" required class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Neues Passwort (optional)</label>
                    <input type="password" name="password" placeholder="Leer lassen für keine Änderung" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Rolle</label>
                    <select name="role" x-model="editingUser.role" class="w-full px-4 py-2 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="user">Benutzer</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="edit_is_active" x-model="editingUser.is_active" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="edit_is_active" class="ml-2 block text-sm text-text-light dark:text-text-dark">
                        Benutzer ist aktiv
                    </label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showEditModal = false" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Abbrechen
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Suchfunktion
    document.querySelector('input[placeholder="Suchen..."]')?.addEventListener('input', function(e) {
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
