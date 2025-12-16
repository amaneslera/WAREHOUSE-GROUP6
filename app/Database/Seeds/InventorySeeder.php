<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run()
    {
        // Check if inventory items already exist
        if ($this->db->table('inventory_items')->countAll() > 0) {
            echo "Inventory items table already has data. Skipping seed.\n";
            return;
        }
        
        $data = [
            [
                'item_id'       => 'INV001',
                'item_name'     => 'Steel',
                'category_id'   => 1, // Construction materials
                'warehouse_id'  => 1, // Building A
                'current_stock' => 150,
                'minimum_stock' => 50,
                'unit_price'    => 450.00,
                'description'   => 'High-quality construction steel for building projects. Meets industry standards and specifications.',
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'item_id'       => 'INV002',
                'item_name'     => 'Cement',
                'category_id'   => 1, // Construction materials
                'warehouse_id'  => 2, // Building B
                'current_stock' => 200,
                'minimum_stock' => 75,
                'unit_price'    => 25.50,
                'description'   => 'Portland cement for construction work.',
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'item_id'       => 'INV003',
                'item_name'     => 'Power Drill',
                'category_id'   => 2, // Tools
                'warehouse_id'  => 1, // Building A
                'current_stock' => 15,
                'minimum_stock' => 20,
                'unit_price'    => 125.00,
                'description'   => 'Cordless power drill with multiple speed settings.',
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'item_id'       => 'INV004',
                'item_name'     => 'Safety Helmet',
                'category_id'   => 3, // Safety
                'warehouse_id'  => 3, // Building C
                'current_stock' => 45,
                'minimum_stock' => 30,
                'unit_price'    => 35.75,
                'description'   => 'OSHA compliant safety helmets for construction workers.',
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'item_id'       => 'INV005',
                'item_name'     => 'Electrical Wire',
                'category_id'   => 4, // Electrical
                'warehouse_id'  => 2, // Building B
                'current_stock' => 0,
                'minimum_stock' => 100,
                'unit_price'    => 2.75,
                'description'   => '12 AWG electrical wire for building wiring.',
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'item_id'       => 'INV006',
                'item_name'     => 'Forklift',
                'category_id'   => 5, // Equipment
                'warehouse_id'  => 1, // Building A
                'current_stock' => 3,
                'minimum_stock' => 2,
                'unit_price'    => 25000.00,
                'description'   => 'Electric forklift for warehouse operations.',
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('inventory_items')->insertBatch($data);
        echo "Successfully seeded " . count($data) . " inventory items.\n";
    }
}
