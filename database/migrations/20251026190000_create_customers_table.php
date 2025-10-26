<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCustomersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('bm_customers');
        $table->addColumn('tenant_id', 'integer', ['null' => true])
              ->addColumn('customer_number', 'string', ['limit' => 50])
              ->addColumn('company_name', 'string', ['limit' => 255])
              ->addColumn('contact_person', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('email', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('phone', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('website', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('tax_id', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('vat_id', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('street', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('zip', 'string', ['limit' => 20, 'null' => true])
              ->addColumn('city', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('country', 'string', ['limit' => 100, 'default' => 'Deutschland'])
              ->addColumn('notes', 'text', ['null' => true])
              ->addColumn('is_active', 'boolean', ['default' => 1])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['tenant_id'])
              ->addIndex(['customer_number'], ['unique' => true])
              ->addIndex(['email'])
              ->create();
    }
}
