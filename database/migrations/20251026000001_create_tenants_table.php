<?php
/**
 * Migration: Create Tenants Table
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTenantsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('bm_tenants');
        
        $table
              ->addColumn('uuid', 'string', ['limit' => 36])
              ->addColumn('name', 'string', ['limit' => 255])
              ->addColumn('domain', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('logo_path', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('primary_color', 'string', ['limit' => 7, 'default' => '#3B82F6'])
              ->addColumn('product_name', 'string', ['limit' => 255, 'default' => 'Business Manager'])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['uuid'], ['unique' => true])
              ->addIndex(['domain'], ['unique' => true])
              ->create();
    }
}
