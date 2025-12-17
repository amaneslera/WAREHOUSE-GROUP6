<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchaseOrdersTable extends Migration
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
            'po_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
            'purchase_request_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'vendor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'partial', 'complete', 'cancelled'],
                'default'    => 'pending',
            ],
            'expected_delivery_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addKey('vendor_id');
        $this->forge->addKey('warehouse_id');
        $this->forge->addKey('created_by');
        $this->forge->addUniqueKey('purchase_request_id');

        $this->forge->addForeignKey('purchase_request_id', 'purchase_requests', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('vendor_id', 'vendors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('purchase_orders');
    }

    public function down()
    {
        $this->forge->dropTable('purchase_orders');
    }
}
