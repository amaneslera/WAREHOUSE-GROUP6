<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApprovalColumnsToStockMovements extends Migration
{
    public function up()
    {
        $fields = [
            'approval_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'pending',
                'null'       => false,
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'rejected_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'approval_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];

        $this->forge->addColumn('stock_movements', $fields);

        // Add foreign keys for approval tracking
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('rejected_by', 'users', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropColumn('stock_movements', [
            'approval_status',
            'approved_by',
            'rejected_by',
            'approval_notes',
            'rejection_reason',
        ]);
    }
}
