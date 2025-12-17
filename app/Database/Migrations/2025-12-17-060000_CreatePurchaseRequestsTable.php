<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchaseRequestsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'pr_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
            'requested_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'submitted', 'approved', 'rejected'],
                'default'    => 'draft',
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('requested_by');
        $this->forge->addKey('warehouse_id');

        $this->forge->addForeignKey('requested_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('purchase_requests');
    }

    public function down()
    {
        $this->forge->dropTable('purchase_requests');
    }
}
