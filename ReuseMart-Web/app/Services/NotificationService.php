<?php

namespace App\Services;

use App\Models\Pembeli;
use App\Models\Penitip;
use App\Models\Pegawai;
use App\Notifications\ReuseMartNotification;

class NotificationService
{
    /**
     * Kirim notifikasi masa penitipan hampir habis
     */
    public function sendPenitipanExpireNotification($penitip, $produk, $daysLeft)
    {
        $title = $daysLeft === 0 ?
            "Masa Penitipan Berakhir Hari Ini!" :
            "Masa Penitipan Berakhir dalam {$daysLeft} Hari";

        $message = "Produk '{$produk->deskripsi}' akan berakhir masa penitipannya. Segera ambil atau perpanjang!";

        $penitip->notify(new ReuseMartNotification(
            $title,
            $message,
            'warning',
            [
                'type' => 'penitipan_expire',
                'produk_id' => $produk->idProduk,
                'days_left' => $daysLeft
            ]
        ));
    }

    /**
     * Kirim notifikasi barang laku
     */
    public function sendBarangLakuNotification($penitip, $produk, $harga)
    {
        $title = "Barang Anda Terjual! 🎉";
        $message = "Produk '{$produk->deskripsi}' telah terjual dengan harga Rp " . number_format($harga, 0, ',', '.');

        $penitip->notify(new ReuseMartNotification(
            $title,
            $message,
            'success',
            [
                'type' => 'barang_laku',
                'produk_id' => $produk->idProduk,
                'harga' => $harga
            ]
        ));
    }

    /**
     * Kirim notifikasi jadwal pengiriman
     */
    public function sendJadwalPengirimanNotification($users, $transaksi, $jadwal)
    {
        $title = "Jadwal Pengiriman Ditetapkan";
        $message = "Pengiriman dijadwalkan pada " . $jadwal->format('d/m/Y H:i');

        foreach ($users as $user) {
            $user->notify(new ReuseMartNotification(
                $title,
                $message,
                'info',
                [
                    'type' => 'jadwal_pengiriman',
                    'transaksi_id' => $transaksi->idTransaksiPenjualan,
                    'jadwal' => $jadwal->toISOString()
                ]
            ));
        }
    }

    /**
     * Kirim notifikasi status pengiriman
     */
    public function sendStatusPengirimanNotification($users, $status, $transaksi)
    {
        $statusMessages = [
            'dikirim' => 'Barang sedang dalam perjalanan',
            'sampai' => 'Barang telah sampai di tujuan',
            'diambil' => 'Barang telah diambil pembeli'
        ];

        $title = "Update Status Pengiriman";
        $message = $statusMessages[$status] ?? 'Status pengiriman diperbarui';

        foreach ($users as $user) {
            $user->notify(new ReuseMartNotification(
                $title,
                $message,
                'info',
                [
                    'type' => 'status_pengiriman',
                    'status' => $status,
                    'transaksi_id' => $transaksi->idTransaksiPenjualan
                ]
            ));
        }
    }

    /**
     * Kirim notifikasi barang didonasikan
     */
    public function sendBarangDidonasikanNotification($penitip, $produk, $organisasi)
    {
        $title = "Barang Anda Telah Didonasikan";
        $message = "Produk '{$produk->deskripsi}' telah didonasikan ke {$organisasi->nama}. Terima kasih atas kontribusi Anda!";

        $penitip->notify(new ReuseMartNotification(
            $title,
            $message,
            'success',
            [
                'type' => 'barang_didonasikan',
                'produk_id' => $produk->idProduk,
                'organisasi' => $organisasi->nama
            ]
        ));
    }
}
