<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penitip;
use App\Models\TopSeller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateTopSeller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:top-seller {--month=} {--year=} {--cleanup-only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate top seller for previous month, give bonus, and cleanup expired records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Pertama, cleanup record yang sudah expired
            $this->cleanupExpiredRecords();

            // Jika hanya cleanup, keluar setelah cleanup
            if ($this->option('cleanup-only')) {
                return Command::SUCCESS;
            }

            // Ambil bulan dan tahun, default ke bulan lalu
            $month = $this->option('month') ?? Carbon::now()->subMonth()->month;
            $year = $this->option('year') ?? Carbon::now()->subMonth()->year;
            
            $this->info("🔍 Calculating top seller for {$month}/{$year}");
            
            // Tanggal mulai dan akhir bulan yang dihitung
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            
            // Periode top seller (bulan berikutnya)
            $topSellerStart = $endDate->copy()->addDay()->startOfMonth();
            $topSellerEnd = $topSellerStart->copy()->endOfMonth();
            
            $this->info("📅 Sales Period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
            $this->info("🏆 Top Seller Period: {$topSellerStart->format('Y-m-d')} to {$topSellerEnd->format('Y-m-d')}");
            
            // Hitung total penjualan per penitip untuk bulan yang ditentukan
            $topSellerData = $this->calculateMonthlySales($startDate, $endDate);
            
            if (empty($topSellerData)) {
                $this->info('❌ No sales data found for the specified period.');
                return Command::SUCCESS;
            }

            // Display sales summary
            $this->displaySalesSummary($topSellerData);
            
            // Ambil penitip dengan penjualan tertinggi
            $topPenitip = $topSellerData[0]; // Data sudah diurutkan desc
            
            $this->info("🏆 Top Seller: {$topPenitip['nama']} (ID: {$topPenitip['idPenitip']})");
            $this->info("💰 Total Sales: Rp " . number_format($topPenitip['total_penjualan'], 0, ',', '.'));
            
            // Cek apakah ada top seller yang masih aktif
            $existingTopSeller = TopSeller::active()->first();
            
            // Variables untuk menyimpan hasil transaction
            $bonusAmount = 0;
            $actionTaken = '';
            $penitipName = '';
            
            // Simpan ke database menggunakan transaction
            DB::transaction(function () use ($topPenitip, $topSellerStart, $topSellerEnd, $existingTopSeller, &$bonusAmount, &$actionTaken, &$penitipName) {
                $bonusAmount = $topPenitip['total_penjualan'] * 0.01;

                if ($existingTopSeller) {
                    if ($existingTopSeller->idPenitip == $topPenitip['idPenitip']) {
                        // Penitip yang sama, update periode saja
                        $existingTopSeller->update([
                            'tanggal_mulai' => $topSellerStart,
                            'tanggal_selesai' => $topSellerEnd
                        ]);
                        $actionTaken = 'updated_existing';
                    } else {
                        // Penitip berbeda, hapus yang lama dan buat yang baru
                        $existingTopSeller->delete();
                        
                        TopSeller::create([
                            'tanggal_mulai' => $topSellerStart,
                            'tanggal_selesai' => $topSellerEnd,
                            'idPenitip' => $topPenitip['idPenitip']
                        ]);
                        $actionTaken = 'replaced';
                    }
                } else {
                    // Tidak ada top seller aktif, buat baru
                    TopSeller::create([
                        'tanggal_mulai' => $topSellerStart,
                        'tanggal_selesai' => $topSellerEnd,
                        'idPenitip' => $topPenitip['idPenitip']
                    ]);
                    $actionTaken = 'created_new';
                }
                
                // Update saldo penitip
                $penitip = Penitip::find($topPenitip['idPenitip']);
                if (!$penitip) {
                    throw new \Exception("Penitip not found: " . $topPenitip['idPenitip']);
                }
                
                $penitip->increment('saldo', $bonusAmount);
                $penitip->increment('bonus', $bonusAmount);
                $penitipName = $penitip->nama;
            });
            
            // Tampilkan hasil setelah transaction berhasil
            switch ($actionTaken) {
                case 'updated_existing':
                    $this->info("🔄 Updated existing top seller period");
                    break;
                case 'replaced':
                    $this->info("🔄 Replaced old top seller with new one");
                    break;
                case 'created_new':
                    $this->info("✨ Created new top seller record");
                    break;
            }

            $this->info("💸 Bonus given to {$penitipName}: Rp " . number_format($bonusAmount, 0, ',', '.'));
            
            // Log hasil
            Log::info('Top Seller calculated successfully', [
                'period' => $topSellerStart->format('Y-m'),
                'sales_period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
                'penitip_id' => $topPenitip['idPenitip'],
                'penitip_name' => $topPenitip['nama'],
                'total_sales' => $topPenitip['total_penjualan'],
                'bonus_amount' => $bonusAmount,
                'action' => $actionTaken
            ]);
            
            $this->info('✅ Top seller calculation completed successfully!');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error calculating top seller: ' . $e->getMessage());
            
            Log::error('Top Seller calculation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }

    /**
     * Cleanup expired top seller records
     */
    private function cleanupExpiredRecords()
    {
        try {
            $expiredCount = TopSeller::where('tanggal_selesai', '<', Carbon::now())->count();
            
            if ($expiredCount > 0) {
                TopSeller::where('tanggal_selesai', '<', Carbon::now())->delete();
                $this->info("🧹 Cleaned up {$expiredCount} expired top seller record(s)");
                
                Log::info('Expired top seller records cleaned up', [
                    'count' => $expiredCount
                ]);
            } else {
                $this->info("✨ No expired top seller records to cleanup");
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ Error during cleanup: " . $e->getMessage());
        }
    }
    
    /**
     * Calculate monthly sales per penitip
     */
    private function calculateMonthlySales($startDate, $endDate)
    {
        $this->info("🔍 Executing query with dates: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        
        try {
            $salesData = DB::select("
                SELECT 
                    p.idPenitip,
                    p.nama,
                    SUM(pr.hargaJual) as total_penjualan,
                    COUNT(DISTINCT dtp.idDetailTransaksiPenjualan) as total_transaksi
                FROM penitip p
                INNER JOIN transaksi_penitipan tp ON p.idPenitip = tp.idPenitip
                INNER JOIN detail_transaksi_penitipan dpen ON tp.idTransaksiPenitipan = dpen.idTransaksiPenitipan
                INNER JOIN produk pr ON dpen.idProduk = pr.idProduk
                INNER JOIN detail_transaksi_penjualan dtp ON pr.idProduk = dtp.idProduk
                INNER JOIN transaksi_penjualan tpj ON dtp.idTransaksiPenjualan = tpj.idTransaksiPenjualan
                WHERE tpj.status IN ('terjual', 'kirim', 'diambil')
                    AND tpj.tanggalLaku >= ?
                    AND tpj.tanggalLaku <= ?
                GROUP BY p.idPenitip, p.nama
                HAVING total_penjualan > 0
                ORDER BY total_penjualan DESC
            ", [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')]);
            
            $this->info("📊 Query returned " . count($salesData) . " records");
            
            if (empty($salesData)) {
                $this->warn("⚠️ No sales data found for the period");
                return [];
            }
            
            return collect($salesData)->map(function ($item) {
                return [
                    'idPenitip' => (int) $item->idPenitip,
                    'nama' => $item->nama,
                    'total_penjualan' => (float) $item->total_penjualan,
                    'total_transaksi' => (int) $item->total_transaksi
                ];
            })->toArray();
            
        } catch (\Exception $e) {
            $this->error("❌ Database query error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Display sales summary for debugging
     */
    private function displaySalesSummary($salesData)
    {
        $this->info("\n📊 Sales Summary (Top 10):");
        
        if (empty($salesData)) {
            $this->info("No data to display");
            return;
        }
        
        $tableData = collect($salesData)->take(10)->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'id' => $item['idPenitip'],
                'nama' => $item['nama'],
                'total_sales' => 'Rp ' . number_format($item['total_penjualan'], 0, ',', '.'),
                'transactions' => $item['total_transaksi']
            ];
        })->toArray();
        
        $this->table(
            ['Rank', 'Penitip ID', 'Nama', 'Total Penjualan', 'Total Transaksi'],
            $tableData
        );
        
        $totalSales = collect($salesData)->sum('total_penjualan');
        $totalTransactions = collect($salesData)->sum('total_transaksi');
        
        $this->info("💰 Grand Total Sales: Rp " . number_format($totalSales, 0, ',', '.'));
        $this->info("📦 Total Transactions: " . $totalTransactions);
    }
}