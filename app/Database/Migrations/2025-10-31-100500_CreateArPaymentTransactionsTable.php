<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Create AR Payment Transactions Table
 * 
 * Stores individual payment receipts for accounts receivable
 */
class CreateArPaymentTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ar_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'payment_date' => [
                'type' => 'DATE',
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'reference_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'processed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('ar_id');
        $this->forge->addForeignKey('ar_id', 'accounts_receivable', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('processed_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ar_payment_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('ar_payment_transactions');
    }
}
