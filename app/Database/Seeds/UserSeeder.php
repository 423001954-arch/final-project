<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $getRoleId = function (string $slug): ?int {
            $row = $this->db->table('roles')->where('name', $slug)->get()->getRowArray();

            return $row ? (int) $row['id'] : null;
        };

        $hash = password_hash('Password1', PASSWORD_BCRYPT);

        $users = [
            [
                'name'       => 'Supply Chain SuperAdmin',
                'email'      => 'superadmin@healthchain.local',
                'password'   => $hash,
                'role_id'    => $getRoleId('superadmin'),
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'name'       => 'Warehouse Manager',
                'email'      => 'manager@healthchain.local',
                'password'   => $hash,
                'role_id'    => $getRoleId('manager'),
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'name'       => 'Clinic Staff',
                'email'      => 'staff@healthchain.local',
                'password'   => $hash,
                'role_id'    => $getRoleId('staff'),
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ];

        foreach ($users as $user) {
            $existing = $this->db->table('users')
                ->where('email', $user['email'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $update = $user;
                unset($update['created_at']);
                $this->db->table('users')->where('id', $existing['id'])->update($update);
                continue;
            }

            $this->db->table('users')->insert($user);
        }
    }
}
