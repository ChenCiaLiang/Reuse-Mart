<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TransaksiPenitipan;
use App\Events\PenitipanH3Notification;
use App\Events\PenitipanHariHNotification;
use Carbon\Carbon;

class CheckPenitipanExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Cek H-3 masa penitipan berakhir
        $h3_date = Carbon::now()->addDays(3)->format('Y-m-d');

        $h3_penitipan = TransaksiPenitipan::with([
            'penitip',
            'detailTransaksiPenitipan.produk'
        ])
            ->whereDate('tanggalAkhirPenitipan', $h3_date)
            ->where('statusPenitipan', 'Aktif')
            ->get();

        // Group by penitip untuk notification
        $grouped_h3 = $h3_penitipan->groupBy('idPenitip');

        foreach ($grouped_h3 as $idPenitip => $transaksi_group) {
            $produk_names = [];
            foreach ($transaksi_group as $transaksi) {
                foreach ($transaksi->detailTransaksiPenitipan as $detail) {
                    $produk_names[] = $detail->produk->deskripsi;
                }
            }

            if (!empty($produk_names)) {
                broadcast(new PenitipanH3Notification($idPenitip, $produk_names));
            }
        }

        // Cek Hari H masa penitipan berakhir  
        $hariH_date = Carbon::now()->format('Y-m-d');

        $hariH_penitipan = TransaksiPenitipan::with([
            'penitip',
            'detailTransaksiPenitipan.produk'
        ])
            ->whereDate('tanggalAkhirPenitipan', $hariH_date)
            ->where('statusPenitipan', 'Aktif')
            ->get();

        // Group by penitip untuk notification
        $grouped_hariH = $hariH_penitipan->groupBy('idPenitip');

        foreach ($grouped_hariH as $idPenitip => $transaksi_group) {
            $produk_names = [];
            foreach ($transaksi_group as $transaksi) {
                foreach ($transaksi->detailTransaksiPenitipan as $detail) {
                    $produk_names[] = $detail->produk->deskripsi;
                }
            }

            if (!empty($produk_names)) {
                broadcast(new PenitipanHariHNotification($idPenitip, $produk_names));
            }
        }
    }
}
