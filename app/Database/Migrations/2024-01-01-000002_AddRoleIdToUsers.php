<?php

// app/Database/Migrations/2024-01-01-000002_AddRoleIdToUsers.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddRoleIdToUsers
 *
 * Adds a role_id foreign key column to the existing 'users' table.
 * Defaults to NULL so any existing user rows are not broken.
 * The FK references roles(id) with ON DELETE SET NULL — if a role
 * is deleted, affected users are unassigned (role_id becomes NULL)
 * rather than being deleted themselves.
 *
 * Run with:  php spark migrate
 * Rollback:  php spark migrate:rollback
 */
class AddRoleIdToUsers extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        // Add the role_id column
        if (! $this->db->fieldExists('role_id', 'users')) {
            $this->forge->addColumn('users', [
                'role_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => true,       // NULL = no role assigned yet
                    'default'    => null,
                    'after'      => 'email',    // places column after 'email'
                ],
            ]);
        }

        // Add deleted_at for soft-delete support
        // NULL means the record is active; a timestamp means it's soft-deleted
        if (! $this->db->fieldExists('deleted_at', 'users')) {
            $this->forge->addColumn('users', [
                'deleted_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'updated_at',  // places column after 'updated_at'
                ],
            ]);
        }

        // Add the foreign key constraint separately
        // ON DELETE SET NULL: deleting a role unassigns users, doesn't delete them
        if ($this->db->tableExists('roles') && ! $this->foreignKeyExists('users', 'fk_users_role_id')) {
            $this->db->query('
                ALTER TABLE users
                ADD CONSTRAINT fk_users_role_id
                FOREIGN KEY (role_id)
                REFERENCES roles(id)
                ON DELETE SET NULL
                ON UPDATE CASCADE
            ');
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        // Must drop FK constraint before dropping the column
        if ($this->foreignKeyExists('users', 'fk_users_role_id')) {
            $this->db->query('ALTER TABLE users DROP FOREIGN KEY fk_users_role_id');
        }

        if ($this->db->fieldExists('role_id', 'users')) {
            $this->forge->dropColumn('users', 'role_id');
        }

        // Drop the soft-delete column
        if ($this->db->fieldExists('deleted_at', 'users')) {
            $this->forge->dropColumn('users', 'deleted_at');
        }
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $row = $this->db->table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $this->db->database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->get()
            ->getRowArray();

        return $row !== null;
    }
}
