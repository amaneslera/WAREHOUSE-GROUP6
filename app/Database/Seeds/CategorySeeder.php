<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Check if categories already exist
        if ($this->db->table('categories')->countAll() > 0) {
            echo "Categories table already has data. Skipping seed.\n";
            return;
        }
        
        $data = [
            [
                'category_name' => 'Construction materials',
                'description' => 'Materials used for construction projects',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'category_name' => 'Tools',
                'description' => 'Hand tools and power tools',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'category_name' => 'Safety',
                'description' => 'Safety equipment and gear',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'category_name' => 'Electrical',
                'description' => 'Electrical components and supplies',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'category_name' => 'Equipment',
                'description' => 'Heavy equipment and machinery',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('categories')->insertBatch($data);
        echo "Successfully seeded " . count($data) . " categories.\n";
    }
}
