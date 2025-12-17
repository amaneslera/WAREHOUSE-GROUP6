<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCreatedByAndUniqueVendorNameToVendorsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('vendors')) {
            return;
        }

        if (! $db->fieldExists('created_by', 'vendors')) {
            $this->forge->addColumn('vendors', [
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'status',
                ],
            ]);
        }

        try {
            $db->query('ALTER TABLE `vendors` ADD UNIQUE KEY `vendors_vendor_name_unique` (`vendor_name`)');
        } catch (\Throwable $e) {
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('vendors')) {
            return;
        }

        if ($db->fieldExists('created_by', 'vendors')) {
            try {
                $this->forge->dropColumn('vendors', 'created_by');
            } catch (\Throwable $e) {
            }
        }

        try {
            $db->query('ALTER TABLE `vendors` DROP INDEX `vendors_vendor_name_unique`');
        } catch (\Throwable $e) {
        }
    }
}
