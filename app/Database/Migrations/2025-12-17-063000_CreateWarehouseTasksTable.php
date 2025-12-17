<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWarehouseTasksTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('warehouse_tasks')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'po_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'assigned_staff_id' => [
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
                'constraint' => ['pending', 'in_progress', 'completed'],
                'default'    => 'pending',
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('assigned_staff_id');
        $this->forge->addKey('warehouse_id');
        $this->forge->addKey('status');

        $this->forge->addUniqueKey('po_id');

        $this->forge->addForeignKey('po_id', 'purchase_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_staff_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('warehouse_tasks');
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('warehouse_tasks')) {
            return;
        }

        $this->forge->dropTable('warehouse_tasks');
    }
}
