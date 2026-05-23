<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileImageToUsers extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('profile_image', 'users')) {
            $this->forge->addColumn('users', [
                'profile_image' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'password',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('profile_image', 'users')) {
            $this->forge->dropColumn('users', 'profile_image');
        }
    }
}
