<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penitip;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksiPenjualan;
use App\Models\DetailTransaksiPenitipan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateRatingPenitip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'penitip:update-rating';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update rating penitip berdasarkan rating produk yang telah terjual';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai update rating penitip...');
        
        try {
            // Ambil semua penitip
            $penitips = Penitip::all();
            $updatedCount = 0;

            foreach ($penitips as $penitip) {
                // Hitung rating rata-rata dari produk yang telah terjual oleh penitip ini
                $averageRating = $this->calculatePenitipRating($penitip->idPenitip);
                
                if ($averageRating !== null && $averageRating != $penitip->rating) {
                    // Update rating penitip
                    $penitip->update(['rating' => round($averageRating, 2)]);
                    
                    $this->line("Updated rating for {$penitip->nama}: {$penitip->rating} -> " . round($averageRating, 2));
                    $updatedCount++;
                }
            }

            $this->info("Update rating penitip selesai. Total diupdate: {$updatedCount} penitip");
            
            // Log untuk monitoring
            Log::info("Rating penitip updated successfully", [
                'total_penitip' => $penitips->count(),
                'updated_count' => $updatedCount,
                'timestamp' => now()
            ]);

        } catch (\Exception $e) {
            $this->error('Error saat update rating: ' . $e->getMessage());
            Log::error('Failed to update penitip rating', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Hitung rating rata-rata penitip berdasarkan rating produk yang terjual
     */
    private function calculatePenitipRating($idPenitip)
    {
        try {
            // Query untuk mendapatkan rata-rata rating produk yang terjual dari penitip ini
            $averageRating = DB::table('penitip as p')
                ->join('transaksi_penitipan as tp', 'p.idPenitip', '=', 'tp.idPenitip')
                ->join('detail_transaksi_penitipan as dtp', 'tp.idTransaksiPenitipan', '=', 'dtp.idTransaksiPenitipan')
                ->join('produk as pr', 'dtp.idProduk', '=', 'pr.idProduk')
                ->join('detail_transaksi_penjualan as dts', 'pr.idProduk', '=', 'dts.idProduk')
                ->join('transaksi_penjualan as ts', 'dts.idTransaksiPenjualan', '=', 'ts.idTransaksiPenjualan')
                ->where('p.idPenitip', $idPenitip)
                ->where('ts.status', 'selesai') // Hanya yang sudah selesai
                ->where('pr.ratingProduk', '>', 0.00) // Hanya yang sudah dirating
                ->avg('pr.ratingProduk');

            return $averageRating;

        } catch (\Exception $e) {
            Log::warning("Failed to calculate rating for penitip {$idPenitip}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Alternative method: Hitung berdasarkan rating yang diberikan pembeli
     * (Jika nanti ada sistem rating dari pembeli ke produk)
     */
    private function calculateFromBuyerRatings($idPenitip)
    {
        // Implementasi untuk rating dari pembeli
        // Saat ini belum ada tabel rating dari pembeli, tapi siap untuk dikembangkan
        
        return DB::table('rating_pembeli as rb') // Tabel yang belum ada
            ->join('produk as p', 'rb.idProduk', '=', 'p.idProduk')
            ->join('detail_transaksi_penitipan as dtp', 'p.idProduk', '=', 'dtp.idProduk')
            ->join('transaksi_penitipan as tp', 'dtp.idTransaksiPenitipan', '=', 'tp.idTransaksiPenitipan')
            ->where('tp.idPenitip', $idPenitip)
            ->avg('rb.rating');
    }
}