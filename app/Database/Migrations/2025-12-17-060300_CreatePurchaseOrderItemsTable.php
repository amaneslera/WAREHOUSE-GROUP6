<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchaseOrderItemsTable extends Migration
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
            'purchase_order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'inventory_item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
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
        $this->forge->addKey('purchase_order_id');
        $this->forge->addKey('inventory_item_id');

        $this->forge->addForeignKey('purchase_order_id', 'purchase_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('inventory_item_id', 'inventory_items', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('purchase_order_items');
    }

    public function down()
    {
        $this->forge->dropTable('purchase_order_items');
    }
}
