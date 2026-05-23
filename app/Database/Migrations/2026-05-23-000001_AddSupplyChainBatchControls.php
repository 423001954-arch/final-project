<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSupplyChainBatchControls extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('medicine_batches')) {
            return;
        }

        if (! $this->db->fieldExists('warehouse_location', 'medicine_batches')) {
            $this->forge->addColumn('medicine_batches', [
                'warehouse_location' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => false,
                    'default'    => 'Main Warehouse',
                    'after'      => 'supplier',
                ],
            ]);
        }

        if ($this->db->getPlatform() === 'MySQLi') {
            $this->db->query("
                ALTER TABLE medicine_batches
                MODIFY status ENUM('available', 'depleted', 'quarantined', 'recalled', 'expired')
                NOT NULL DEFAULT 'available'
            ");
        }

        if (! $this->indexExists('medicine_batches', 'medicine_batches_batch_number_unique')) {
            $this->db->query('ALTER TABLE medicine_batches ADD UNIQUE medicine_batches_batch_number_unique (batch_number)');
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('medicine_batches')) {
            return;
        }

        if ($this->indexExists('medicine_batches', 'medicine_batches_batch_number_unique')) {
            $this->db->query('ALTER TABLE medicine_batches DROP INDEX medicine_batches_batch_number_unique');
        }

        if ($this->db->getPlatform() === 'MySQLi') {
            $this->db->query("
                ALTER TABLE medicine_batches
                MODIFY status ENUM('available', 'depleted', 'quarantined', 'recalled')
                NOT NULL DEFAULT 'available'
            ");
        }

        if ($this->db->fieldExists('warehouse_location', 'medicine_batches')) {
            $this->forge->dropColumn('medicine_batches', 'warehouse_location');
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $row = $this->db->table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $this->db->database)
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->get()
            ->getRowArray();

        return $row !== null;
    }
}
