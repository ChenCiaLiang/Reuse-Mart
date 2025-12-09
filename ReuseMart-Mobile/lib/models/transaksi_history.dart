class TransaksiHistory {
  final int idTransaksiPenjualan;
  final String? tanggalPesan;
  final String? tanggalLunas;
  final String? tanggalKirim;
  final String? tanggalAmbil;
  final StatusTransaksi status;
  final MetodePengiriman metodePengiriman;
  final AlamatPengiriman? alamatPengiriman;
  final String? kurir;
  final int poinDidapat;
  final int poinDigunakan;
  final double totalHarga;
  final double ongkosKirim;
  final double potonganPoin;
  final double totalBayar;
  final int jumlahItem;
  final List<DetailProduk> detailProduk;
  final List<TimelineItem> timeline;

  TransaksiHistory({
    required this.idTransaksiPenjualan,
    this.tanggalPesan,
    this.tanggalLunas,
    this.tanggalKirim,
    this.tanggalAmbil,
    required this.status,
    required this.metodePengiriman,
    this.alamatPengiriman,
    this.kurir,
    required this.poinDidapat,
    required this.poinDigunakan,
    required this.totalHarga,
    required this.ongkosKirim,
    required this.potonganPoin,
    required this.totalBayar,
    required this.jumlahItem,
    required this.detailProduk,
    required this.timeline,
  });

  factory TransaksiHistory.fromJson(Map<String, dynamic> json) {
    // Parse detail produk dulu untuk hitung jumlah item
    final detailProdukList = (json['detail_produk'] as List? ?? [])
        .map((item) => DetailProduk.fromJson(item))
        .toList();

    return TransaksiHistory(
      idTransaksiPenjualan: _parseInt(json['idTransaksiPenjualan']) ?? 0,
      tanggalPesan: json['tanggal_pesan'],
      tanggalLunas: json['tanggal_lunas'],
      tanggalKirim: json['tanggal_kirim'],
      tanggalAmbil: json['tanggal_ambil'],
      status: StatusTransaksi.fromJson(json['status']),
      metodePengiriman: _parseMetodePengiriman(json['metode_pengiriman']),
      alamatPengiriman: json['alamat_pengiriman'] != null
          ? AlamatPengiriman.fromJson(json['alamat_pengiriman'])
          : null,
      kurir: json['kurir'],

      // ✅ SAFE PARSING untuk semua field numerik
      poinDidapat: _parseInt(json['poin_didapat']) ?? 0,
      poinDigunakan: _parseInt(json['poin_digunakan']) ?? 0,
      totalHarga: _parseDouble(json['total_harga']) ?? 0.0,
      ongkosKirim: _parseDouble(json['ongkos_kirim']) ?? 0.0,
      potonganPoin: _parseDouble(json['potongan_poin']) ?? 0.0,
      totalBayar: _parseDouble(json['total_bayar']) ?? 0.0, // ✅ FIX!
      jumlahItem: _parseInt(json['jumlah_item']) ?? detailProdukList.length,

      detailProduk: detailProdukList,
      timeline: (json['timeline'] as List? ?? [])
          .map((item) => TimelineItem.fromJson(item))
          .toList(),
    );
  }

  // ✅ TAMBAHKAN Helper methods untuk safe parsing
  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) {
      return int.tryParse(value); // "297" → 297
    }
    if (value is double) return value.toInt();
    return null;
  }

  static double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) {
      return double.tryParse(value); // "2500000" → 2500000.0
    }
    return null;
  }

  // ✅ Helper method untuk parse metode pengiriman
  static MetodePengiriman _parseMetodePengiriman(dynamic metodePengiriman) {
    if (metodePengiriman is String) {
      // Handle string response dari backend
      return MetodePengiriman.fromString(metodePengiriman);
    } else if (metodePengiriman is Map<String, dynamic>) {
      // Handle object response (backward compatibility)
      return MetodePengiriman.fromJson(metodePengiriman);
    } else {
      // Fallback
      return MetodePengiriman(code: 'unknown', label: 'Unknown');
    }
  }
}

class StatusTransaksi {
  final String code;
  final String label;

  StatusTransaksi({required this.code, required this.label});

  factory StatusTransaksi.fromJson(Map<String, dynamic> json) {
    return StatusTransaksi(
      code: json['code'] ?? '',
      label: json['label'] ?? '',
    );
  }
}

class MetodePengiriman {
  final String code;
  final String label;

  MetodePengiriman({required this.code, required this.label});

  // ✅ Factory untuk handle String dari backend
  factory MetodePengiriman.fromString(String metode) {
    switch (metode.toLowerCase()) {
      case 'kurir':
        return MetodePengiriman(code: 'kurir', label: 'Kurir');
      case 'ambil_sendiri':
        return MetodePengiriman(code: 'ambil_sendiri', label: 'Ambil Sendiri');
      case 'pickup':
        return MetodePengiriman(code: 'pickup', label: 'Diambil Sendiri');
      default:
        return MetodePengiriman(code: metode, label: metode);
    }
  }

  // Keep existing fromJson for backward compatibility
  factory MetodePengiriman.fromJson(Map<String, dynamic> json) {
    return MetodePengiriman(
      code: json['code'] ?? '',
      label: json['label'] ?? '',
    );
  }
}

class AlamatPengiriman {
  final String? jenis;
  final String? alamatLengkap;
  final int? idAlamat;

  AlamatPengiriman({this.jenis, this.alamatLengkap, this.idAlamat});

  factory AlamatPengiriman.fromJson(Map<String, dynamic> json) {
    return AlamatPengiriman(
      jenis: json['jenis'],
      alamatLengkap: json['alamat_lengkap'],
      idAlamat: json['id_alamat'],
    );
  }
}

class DetailProduk {
  final int idProduk;
  final String nama;
  final double hargaJual;
  final String? kategori;
  final String? gambarUtama;
  final double berat;
  final String statusGaransi;
  final String? tanggalGaransi;

  DetailProduk({
    required this.idProduk,
    required this.nama,
    required this.hargaJual,
    this.kategori,
    this.gambarUtama,
    required this.berat,
    required this.statusGaransi,
    this.tanggalGaransi,
  });

  factory DetailProduk.fromJson(Map<String, dynamic> json) {
    return DetailProduk(
      idProduk: _parseInt(json['idProduk']) ?? 0, // ✅ Safe parsing
      nama: json['nama'] ?? '',
      hargaJual: _parseDouble(json['harga_jual']) ?? 0.0, // ✅ Safe parsing
      kategori: json['kategori'],
      gambarUtama: json['gambar_utama'],
      berat: _parseDouble(json['berat']) ?? 0.0, // ✅ Safe parsing
      statusGaransi: json['status_garansi'] ?? '',
      tanggalGaransi: json['tanggal_garansi'],
    );
  }

  // ✅ TAMBAHKAN helper methods yang sama di DetailProduk juga
  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    if (value is double) return value.toInt();
    return null;
  }

  static double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }
}

class TimelineItem {
  final String status;
  final String tanggal;
  final String keterangan;

  TimelineItem({
    required this.status,
    required this.tanggal,
    required this.keterangan,
  });

  factory TimelineItem.fromJson(Map<String, dynamic> json) {
    return TimelineItem(
      status: json['status'] ?? '',
      tanggal: json['tanggal'] ?? '',
      keterangan: json['keterangan'] ?? '',
    );
  }
}

class HistorySummary {
  final int totalTransaksi;
  final Map<String, String?> filterTanggalLunas;

  HistorySummary({
    required this.totalTransaksi,
    required this.filterTanggalLunas,
  });

  factory HistorySummary.fromJson(Map<String, dynamic> json) {
    return HistorySummary(
      totalTransaksi: json['total_transaksi'] ?? 0,
      filterTanggalLunas: Map<String, String?>.from(
        json['filter_tanggal_lunas'] ?? {},
      ),
    );
  }
}

class StatistikTransaksi {
  final int totalSelesai;
  final int totalProses;
  final int totalBatal;
  final int totalMenunggu;

  StatistikTransaksi({
    required this.totalSelesai,
    required this.totalProses,
    required this.totalBatal,
    required this.totalMenunggu,
  });

  factory StatistikTransaksi.fromJson(Map<String, dynamic> json) {
    return StatistikTransaksi(
      totalSelesai: json['total_selesai'] ?? 0,
      totalProses: json['total_proses'] ?? 0,
      totalBatal: json['total_batal'] ?? 0,
      totalMenunggu: json['total_menunggu'] ?? 0,
    );
  }
}
