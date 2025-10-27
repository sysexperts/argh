<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= $title ?? 'Business Manager' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#009688",
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                        "card-light": "#ffffff",
                        "card-dark": "#1e293b",
                        "text-light": "#334155",
                        "text-dark": "#cbd5e1",
                        "text-muted-light": "#64748b",
                        "text-muted-dark": "#94a3b8",
                        "border-light": "#e2e8f0",
                        "border-dark": "#334155",
                    },
                    fontFamily: {
                        display: ["Poppins", "sans-serif"],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark h-full" x-data="{ 
    sidebarOpen: window.innerWidth >= 1024, 
    showCreateModal: false,
    showEditModal: false,
    showAddItemModal: false,
    showEditCustomerModal: false,
    editingUser: {},
    editingCustomer: {},
    editUser(user) {
        this.editingUser = {...user, is_active: user.is_active == 1};
        this.showEditModal = true;
    },
    editCustomer(customer) {
        this.editingCustomer = {...customer};
        this.showEditCustomerModal = true;
    }
}">
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }" class="fixed inset-y-0 left-0 z-30 w-64 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark transform transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 lg:flex-shrink-0">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-between h-20 px-6 border-b border-border-light dark:border-border-dark">
                    <a class="flex items-center gap-2" href="/dashboard">
                        <img src="/assets/images/sys-expertslogo.png" alt="sys-experts Logo" class="h-8 w-auto" onerror="this.style.display='none'"/>
                    </a>
                    <button @click="sidebarOpen = false" class="lg:hidden text-text-muted-light dark:text-text-muted-dark">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-2">
                    <?php foreach ($navigation ?? [] as $group): ?>
                        <?php foreach ($group['items'] ?? [] as $item): ?>
                            <a href="<?= htmlspecialchars($item['url']) ?>" 
                               class="flex items-center px-4 py-2.5 rounded text-sm font-medium transition-colors <?= ($item['active'] ?? false) ? 'text-white bg-primary shadow-sm' : 'text-text-muted-light dark:text-text-muted-dark hover:bg-background-light dark:hover:bg-background-dark hover:text-primary' ?>">
                                <?php if (isset($item['icon'])): ?>
                                    <span class="mr-3 text-xl"><?= $item['icon'] ?></span>
                                <?php endif; ?>
                                <span><?= htmlspecialchars($item['label']) ?></span>
                                <?php if (isset($item['badge'])): ?>
                                    <span class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-primary bg-opacity-20">
                                        <?= htmlspecialchars($item['badge']) ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </nav>

                <!-- User Menu -->
                <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                <?= htmlspecialchars($user['name'] ?? 'User') ?>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                <?= htmlspecialchars($user['email'] ?? '') ?>
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <a href="/settings" class="block px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            ⚙️ Einstellungen
                        </a>
                        <?php if (isset($user['email']) && $user['email'] === 'admin@sys-experts.de'): ?>
                        <a href="/partner-console" class="block px-3 py-2 text-sm text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg font-semibold">
                            🔧 Partner Console
                        </a>
                        <?php endif; ?>
                        <a href="/auth/logout" class="block px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                            🚪 Abmelden
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="flex items-center justify-between h-20 px-4 sm:px-6 bg-card-light dark:bg-card-dark border-b border-border-light dark:border-border-dark">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-text-muted-light dark:text-text-muted-dark">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div class="hidden lg:block">
                    <h1 class="text-2xl font-semibold text-text-light dark:text-text-dark">
                        Willkommen, <?= htmlspecialchars($user['name'] ?? 'User') ?>!
                    </h1>
                    <p class="text-sm text-text-muted-light dark:text-text-muted-dark">
                        Hier ist Ihre Business-Übersicht für heute.
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    
                    <div class="flex items-center space-x-4">
                        <!-- Dark Mode Toggle -->
                        <button id="darkModeToggle" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg id="sunIcon" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <svg id="moonIcon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </button>

                        <!-- Notifications -->
                        <div x-data="notificationWidget()" x-init="init()" class="relative">
                            <button @click="toggleDropdown()" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 relative">
                                <span class="material-symbols-outlined">notifications</span>
                                <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full min-w-[1.25rem]"></span>
                            </button>
                            
                            <!-- Dropdown -->
                            <div x-show="showDropdown" @click.away="showDropdown = false" x-transition class="absolute right-0 mt-2 w-96 bg-card-light dark:bg-card-dark rounded-xl shadow-2xl border border-border-light dark:border-border-dark z-50">
                                <div class="p-4 border-b border-border-light dark:border-border-dark flex items-center justify-between">
                                    <h3 class="font-bold text-text-light dark:text-text-dark">Benachrichtigungen</h3>
                                    <button @click="markAllAsRead()" class="text-xs text-primary hover:underline">Alle als gelesen</button>
                                </div>
                                
                                <div class="max-h-96 overflow-y-auto">
                                    <template x-if="notifications.length === 0">
                                        <div class="p-8 text-center text-text-muted-light dark:text-text-muted-dark">
                                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">notifications_off</span>
                                            <p>Keine Benachrichtigungen</p>
                                        </div>
                                    </template>
                                    
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <div @click="markAsRead(notification.id); if(notification.link) window.location.href = notification.link;" 
                                             :class="notification.is_read ? 'bg-transparent' : 'bg-primary/5'"
                                             class="p-4 border-b border-border-light dark:border-border-dark hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors">
                                            <div class="flex items-start gap-3">
                                                <span class="material-symbols-outlined text-primary" x-text="notification.icon || 'notifications'"></span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-semibold text-sm text-text-light dark:text-text-dark" x-text="notification.title"></p>
                                                    <p class="text-xs text-text-muted-light dark:text-text-muted-dark mt-1" x-text="notification.message"></p>
                                                    <p class="text-xs text-text-muted-light dark:text-text-muted-dark mt-1" x-text="formatDate(notification.created_at)"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Notification Widget
        function notificationWidget() {
            return {
                notifications: [],
                unreadCount: 0,
                showDropdown: false,
                
                init() {
                    this.loadNotifications();
                    // Aktualisiere alle 30 Sekunden
                    setInterval(() => this.loadNotifications(), 30000);
                },
                
                async loadNotifications() {
                    try {
                        const response = await fetch('/api/notifications');
                        const data = await response.json();
                        this.notifications = data.notifications || [];
                        this.unreadCount = data.unread_count || 0;
                    } catch (error) {
                        console.error('Failed to load notifications:', error);
                    }
                },
                
                toggleDropdown() {
                    this.showDropdown = !this.showDropdown;
                },
                
                async markAsRead(id) {
                    try {
                        await fetch(`/api/notifications/${id}/read`, { method: 'POST' });
                        const notification = this.notifications.find(n => n.id === id);
                        if (notification) {
                            notification.is_read = 1;
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        }
                    } catch (error) {
                        console.error('Failed to mark as read:', error);
                    }
                },
                
                async markAllAsRead() {
                    try {
                        await fetch('/api/notifications/read-all', { method: 'POST' });
                        this.notifications.forEach(n => n.is_read = 1);
                        this.unreadCount = 0;
                    } catch (error) {
                        console.error('Failed to mark all as read:', error);
                    }
                },
                
                formatDate(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const diff = Math.floor((now - date) / 1000);
                    
                    if (diff < 60) return 'Gerade eben';
                    if (diff < 3600) return `vor ${Math.floor(diff / 60)} Min.`;
                    if (diff < 86400) return `vor ${Math.floor(diff / 3600)} Std.`;
                    if (diff < 604800) return `vor ${Math.floor(diff / 86400)} Tagen`;
                    
                    return date.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
                }
            };
        }
        
        // Dark Mode Toggle
        document.addEventListener('DOMContentLoaded', () => {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const html = document.documentElement;
            
            // Check for saved theme preference or default to light mode
            const currentTheme = localStorage.getItem('theme') || 'light';
            if (currentTheme === 'dark') {
                html.classList.add('dark');
            }

            darkModeToggle?.addEventListener('click', () => {
                html.classList.toggle('dark');
                const theme = html.classList.contains('dark') ? 'dark' : 'light';
                localStorage.setItem('theme', theme);
            });
        });
    </script>
</body>
</html>
