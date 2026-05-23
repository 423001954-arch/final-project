<?php

namespace App\Commands;

use App\Models\MedicineBatchModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FlagExpiredStock extends BaseCommand
{
    protected $group       = 'Supply Chain';
    protected $name        = 'supply:flag-expired';
    protected $description = 'Flags expired active medicine batches so FEFO cannot use them.';

    public function run(array $params): void
    {
        $batchModel = new MedicineBatchModel();
        $expired = $batchModel->expiredActiveBatches(500);

        if ($expired === []) {
            CLI::write('No active expired stock found.', 'green');
            return;
        }

        $updated = $batchModel->flagExpiredActiveBatches();

        CLI::write($updated . ' batch(es) flagged as expired and removed from active FEFO stock.', 'yellow');

        foreach ($expired as $batch) {
            CLI::write(sprintf(
                '- %s | %s | Batch %s | Expired %s | Qty %s',
                $batch['facility_name'] ?? 'Unknown facility',
                $batch['generic_name'] ?? 'Unknown medicine',
                $batch['batch_number'],
                $batch['expiry_date'],
                $batch['available_quantity']
            ));
        }
    }
}
