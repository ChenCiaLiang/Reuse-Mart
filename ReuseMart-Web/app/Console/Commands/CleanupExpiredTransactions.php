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
     * Execute the console command - UPDATED untuk hard delete
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired transactions...');
        
        // Find expired transactions yang masih menunggu pembayaran
        $expiredTransactions = TransaksiPenjualan::where('status', 'menunggu_pembayaran')
            ->where('tanggalBatasLunas', '<', Carbon::now())
            ->get();
            
        if ($expiredTransactions->isEmpty()) {
            $this->info('No expired transactions found.');
            return 0;
        }
        
        $this->info("Found {$expiredTransactions->count()} expired transactions to clean up...");
        
        $cleanedCount = 0;
        $restoredProducts = [];
        
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
                
                // 2. Refund points
                if ($transaksi->poinDigunakan > 0) {
                    $pembeli = $transaksi->pembeli;
                    $pembeli->update(['poin' => $pembeli->poin + $transaksi->poinDigunakan]);
                    $this->line("  - Refunded {$transaksi->poinDigunakan} points to customer {$pembeli->idPembeli}");
                }
                
                // 3. HARD DELETE expired transactions
                DetailTransaksiPenjualan::where('idTransaksiPenjualan', $transaksi->idTransaksiPenjualan)->delete();
                $transaksi->delete();
                
                DB::commit();
                $cleanedCount++;
                
                $this->line("  ✓ Transaction {$transaksi->idTransaksiPenjualan} deleted successfully");
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("  ✗ Failed to delete transaction {$transaksi->idTransaksiPenjualan}: {$e->getMessage()}");
                \Log::error('Failed to delete expired transaction', [
                    'transaction_id' => $transaksi->idTransaksiPenjualan,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("\nCleanup completed:");
        $this->info("- Deleted transactions: {$cleanedCount}");
        $this->info("- Restored products: " . count($restoredProducts));
        
        if (!empty($restoredProducts)) {
            $this->info("- Restored product IDs: " . implode(', ', array_unique($restoredProducts)));
        }
        
        \Log::info('Expired transactions cleanup completed', [
            'deleted_count' => $cleanedCount,
            'restored_products' => count(array_unique($restoredProducts)),
            'product_ids' => array_unique($restoredProducts)
        ]);
        
        return 0;
    }
}