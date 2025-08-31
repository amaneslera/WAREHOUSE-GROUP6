<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'last_name'   => 'Manager',
                'first_name'  => 'Warehouse',
                'middle_name' => '',
                'email'       => 'manager@example.com',
                'password'    => password_hash('manager123', PASSWORD_DEFAULT),
                'role'        => 'warehouse_manager',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'last_name'   => 'Staff',
                'first_name'  => 'Warehouse',
                'middle_name' => '',
                'email'       => 'staff@example.com',
                'password'    => password_hash('staff123', PASSWORD_DEFAULT),
                'role'        => 'warehouse_staff',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'last_name'   => 'Auditor',
                'first_name'  => 'Inventory',
                'middle_name' => '',
                'email'       => 'auditor@example.com',
                'password'    => password_hash('auditor123', PASSWORD_DEFAULT),
                'role'        => 'inventory_auditor',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'last_name'   => 'Officer',
                'first_name'  => 'Procurement',
                'middle_name' => '',
                'email'       => 'procurement@example.com',
                'password'    => password_hash('procure123', PASSWORD_DEFAULT),
                'role'        => 'procurement_officer',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'last_name'   => 'Clerk',
                'first_name'  => 'Accounts Payable',
                'middle_name' => '',
                'email'       => 'apclerk@example.com',
                'password'    => password_hash('apclerk123', PASSWORD_DEFAULT),
                'role'        => 'accounts_payable_clerk',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'last_name'   => 'Clerk',
                'first_name'  => 'Accounts Receivable',
                'middle_name' => '',
                'email'       => 'arclerk@example.com',
                'password'    => password_hash('arclerk123', PASSWORD_DEFAULT),
                'role'        => 'accounts_receivable_clerk',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'last_name'   => 'Administrator',
                'first_name'  => 'IT',
                'middle_name' => '',
                'email'       => 'itadmin@example.com',
                'password'    => password_hash('itadmin123', PASSWORD_DEFAULT),
                'role'        => 'it_administrator',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'last_name'   => 'Management',
                'first_name'  => 'Top',
                'middle_name' => '',
                'email'       => 'topmanagement@example.com',
                'password'    => password_hash('topmanage123', PASSWORD_DEFAULT),
                'role'        => 'top_management',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}