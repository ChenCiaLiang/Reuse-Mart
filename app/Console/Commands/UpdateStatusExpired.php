<?php

namespace App\Console\Commands;

use App\Models\DetailTransaksiPenjualan;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateStatusExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaksi:update-status-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'hangus';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sekarang = Carbon::now();
        $pengiriman = TransaksiPenjualan::where('status', 'pengambilan')
            ->where('tanggalBatasAmbil', '<', $sekarang)
            ->get();
        $this->info("Total transaksi pengambilan: " . $pengiriman->count());
        foreach ($pengiriman as $transaksi) {
            $this->info("ID Transaksi: {$transaksi->idTransaksiPenjualan}, Batas Pengambilan: {$transaksi->tanggalBatasAmbil}");
        }
        $detailPengiriman = DetailTransaksiPenjualan::whereIn('idTransaksiPenjualan', $pengiriman->pluck('idTransaksiPenjualan'))->get();
        $produk = Produk::whereIn('idProduk', $detailPengiriman->pluck('idProduk'))->get();

        foreach ($pengiriman as $item) {
            $item->update([
                'status' => 'hangus',
            ]);
        }

        foreach ($produk as $item) {
            $item->update([
                'status' => 'barang untuk donasi',
            ]);
        }
        $this->info('Status transaksi dan produk telah diperbarui.');
        return 0;
    }
}
