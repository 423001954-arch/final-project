<?php

// app/Database/Migrations/2024-01-01-000001_CreateRolesTable.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateRolesTable
 *
 * Creates the 'roles' table which stores all role definitions.
 * The 'name' column is the slug used by Filter classes
 * (e.g. 'superadmin', 'manager', 'staff').
 *
 * Run with:  php spark migrate
 * Rollback:  php spark migrate:rollback
 */
class CreateRolesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // Slug used by Filter classes — must be lowercase, no spaces
            // e.g. 'superadmin', 'manager', 'staff'
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'unique'     => true,
            ],
            // Human-readable label shown in the UI
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            // Optional description of what this role can access
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        // $this->forge->addUniqueKey('name');
        $this->forge->createTable('roles');
    }

    public function down(): void
    {
        // Must drop FK constraint in users table before dropping roles
        // (handled in the AddRoleIdToUsers migration's down() method)
        $this->forge->dropTable('roles', true);
    }
}
