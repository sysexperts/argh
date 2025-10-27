<?php
/**
 * Migration: Create User Sessions Table
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserSessionsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('user_sessions', ['id' => false, 'primary_key' => 'session_id']);
        
        $table->addColumn('session_id', 'string', ['limit' => 64])
              ->addColumn('user_id', 'integer')
              ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
              ->addColumn('user_agent', 'text', ['null' => true])
              ->addColumn('last_activity', 'datetime')
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['user_id'])
              ->addIndex(['last_activity'])
              ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
              ->create();
    }
}
