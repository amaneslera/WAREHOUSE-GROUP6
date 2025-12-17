<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchaseRequestItemsTable extends Migration
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
            'purchase_request_id' => [
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('purchase_request_id');
        $this->forge->addKey('inventory_item_id');

        $this->forge->addForeignKey('purchase_request_id', 'purchase_requests', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('inventory_item_id', 'inventory_items', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('purchase_request_items');
    }

    public function down()
    {
        $this->forge->dropTable('purchase_request_items');
    }
}
