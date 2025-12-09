<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksiPenjualan;
use App\Models\Komisi;
use App\Models\Pegawai;
use App\Models\Pembeli;
use App\Models\Penitip;
use Carbon\Carbon;
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
            ->leftJoin('pegawai as hunter', 'tpt.idHunter', '=', 'hunter.idPegawai')
            ->leftJoin('komisi as kom', function ($join) {
                $join->on('kom.idDetailTransaksiPenjualan', '=', 'dtpj.idDetailTransaksiPenjualan')
                    ->on('kom.idPenitip', '=', 'pt.idPenitip');
            })
            ->select([
                'dtpj.idDetailTransaksiPenjualan',
                'tpj.poinDidapat',
                'pr.hargaJual',
                'pb.idPembeli',
                'pr.idProduk',
                'pr.hargaJual',
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

        if (!$data) {
            return response()->json([
                'message' => 'Detail Transaksi Penjualan tidak ditemukan',
            ], 404);
        }

        if ($data->komisiPenitip !== null) {
            return response()->json([
                'message' => 'Komisi sudah dihitung sebelumnya',
            ], 400);
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

        try {
            DB::beginTransaction();

            $harga = $data->hargaJual;
            $poinDapat = $data->poinDidapat;

            $komisiHunter = 0;
            $komisiReusemart = 0;
            $komisiPenitip = 0;
            $bonusPenitip = 0;
            if ($data->idHunter) {
                if ($data->statusPerpanjangan == 0) {
                    $komisiPenitip = $harga * 0.8;
                    $komisiHunter = $harga * 0.05;
                    $komisiReusemart = $harga * 0.15;
                } else {
                    $komisiPenitip = $harga * 0.7;
                    $komisiHunter = $harga * 0.05;
                    $komisiReusemart = $harga * 0.25;
                }
            } else {
                if ($data->statusPerpanjangan == 0) {
                    $komisiPenitip = $harga * 0.8;
                    $komisiReusemart = $harga * 0.2;
                } else {
                    $komisiPenitip = $harga * 0.7;
                    $komisiReusemart = $harga * 0.3;
                }
            }

            $tanggalMasuk = Carbon::parse($data->tanggalMasukPenitipan);
            $tanggalLaku = Carbon::parse($data->tanggalLaku);
            $selisihHari = $tanggalMasuk->diffInDays($tanggalLaku);

            if ($selisihHari < 7) {
                $bonusPenitip = $komisiReusemart * 0.1;
                $komisiReusemart = $komisiReusemart - $bonusPenitip;
                $komisiPenitip += $bonusPenitip;
            }

            Komisi::create([
                'idDetailTransaksiPenjualan' => $data->idDetailTransaksiPenjualan,
                'komisiPenitip' => $komisiPenitip,
                'komisiHunter' => $komisiHunter,
                'komisiReuse' => $komisiReusemart,
                'idPegawai' => 3, //CEKME
                'idPenitip' => $data->idPenitip,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($data->idPenitip) {
                $penitip = Penitip::find($data->idPenitip);
                if ($penitip) {
                    $penitip->saldo += $komisiPenitip;
                    if ($bonusPenitip > 0) {
                        $penitip->bonus += $bonusPenitip;
                    }
                    $penitip->save();
                }
            }

            if ($data->idHunter && $komisiHunter > 0) {
                $hunter = Pegawai::find($data->idHunter);
                if ($hunter) {
                    $hunter->komisi = ($hunter->komisi ?? 0) + $komisiHunter;
                    $hunter->save();
                }
            }

            if ($data->idPembeli && $poinDapat > 0) {
                $pembeli = Pembeli::find($data->idPembeli);
                if ($pembeli) {
                    $pembeli->poin += $poinDapat;
                    $pembeli->save();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Komisi berhasil dihitung dan didistribusikan',
                'data' => [
                    'komisi_penitip' => $komisiPenitip,
                    'komisi_hunter' => $komisiHunter,
                    'komisi_reusemart' => $komisiReusemart,
                    'bonus_penitip' => $bonusPenitip,
                    'poin_pembeli' => $poinDapat,
                    'terjual_cepat' => $selisihHari < 7,
                    'selisih_hari' => $selisihHari
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghitung komisi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
