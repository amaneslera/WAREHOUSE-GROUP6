<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'warehouse_name' => 'Building A',
                'location' => 'North Wing',
                'capacity' => 1000,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'warehouse_name' => 'Building B', 
                'location' => 'South Wing',
                'capacity' => 800,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'warehouse_name' => 'Building C',
                'location' => 'East Wing', 
                'capacity' => 600,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('warehouses')->insertBatch($data);
    }
}
