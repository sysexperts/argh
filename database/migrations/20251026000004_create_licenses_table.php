<?php
/**
 * Migration: Create Licenses Table
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateLicensesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('bm_licenses');
        
        $table->addColumn('tenant_id', 'integer')
              ->addColumn('module_id', 'integer')
              ->addColumn('user_count', 'integer', ['default' => 1])
              ->addColumn('valid_from', 'datetime')
              ->addColumn('valid_until', 'datetime', ['null' => true])
              ->addColumn('is_active', 'boolean', ['default' => true])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['tenant_id', 'module_id'], ['unique' => true])
              ->addForeignKey('tenant_id', 'bm_tenants', 'id', ['delete' => 'CASCADE'])
              ->addForeignKey('module_id', 'bm_modules', 'id', ['delete' => 'CASCADE'])
              ->create();
    }
}
