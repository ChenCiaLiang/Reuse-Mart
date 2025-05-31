<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksiPenjualan;
use App\Models\Komisi;
use Illuminate\Support\Facades\DB;

class KomisiController extends Controller
{
    public function getKomisiPenjualan($idDetailTransaksiPenjualan)
    {
        $data = DB::table('detail_transaksi_penjualan as dtpj')
            ->leftJoin('transaksi_penjualan as tpj', 'dtpj.idTransaksiPenjualan', '=', 'tpj.idTransaksiPenjualan')
            ->leftJoin('pembeli as pb', 'tpj.idPembeli', '=', 'pb.idPembeli')
            ->leftJoin('produk as pr', 'dtpj.idProduk', '=', 'pr.idProduk')
            ->leftJoin('detail_transaksi_penitipan as dtpt', 'dtpt.idProduk', '=', 'pr.idProduk')
            ->leftJoin('transaksi_penitipan as tpt', 'tpt.idTransaksiPenitipan', '=', 'dtpt.idTransaksiPenitipan')
            ->leftJoin('penitip as pt', 'pt.idPenitip', '=', 'tpt.idPenitip')
            ->leftJoin('pegawai as hunter', 'pr.idPegawai', '=', 'hunter.idPegawai')
            ->leftJoin('komisi as kom', function ($join) {
                $join->on('kom.idDetailTransaksiPenjualan', '=', 'dtpj.idDetailTransaksiPenjualan')
                    ->on('kom.idPenitip', '=', 'pt.idPenitip');
            })
            ->select([
                'pr.hargaJual as harga_barang',
                'pr.idProduk',
                'pt.idPenitip',
                'hunter.idPegawai as idHunter',
                'tpj.tanggalLaku',
                'tpt.tanggalMasukPenitipan',
                'pt.saldo as saldo_penitip',
                'tpt.statusPerpanjangan',
                'pb.poin as poin_pembeli',
                'kom.komisiPenitip',
                'kom.komisiHunter',
                'kom.komisiReuse'
            ])
            ->where('dtpj.idDetailTransaksiPenjualan', $idDetailTransaksiPenjualan)
            ->first();
        dd($data);

        if (!$data) {
            return response()->json([
                'message' => 'Detail Transaksi Penjualan tidak ditemukan',
            ], 404);
        }
        //harga jual barang
        // barangnya apa
        // penitip siapa
        // hunter???
        // tanggal laku penjualan
        // tanggal penitipan
        // saldo penitip
        // cuan bersih penitip
        // bonus penitip jika penitipan kurang dari 7 hari
        // poin pembeli brp
        // brp poin yang pembeli dapat
        // status perpanjangan

        $barang = $komisi->barang;
        $harga = $data->harga_barang;
        $detail = $komisi->barang->detailtransaksipenitipan->first();
        $pembeli = $komisi->transaksiPembelian->pembeli;
        $poinDapat = $komisi->transaksiPembelian->tambahan_poin;


        $komisi_hunter = 0;
        $komisi_reusemart = 0;
        $komisi_penitip = 0;
        $bonus_penitip = 0;

        if ($barang->id_hunter) {
            if ($detail->status_perpanjangan == 0) {
                $komisi_penitip = $harga * 0.8;
                $komisi_hunter = $harga * 0.05;
                $komisi_reusemart = $harga * 0.15;
            } else {
                $komisi_penitip = $harga * 0.7;
                $komisi_hunter = $harga * 0.05;
                $komisi_reusemart = $harga * 0.25;
            }
        } else {
            if ($detail->status_perpanjangan == 0) {
                $komisi_penitip = $harga * 0.8;
                $komisi_reusemart = $harga * 0.2;
            } else {
                $komisi_penitip = $harga * 0.7;
                $komisi_reusemart = $harga * 0.3;
            }
        }

        if ($detail->tanggal_penitipan >= now()->subDays(7)) {
            $bonus_penitip = $komisi_reusemart * 0.1;
            $komisi_reusemart = $komisi_reusemart - $bonus_penitip;
        }

        $komisi->update([
            'komisi_hunter' => $komisi_hunter,
            'komisi_reusemart' => $komisi_reusemart,
            'bonus_penitip' => $bonus_penitip,
        ]);

        if ($detail->transaksiPenitipan->penitip) {
            $penitip = $detail->transaksiPenitipan->penitip;
            $penitip->saldo += $komisi_penitip;
            $penitip->komisi_penitip += $bonus_penitip;
            $penitip->save();
        }

        if ($barang->id_hunter) {
            $hunter = $barang->hunter;
            $hunter->total_komisi += $komisi_hunter;
            $hunter->save();
        }

        if ($pembeli) {
            $pembeli->poin_loyalitas += $poinDapat;
            $pembeli->save();
        }

        return response()->json([
            'message' => 'Komisi berhasil dihitung',
        ], 200);
    }
}
