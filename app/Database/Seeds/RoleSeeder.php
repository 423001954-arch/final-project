<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $roles = [
            [
                'name'        => 'superadmin',
                'label'       => 'SuperAdmin',
                'description' => 'Full access to healthcare supply-chain administration, users, roles, stock, batches, and API operations.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'manager',
                'label'       => 'Manager',
                'description' => 'Operational access to facilities, medicines, batches, allocations, expiry alerts, and stock queries.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'staff',
                'label'       => 'Staff',
                'description' => 'Front-line access for profile and request workflows.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        foreach ($roles as $role) {
            $existing = $this->db->table('roles')
                ->where('name', $role['name'])
                ->get()
                ->getRowArray();

            if ($existing) {
                unset($role['created_at']);
                $this->db->table('roles')->where('id', $existing['id'])->update($role);
                continue;
            }

            $this->db->table('roles')->insert($role);
        }
    }
}
