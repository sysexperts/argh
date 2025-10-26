<?php
/**
 * Migration: Create Modules Table
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateModulesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('bm_modules');
        
        $table->addColumn('code', 'string', ['limit' => 50])
              ->addColumn('name', 'string', ['limit' => 100])
              ->addColumn('description', 'text', ['null' => true])
              ->addColumn('version', 'string', ['limit' => 20])
              ->addColumn('is_core', 'boolean', ['default' => false])
              ->addColumn('price_per_user', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 1.00])
              ->addColumn('icon', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['code'], ['unique' => true])
              ->create();
    }
}
