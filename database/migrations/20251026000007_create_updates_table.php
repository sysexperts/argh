<?php
/**
 * Migration: Create Updates Table
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUpdatesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('bm_updates');
        
        $table->addColumn('tenant_id', 'integer')
              ->addColumn('module_code', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('from_version', 'string', ['limit' => 20, 'null' => true])
              ->addColumn('to_version', 'string', ['limit' => 20])
              ->addColumn('update_type', 'string', ['limit' => 20, 'comment' => 'feature, security, bugfix'])
              ->addColumn('status', 'string', ['limit' => 20, 'default' => 'pending', 'comment' => 'pending, completed, failed'])
              ->addColumn('executed_at', 'datetime', ['null' => true])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['tenant_id'])
              ->addIndex(['module_code'])
              ->addForeignKey('tenant_id', 'bm_tenants', 'id', ['delete' => 'CASCADE'])
              ->create();
    }
}
