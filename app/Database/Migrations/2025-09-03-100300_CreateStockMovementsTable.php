<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockMovementsTable extends Migration
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
            'inventory_item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'movement_type' => [
                'type'       => 'ENUM',
                'constraint' => ['in', 'out', 'transfer', 'adjustment'],
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'from_warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'to_warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
            'performed_by' => [
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('inventory_item_id', 'inventory_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('from_warehouse_id', 'warehouses', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('to_warehouse_id', 'warehouses', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('performed_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_movements');
    }

    public function down()
    {
        $this->forge->dropTable('stock_movements');
    }
}
