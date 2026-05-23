<?php

// app/Models/RoleModel.php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RoleModel
 *
 * Manages the 'roles' table.
 * The 'name' column stores a slug (e.g. 'superadmin', 'manager', 'staff')
 * which is what Filters compare against session data.
 */
class RoleModel extends Model
{
    protected $table      = 'roles';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // No soft deletes on roles — a hard delete keeps things simple
    protected $useSoftDeletes = false;

    protected $allowedFields = ['name', 'label', 'description'];

    protected $returnType = 'array';

    // ── Validation ────────────────────────────────────────────
    protected $validationRules = [
        'name'  => 'required|alpha_dash|min_length[2]|max_length[50]|is_unique[roles.name]',
        'label' => 'required|min_length[2]|max_length[100]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Role slug is required.',
            'alpha_dash' => 'Role slug may only contain letters, numbers, hyphens, and underscores.',
            'is_unique'  => 'That role slug already exists.',
        ],
        'label' => [
            'required' => 'A display label is required.',
        ],
    ];

    // ── Custom helpers ────────────────────────────────────────

    /**
     * Return all roles as a simple id => label map.
     * Useful for populating <select> dropdowns.
     */
    public function getDropdown(): array
    {
        $rows = $this->findAll();
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['id']] = $row['label'];
        }
        return $map;
    }

    /**
     * Find a role by its slug name.
     */
    public function findByName(string $name): ?array
    {
        return $this->where('name', $name)->first();
    }
}
