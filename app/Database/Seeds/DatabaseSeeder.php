<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Run seeders in the correct order to satisfy foreign key constraints
        
        // 1. First seed users (no dependencies)
        $this->call('UsersSeeder');
        
        // 2. Seed categories (no dependencies)
        $this->call('CategorySeeder');
        
        // 3. Seed warehouses (no dependencies)
        $this->call('WarehouseSeeder');
        
        // 4. Seed inventory (depends on categories and warehouses)
        $this->call('InventorySeeder');
        
        echo "Database seeding completed successfully!\n";
    }
}
