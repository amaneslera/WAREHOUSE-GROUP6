<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMatchingFieldsToAccountsPayableTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('accounts_payable', [
            'po_reference' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'Purchase Order reference number',
                'after'      => 'invoice_number',
            ],
            'delivery_receipt' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'Delivery receipt number',
                'after'      => 'po_reference',
            ],
            'stock_movement_ids' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Comma-separated stock movement IDs for matching',
                'after'      => 'warehouse_id',
            ],
            'matching_status' => [
                'type'       => 'ENUM',
                'constraint' => ['unmatched', 'matched', 'discrepancy'],
                'default'    => 'unmatched',
                'comment'    => 'Status of invoice matching with documents',
                'after'      => 'stock_movement_ids',
            ],
            'discrepancy_notes' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Notes about discrepancies found during matching',
                'after'      => 'matching_status',
            ],
            'matched_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID who performed the matching',
                'after'      => 'discrepancy_notes',
            ],
            'matched_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Timestamp when matching was completed',
                'after'      => 'matched_by',
            ],
        ]);

        // Add foreign key for matched_by
        $this->forge->addForeignKey('matched_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Add indexes
        $this->forge->addKey('po_reference');
        $this->forge->addKey('delivery_receipt');
        $this->forge->addKey('matching_status');
    }

    public function down()
    {
        $this->forge->dropForeignKey('accounts_payable', 'accounts_payable_matched_by_foreign');
        $this->forge->dropColumn('accounts_payable', [
            'po_reference',
            'delivery_receipt',
            'stock_movement_ids',
            'matching_status',
            'discrepancy_notes',
            'matched_by',
            'matched_at',
        ]);
    }
}
