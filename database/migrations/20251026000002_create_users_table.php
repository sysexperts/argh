<?php
/**
 * Migration: Create Users Table
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('bm_users');
        
        $table->addColumn('tenant_id', 'integer')
              ->addColumn('email', 'string', ['limit' => 255])
              ->addColumn('password_hash', 'string', ['limit' => 255])
              ->addColumn('first_name', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('last_name', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('role', 'string', ['limit' => 50, 'default' => 'user'])
              ->addColumn('is_active', 'boolean', ['default' => true])
              ->addColumn('email_verified_at', 'datetime', ['null' => true])
              ->addColumn('last_login_at', 'datetime', ['null' => true])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['email'], ['unique' => true])
              ->addIndex(['tenant_id'])
              ->addForeignKey('tenant_id', 'bm_tenants', 'id', ['delete' => 'CASCADE'])
              ->create();
    }
}
