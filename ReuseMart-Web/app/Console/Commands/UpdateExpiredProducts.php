<?php
// app/Console/Commands/UpdateExpiredProducts.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransaksiPenitipan;
use App\Models\Produk;
use App\Models\DetailTransaksiPenitipan;
use App\Models\Penitip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateExpiredProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:expired-products {--dry-run : Show what would be updated without actually updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of products that are not picked up after 7 days past penitipan expiry to "barang untuk donasi"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $isDryRun = $this->option('dry-run');
            
            if ($isDryRun) {
                $this->info("🔍 DRY RUN MODE - No changes will be made");
            }
            
            $this->info("🔍 Checking for expired products...");
            $currentTime = Carbon::now();
            $this->info("⏰ Current time: {$currentTime->format('Y-m-d H:i:s')}");
            
            // Ambil transaksi penitipan yang sudah melewati batas ambil
            $expiredTransactions = $this->getExpiredTransactions();
            
            if ($expiredTransactions->isEmpty()) {
                $this->info("✨ No expired products found");
                return Command::SUCCESS;
            }
            
            $this->info("📦 Found {$expiredTransactions->count()} expired transaction(s)");
            
            // Display details
            $this->displayExpiredTransactions($expiredTransactions);
            
            if (!$isDryRun) {
                // Konfirmasi sebelum update
                if ($this->confirm('Do you want to proceed with updating these products?', true)) {
                    $updatedCount = $this->updateExpiredProducts($expiredTransactions);
                    $this->info("✅ Successfully updated {$updatedCount} product(s) to 'Didonasikan'");
                } else {
                    $this->info("❌ Operation cancelled by user");
                    return Command::SUCCESS;
                }
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error updating expired products: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            Log::error('UpdateExpiredProducts failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Get expired transactions that passed batasAmbil date
     */
    private function getExpiredTransactions()
    {
        $now = Carbon::now();
        
        $this->info("🔍 Looking for transactions expired before: {$now->format('Y-m-d H:i:s')}");
        
        $transactions = TransaksiPenitipan::with(['penitip', 'detailTransaksiPenitipan.produk'])
            ->where('statusPenitipan', 'Aktif') // Hanya yang masih aktif
            ->whereNull('tanggalPengambilan') // Belum diambil
            ->whereNotNull('batasAmbil') // Pastikan ada batas ambil
            ->whereHas('detailTransaksiPenitipan.produk', function ($query) {
                $query->whereIn('status', ['Tersedia']); // Hanya yang masih tersedia
            })
            ->get()
            ->filter(function ($transaction) use ($now) {
                // Filter secara manual untuk handle Carbon comparison dengan lebih aman
                try {
                    if (!$transaction->batasAmbil) {
                        return false;
                    }
                    
                    $batasAmbil = $transaction->batasAmbil instanceof Carbon 
                        ? $transaction->batasAmbil 
                        : Carbon::parse($transaction->batasAmbil);
                    
                    return $batasAmbil->isBefore($now);
                } catch (\Exception $e) {
                    $this->warn("⚠️ Invalid date format for transaction {$transaction->idTransaksiPenitipan}");
                    return false;
                }
            });
            
        $this->info("🔍 Found {$transactions->count()} transactions matching criteria");
        
        return $transactions;
    }
    
    /**
     * Display expired transactions details
     */
    private function displayExpiredTransactions($expiredTransactions)
    {
        $this->info("\n📋 Expired Transactions Details:");
        
        $tableData = [];
        $totalProductsFound = 0;
        
        foreach ($expiredTransactions as $transaction) {
            try {
                // Safe Carbon conversion
                $batasAmbil = $transaction->batasAmbil instanceof Carbon 
                    ? $transaction->batasAmbil 
                    : Carbon::parse($transaction->batasAmbil);
                
                foreach ($transaction->detailTransaksiPenitipan as $detail) {
                    $produk = $detail->produk;
                    
                    // Skip jika produk sudah jadi donasi atau tidak ada
                    if (!$produk || $produk->status !== 'Tersedia') {
                        continue;
                    }
                    
                    $daysExpired = $batasAmbil->diffInDays(Carbon::now());
                    
                    $tableData[] = [
                        'id_transaksi' => $transaction->idTransaksiPenitipan,
                        'penitip' => $transaction->penitip->nama ?? 'Unknown',
                        'produk' => $produk->deskripsi,
                        'harga' => 'Rp ' . number_format($produk->hargaJual, 0, ',', '.'),
                        'expired_date' => $batasAmbil->format('Y-m-d H:i'),
                        'days_expired' => $daysExpired,
                        'current_status' => $produk->status
                    ];
                    
                    $totalProductsFound++;
                }
            } catch (\Exception $e) {
                $this->warn("⚠️ Error processing transaction {$transaction->idTransaksiPenitipan}: " . $e->getMessage());
            }
        }
        
        if (!empty($tableData)) {
            $this->table(
                ['ID Transaksi', 'Penitip', 'Produk', 'Harga', 'Expired Date', 'Days Expired', 'Current Status'],
                $tableData
            );
            
            $this->info("📊 Total products to be updated: {$totalProductsFound}");
        } else {
            $this->info("✨ No products need to be updated to donation status");
        }
    }
    
    /**
     * Update expired products to donation status
     */
    private function updateExpiredProducts($expiredTransactions)
    {
        $updatedCount = 0;
        $errors = [];
        
        DB::transaction(function () use ($expiredTransactions, &$updatedCount, &$errors) {
            foreach ($expiredTransactions as $transaction) {
                try {
                    $batasAmbil = $transaction->batasAmbil instanceof Carbon 
                        ? $transaction->batasAmbil 
                        : Carbon::parse($transaction->batasAmbil);
                    
                    foreach ($transaction->detailTransaksiPenitipan as $detail) {
                        $produk = $detail->produk;
                        
                        // Skip jika produk tidak ada atau sudah didonasikan
                        if (!$produk || $produk->status !== 'Tersedia') {
                            continue;
                        }
                        
                        // Update status produk
                        $produk->update(['status' => 'Didonasikan']);
                        
                        $updatedCount++;
                        
                        $this->info("📦 Updated: {$produk->deskripsi} -> Didonasikan");
                        
                        // Log individual product update
                        Log::info('Product status updated to donation', [
                            'produk_id' => $produk->idProduk,
                            'produk_name' => $produk->deskripsi,
                            'transaksi_id' => $transaction->idTransaksiPenitipan,
                            'penitip_id' => $transaction->idPenitip,
                            'penitip_name' => $transaction->penitip->nama ?? 'Unknown',
                            'expired_date' => $batasAmbil->format('Y-m-d H:i:s'),
                            'days_expired' => $batasAmbil->diffInDays(Carbon::now())
                        ]);
                    }
                    
                    // Update status transaksi penitipan menjadi expired
                    $transaction->update([
                        'statusPenitipan' => 'Expired'
                    ]);
                    
                    $this->info("🔄 Transaction {$transaction->idTransaksiPenitipan} status -> Expired");
                    
                } catch (\Exception $e) {
                    $error = "Error processing transaction {$transaction->idTransaksiPenitipan}: " . $e->getMessage();
                    $errors[] = $error;
                    $this->error("❌ " . $error);
                    
                    // Don't throw, just continue with next transaction
                    // throw $e; // Uncomment if you want to rollback on any error
                }
            }
        });
        
        // Report errors if any
        if (!empty($errors)) {
            $this->warn("⚠️ Some transactions had errors:");
            foreach ($errors as $error) {
                $this->warn("  - " . $error);
            }
        }
        
        // Log summary
        Log::info('Expired products update completed', [
            'total_updated' => $updatedCount,
            'total_transactions' => $expiredTransactions->count(),
            'errors' => $errors
        ]);
        
        return $updatedCount;
    }
}