<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateInvoicesTable extends AbstractMigration
{
    public function change(): void
    {
        // Rechnungen Tabelle
        $table = $this->table('bm_invoices');
        $table->addColumn('tenant_id', 'integer', ['null' => true])
              ->addColumn('user_id', 'integer', ['null' => true])
              ->addColumn('invoice_number', 'string', ['limit' => 50])
              ->addColumn('customer_name', 'string', ['limit' => 255])
              ->addColumn('customer_email', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('customer_address', 'text', ['null' => true])
              ->addColumn('invoice_date', 'date')
              ->addColumn('due_date', 'date')
              ->addColumn('status', 'string', ['limit' => 20, 'default' => 'draft']) // draft, sent, paid, overdue, cancelled
              ->addColumn('subtotal', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
              ->addColumn('tax_rate', 'decimal', ['precision' => 5, 'scale' => 2, 'default' => 19])
              ->addColumn('tax_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
              ->addColumn('total', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
              ->addColumn('notes', 'text', ['null' => true])
              ->addColumn('paid_at', 'datetime', ['null' => true])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['tenant_id'])
              ->addIndex(['user_id'])
              ->addIndex(['invoice_number'], ['unique' => true])
              ->addIndex(['status'])
              ->create();

        // Rechnungspositionen Tabelle
        $itemsTable = $this->table('bm_invoice_items');
        $itemsTable->addColumn('invoice_id', 'integer')
                   ->addColumn('description', 'string', ['limit' => 255])
                   ->addColumn('quantity', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 1])
                   ->addColumn('unit_price', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
                   ->addColumn('total', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
                   ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                   ->addIndex(['invoice_id'])
                   ->create();
    }
}
