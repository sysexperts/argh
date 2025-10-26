<?php
/**
 * Migration: Create Audit Logs Table (GoBD-konform)
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAuditLogsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('bm_audit_logs');
        
        $table->addColumn('tenant_id', 'integer')
              ->addColumn('user_id', 'integer', ['null' => true])
              ->addColumn('module_code', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('action', 'string', ['limit' => 100])
              ->addColumn('entity_type', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('entity_id', 'integer', ['null' => true])
              ->addColumn('old_values', 'text', ['null' => true, 'comment' => 'JSON'])
              ->addColumn('new_values', 'text', ['null' => true, 'comment' => 'JSON'])
              ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
              ->addColumn('user_agent', 'text', ['null' => true])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['tenant_id'])
              ->addIndex(['user_id'])
              ->addIndex(['module_code'])
              ->addIndex(['entity_type', 'entity_id'])
              ->addForeignKey('tenant_id', 'bm_tenants', 'id', ['delete' => 'CASCADE'])
              ->addForeignKey('user_id', 'bm_users', 'id', ['delete' => 'SET_NULL'])
              ->create();
    }
}
