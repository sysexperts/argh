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
    <button onclick="openCreateModal()" 
            class="bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary/90 transition flex items-center gap-2">
        <span class="material-symbols-outlined">add</span>
        Neuer Benutzer
    </button>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-text-light dark:text-text-dark mb-6">Benutzer bearbeiten</h3>
        <form id="editUserForm" onsubmit="updateUser(event)">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Vorname</label>
                    <input type="text" name="first_name" id="edit_first_name" required
                           class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Nachname</label>
                    <input type="text" name="last_name" id="edit_last_name" required
                           class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">E-Mail</label>
                    <input type="email" name="email" id="edit_email" required
                           class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Rolle</label>
                    <select name="role" id="edit_role" required
                            class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                        <option value="user">Benutzer</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                               class="w-4 h-4 text-primary border-border-light dark:border-border-dark rounded">
                        <span class="text-sm font-medium text-text-light dark:text-text-dark">Aktiv</span>
                    </label>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-primary/90 transition">
                    Speichern
                </button>
                <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                    Abbrechen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Create User Modal -->
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-text-light dark:text-text-dark mb-6">Neuer Benutzer</h3>
        <form id="createUserForm" onsubmit="createUser(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Vorname</label>
                    <input type="text" name="first_name" required
                           class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Nachname</label>
                    <input type="text" name="last_name" required
                           class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">E-Mail</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Passwort</label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Rolle</label>
                    <select name="role" required
                            class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
                        <option value="user">Benutzer</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-primary/90 transition">
                    Erstellen
                </button>
                <button type="button" onclick="closeCreateModal()" class="flex-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                    Abbrechen
                </button>
            </div>
        </form>
    </div>
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
                            <button onclick='editUser(<?= json_encode($u) ?>)' 
                                    class="p-2 text-primary hover:bg-primary/10 rounded-lg transition"
                                    title="Bearbeiten">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                            <button onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>')" 
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                                    title="Löschen">
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

// Modal functions
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.getElementById('createUserForm').reset();
}

function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editUserForm').reset();
}

// Edit user
function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_first_name').value = user.first_name || '';
    document.getElementById('edit_last_name').value = user.last_name || '';
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_is_active').checked = user.is_active == 1;
    openEditModal();
}

// Create user
async function createUser(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/users', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Benutzer erfolgreich erstellt!');
            location.reload();
        } else {
            alert('Fehler: ' + result.message);
        }
    } catch (error) {
        alert('Fehler beim Erstellen des Benutzers');
        console.error(error);
    }
}

// Update user
async function updateUser(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    const userId = data.user_id;
    
    // Checkbox-Wert korrekt setzen
    data.is_active = document.getElementById('edit_is_active').checked;
    delete data.user_id;
    
    try {
        const response = await fetch(`/api/users/${userId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Benutzer erfolgreich aktualisiert!');
            location.reload();
        } else {
            alert('Fehler: ' + result.message);
        }
    } catch (error) {
        alert('Fehler beim Aktualisieren des Benutzers');
        console.error(error);
    }
}

// Delete user
async function deleteUser(userId, userName) {
    if (!confirm(`Möchten Sie den Benutzer "${userName}" wirklich löschen?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/users/${userId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Benutzer erfolgreich gelöscht!');
            location.reload();
        } else {
            alert('Fehler: ' + result.message);
        }
    } catch (error) {
        alert('Fehler beim Löschen des Benutzers');
        console.error(error);
    }
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Benutzerverwaltung';
$title = 'Benutzerverwaltung - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
