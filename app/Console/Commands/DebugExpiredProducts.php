<?php
// app/Console/Commands/DebugExpiredProducts.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransaksiPenitipan;
use Carbon\Carbon;

class DebugExpiredProducts extends Command
{
    protected $signature = 'debug:expired-products';
    protected $description = 'Debug expired products data';

    public function handle()
    {
        $this->info("🔍 Debugging expired products data...");
        
        // Check recent transactions
        $transactions = TransaksiPenitipan::with(['penitip'])
            ->where('statusPenitipan', 'Aktif')
            ->limit(5)
            ->get();
            
        foreach ($transactions as $transaction) {
            $this->info("Transaction ID: {$transaction->idTransaksiPenitipan}");
            $this->info("batasAmbil type: " . gettype($transaction->batasAmbil));
            $this->info("batasAmbil value: " . var_export($transaction->batasAmbil, true));
            
            try {
                if ($transaction->batasAmbil instanceof Carbon) {
                    $this->info("batasAmbil formatted: " . $transaction->batasAmbil->format('Y-m-d H:i:s'));
                } else {
                    $parsed = Carbon::parse($transaction->batasAmbil);
                    $this->info("batasAmbil parsed: " . $parsed->format('Y-m-d H:i:s'));
                }
            } catch (\Exception $e) {
                $this->error("Error parsing date: " . $e->getMessage());
            }
            
            $this->info("---");
        }
    }
}