<?php

namespace App\Console\Commands;

use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;
use App\Models\Produk;
use App\Models\Pembeli;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupExpiredTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:cleanup-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel expired payment transactions and restore product status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired transactions...');
        
        // Find expired transactions that are still waiting for payment
        $expiredTransactions = TransaksiPenjualan::where('status', 'menunggu_pembayaran')
            ->where('tanggalBatasLunas', '<', Carbon::now())
            ->get();
            
        if ($expiredTransactions->isEmpty()) {
            $this->info('No expired transactions found.');
            return 0;
        }
        
        $this->info("Found {$expiredTransactions->count()} expired transactions to cancel...");
        
        $cancelledCount = 0;
        $restoredProducts = [];
        $returnedPoints = 0;
        
        foreach ($expiredTransactions as $transaksi) {
            DB::beginTransaction();
            try {
                $this->info("Processing transaction ID: {$transaksi->idTransaksiPenjualan}");
                
                // 1. Restore product status
                $detailTransaksi = DetailTransaksiPenjualan::where('idTransaksiPenjualan', $transaksi->idTransaksiPenjualan)->get();
                
                foreach ($detailTransaksi as $detail) {
                    $produk = Produk::find($detail->idProduk);
                    if ($produk && $produk->status !== 'Tersedia') {
                        $produk->update(['status' => 'Tersedia']);
                        $restoredProducts[] = $detail->idProduk;
                        $this->line("  - Restored product {$detail->idProduk} to 'Tersedia'");
                    }
                }
                
                // 2. Return points if any were used (approximate calculation based on common patterns)
                // Since we don't store used points in DB, we'll skip this part in the command
                // but the real-time cancellation in the controller will handle it properly
                
                // 3. Update transaction status
                $transaksi->update(['status' => 'batal']);
                
                DB::commit();
                $cancelledCount++;
                
                $this->line("  ✓ Transaction {$transaksi->idTransaksiPenjualan} cancelled successfully");
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("  ✗ Failed to cancel transaction {$transaksi->idTransaksiPenjualan}: {$e->getMessage()}");
                \Log::error('Failed to cancel expired transaction', [
                    'transaction_id' => $transaksi->idTransaksiPenjualan,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("\nCleanup completed:");
        $this->info("- Cancelled transactions: {$cancelledCount}");
        $this->info("- Restored products: " . count($restoredProducts));
        
        if (!empty($restoredProducts)) {
            $this->info("- Restored product IDs: " . implode(', ', $restoredProducts));
        }
        
        \Log::info('Expired transactions cleanup completed', [
            'cancelled_count' => $cancelledCount,
            'restored_products' => count($restoredProducts),
            'product_ids' => $restoredProducts
        ]);
        
        return 0;
    }
}