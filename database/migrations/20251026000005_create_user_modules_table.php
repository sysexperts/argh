<?php
/**
 * Migration: Create User Modules Table
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserModulesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('bm_user_modules');
        
        $table->addColumn('user_id', 'integer')
              ->addColumn('module_id', 'integer')
              ->addColumn('permissions', 'text', ['null' => true, 'comment' => 'JSON: {"read": true, "write": true, "admin": false}'])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['user_id', 'module_id'], ['unique' => true])
              ->addForeignKey('user_id', 'bm_users', 'id', ['delete' => 'CASCADE'])
              ->addForeignKey('module_id', 'bm_modules', 'id', ['delete' => 'CASCADE'])
              ->create();
    }
}
