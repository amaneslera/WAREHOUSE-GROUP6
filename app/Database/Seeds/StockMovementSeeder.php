<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function run()
    {
        // Check if stock movements already exist
        if ($this->db->table('stock_movements')->countAll() > 0) {
            echo "Stock movements table already has data. Skipping seed.\n";
            return;
        }
        
        $data = [
            // Recent stock IN movements (pending approval)
            [
                'inventory_item_id' => 1, // Steel
                'movement_type' => 'in',
                'quantity' => 50,
                'from_warehouse_id' => null,
                'to_warehouse_id' => 1,
                'reference_number' => 'PO-2025-001',
                'notes' => 'New stock received from supplier',
                'performed_by' => 3, // Staff member
                'approval_status' => 'pending',
                'approved_by' => null,
                'rejected_by' => null,
                'approval_notes' => null,
                'rejection_reason' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ],
            [
                'inventory_item_id' => 2, // Cement
                'movement_type' => 'in',
                'quantity' => 75,
                'from_warehouse_id' => null,
                'to_warehouse_id' => 2,
                'reference_number' => 'PO-2025-002',
                'notes' => 'Cement delivery from vendor',
                'performed_by' => 4, // Staff member
                'approval_status' => 'pending',
                'approved_by' => null,
                'rejected_by' => null,
                'approval_notes' => null,
                'rejection_reason' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            ],
            [
                'inventory_item_id' => 5, // Electrical Wire
                'movement_type' => 'in',
                'quantity' => 150,
                'from_warehouse_id' => null,
                'to_warehouse_id' => 2,
                'reference_number' => 'PO-2025-003',
                'notes' => 'Emergency stock of electrical wire',
                'performed_by' => 5, // Staff member
                'approval_status' => 'pending',
                'approved_by' => null,
                'rejected_by' => null,
                'approval_notes' => null,
                'rejection_reason' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
            ],
            // Past movements (already approved)
            [
                'inventory_item_id' => 3, // Power Drill
                'movement_type' => 'out',
                'quantity' => 5,
                'from_warehouse_id' => 1,
                'to_warehouse_id' => null,
                'reference_number' => 'DO-2025-001',
                'notes' => 'Drills sent to Site A for project work',
                'performed_by' => 3,
                'approval_status' => 'approved',
                'approved_by' => 1, // Manager
                'rejected_by' => null,
                'approval_notes' => 'Approved - requested by project manager',
                'rejection_reason' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'inventory_item_id' => 4, // Safety Helmet
                'movement_type' => 'out',
                'quantity' => 20,
                'from_warehouse_id' => 3,
                'to_warehouse_id' => null,
                'reference_number' => 'DO-2025-002',
                'notes' => 'Safety helmets for new worker onboarding',
                'performed_by' => 6, // Staff member
                'approval_status' => 'approved',
                'approved_by' => 1,
                'rejected_by' => null,
                'approval_notes' => 'Approved - routine distribution',
                'rejection_reason' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'inventory_item_id' => 1, // Steel
                'movement_type' => 'transfer',
                'quantity' => 25,
                'from_warehouse_id' => 1,
                'to_warehouse_id' => 2,
                'reference_number' => 'TR-2025-001',
                'notes' => 'Transfer to balance stock levels',
                'performed_by' => 4,
                'approval_status' => 'approved',
                'approved_by' => 2, // Manager
                'rejected_by' => null,
                'approval_notes' => 'Approved - stock balancing',
                'rejection_reason' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            // Rejected movement
            [
                'inventory_item_id' => 2, // Cement
                'movement_type' => 'out',
                'quantity' => 100,
                'from_warehouse_id' => 2,
                'to_warehouse_id' => null,
                'reference_number' => 'DO-2025-003',
                'notes' => 'Large cement order for Site B',
                'performed_by' => 5,
                'approval_status' => 'rejected',
                'approved_by' => null,
                'rejected_by' => 1,
                'approval_notes' => null,
                'rejection_reason' => 'Stock insufficient - only 200 available but requesting 100. Insufficient safety stock.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            ],
        ];

        $this->db->table('stock_movements')->insertBatch($data);
        echo "Successfully seeded " . count($data) . " stock movements.\n";
    }
}
