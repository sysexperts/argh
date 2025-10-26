<?php
/**
 * Lizenz-Konfiguration
 */

return [
    'api_url' => $_ENV['LICENSE_API_URL'] ?? 'https://partner.sys-experts.de/api/v1',
    'api_key' => $_ENV['LICENSE_API_KEY'] ?? '',
    'check_interval' => (int)($_ENV['LICENSE_CHECK_INTERVAL'] ?? 86400), // 24 Stunden
    
    'tenant_id' => $_ENV['TENANT_ID'] ?? '',
    'tenant_name' => $_ENV['TENANT_NAME'] ?? 'Meine Firma GmbH',
    
    // Grace Period bei Lizenzablauf (in Tagen)
    'grace_period' => 7,
    
    // Cache-Dauer für Lizenzprüfung (in Sekunden)
    'cache_duration' => 3600, // 1 Stunde
];
