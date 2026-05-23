<?php

// app/Database/Seeds/MainSeeder.php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MainSeeder
 *
 * Master seeder that calls all other seeders in dependency order.
 * RoleSeeder must run before UserSeeder because UserSeeder looks up
 * role IDs by slug name.
 *
 * ── HOW TO RUN ────────────────────────────────────────────────
 *
 * Step 1 — Run migrations first (creates the tables):
 *   php spark migrate
 *
 * Step 2 — Seed all data in one command:
 *   php spark db:seed MainSeeder
 *
 * Or seed individually (must be in this order):
 *   php spark db:seed RoleSeeder
 *   php spark db:seed UserSeeder
 *
 * ── ROLLBACK ──────────────────────────────────────────────────
 *   php spark migrate:rollback
 *
 * ── FRESH START (drops all tables and re-migrates) ────────────
 *   php spark migrate:fresh --seed
 *   (CI4 will call MainSeeder automatically if set in Database.php)
 *
 * ── SETTING DEFAULT SEEDER IN Database.php ───────────────────
 *   Open app/Config/Database.php and set:
 *     public string $defaultGroup = 'default';
 *   Then in the default group array, add:
 *     'DBPrefix'   => '',
 *   This allows `php spark migrate:fresh --seed` to auto-run MainSeeder.
 */
class MainSeeder extends Seeder
{
    public function run(): void
    {
        // Order matters — roles must exist before users reference them
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(SupplyChainSeeder::class);
    }
}
