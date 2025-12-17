<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserStatusAndLoginColumnsToUsers extends Migration
{
    public function up()
    {
        $fields = [];

        if (! $this->db->fieldExists('is_active', 'users')) {
            $fields['is_active'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ];
        }

        if (! $this->db->fieldExists('last_login_at', 'users')) {
            $fields['last_login_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('last_login_at', 'users')) {
            $this->forge->dropColumn('users', 'last_login_at');
        }

        if ($this->db->fieldExists('is_active', 'users')) {
            $this->forge->dropColumn('users', 'is_active');
        }
    }
}
