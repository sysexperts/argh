<!-- Neuer Mandant -->

<div class="mb-6">
    <a href="/partner-console" class="text-sm text-primary hover:underline mb-2 inline-block">← Zurück zur Übersicht</a>
    <h2 class="text-3xl font-bold text-text-light dark:text-text-dark">Neuer Mandant</h2>
</div>

<div class="max-w-3xl">
    <form method="POST" action="/partner-console/tenants" class="bg-card-light dark:bg-card-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark p-6">
        
        <!-- Firmen-Informationen -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4">Firmen-Informationen</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                        Tenant-Name * <span class="text-xs text-text-muted-light dark:text-text-muted-dark">(Kurzname, z.B. "demo")</span>
                    </label>
                    <input type="text" name="tenant_name" required 
                           class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                        Firmenname *
                    </label>
                    <input type="text" name="company_name" required 
                           class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>
        </div>
        
        <!-- Kontakt -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4">Kontakt</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                        E-Mail *
                    </label>
                    <input type="email" name="contact_email" required 
                           class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                        Telefon
                    </label>
                    <input type="text" name="contact_phone" 
                           class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>
        </div>
        
        <!-- Deployment -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4">Deployment</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                        Domain *
                    </label>
                    <input type="text" name="domain" required placeholder="kunde.sys-experts.de"
                           class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                        Server Host
                    </label>
                    <input type="text" name="server_host" placeholder="server1.sys-experts.de"
                           class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                        Server IP
                    </label>
                    <input type="text" name="server_ip" placeholder="192.168.1.100"
                           class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                        Status
                    </label>
                    <select name="tenant_status" 
                            class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="trial">Trial</option>
                        <option value="active">Aktiv</option>
                        <option value="suspended">Gesperrt</option>
                        <option value="cancelled">Gekündigt</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                    Trial bis (optional)
                </label>
                <input type="date" name="trial_until" 
                       class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
        </div>
        
        <!-- Buttons -->
        <div class="flex gap-4">
            <button type="submit" class="flex items-center gap-2 bg-gradient-to-r from-primary to-teal-600 text-white px-6 py-2 rounded-lg hover:shadow-lg transition-all">
                <span class="material-symbols-outlined">save</span>
                Mandant erstellen
            </button>
            <a href="/partner-console" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Abbrechen
            </a>
        </div>
    </form>
</div>
