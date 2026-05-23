<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMedicineBatchesTable extends Migration
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
            'medicine_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'batch_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => false,
            ],
            'supplier' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
            ],
            'warehouse_location' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => false,
                'default'    => 'Main Warehouse',
            ],
            'received_quantity' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'available_quantity' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'unit_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'manufactured_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'expiry_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'received_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'depleted', 'quarantined', 'recalled', 'expired'],
                'default'    => 'available',
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

        $this->forge->addKey('id', true);
        $this->forge->addKey(['medicine_id', 'expiry_date']);
        $this->forge->addKey('batch_number', false, true);
        $this->forge->addKey(['medicine_id', 'batch_number'], false, true);
        $this->forge->addForeignKey('medicine_id', 'medicines', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('medicine_batches');
    }

    public function down(): void
    {
        $this->forge->dropTable('medicine_batches', true);
    }
}
