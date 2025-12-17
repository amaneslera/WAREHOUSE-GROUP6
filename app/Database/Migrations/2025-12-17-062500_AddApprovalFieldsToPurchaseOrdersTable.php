<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApprovalFieldsToPurchaseOrdersTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('purchase_orders')) {
            return;
        }

        $columns = [];

        if (! $db->fieldExists('po_approval_status', 'purchase_orders')) {
            $columns['po_approval_status'] = [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'pending',
                'after'      => 'status',
            ];
        }

        if (! $db->fieldExists('po_approved_by', 'purchase_orders')) {
            $columns['po_approved_by'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'po_approval_status',
            ];
        }

        if (! $db->fieldExists('po_approved_at', 'purchase_orders')) {
            $columns['po_approved_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'po_approved_by',
            ];
        }

        if (! $db->fieldExists('po_approval_notes', 'purchase_orders')) {
            $columns['po_approval_notes'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'po_approved_at',
            ];
        }

        if ($columns !== []) {
            $this->forge->addColumn('purchase_orders', $columns);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('purchase_orders')) {
            return;
        }

        if ($db->fieldExists('po_approved_by', 'purchase_orders')) {
            // Name may vary by DB driver; avoid hard failing
            try {
                $this->forge->dropForeignKey('purchase_orders', 'purchase_orders_po_approved_by_foreign');
            } catch (\Throwable $e) {
            }
        }

        $drop = [];
        foreach (['po_approval_status', 'po_approved_by', 'po_approved_at', 'po_approval_notes'] as $col) {
            if ($db->fieldExists($col, 'purchase_orders')) {
                $drop[] = $col;
            }
        }

        if ($drop !== []) {
            $this->forge->dropColumn('purchase_orders', $drop);
        }
    }
}
