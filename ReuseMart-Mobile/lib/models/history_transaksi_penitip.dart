class HistoryTransaksiPenitip {
  final int idTransaksiPenitipan;
  final int? idTransaksiPenjualan;
  final int idProduk;
  final String namaProduk;
  final double hargaJual;
  final String? gambar;
  final String? kategori;
  final String tanggalPesan;
  final String? tanggalLunas;
  final String status;
  final String? statusPenitipan;
  final double komisiPenitip;
  final double komisiHunter;
  final double komisiReuse;

  HistoryTransaksiPenitip({
    required this.idTransaksiPenitipan,
    this.idTransaksiPenjualan,
    required this.idProduk,
    required this.namaProduk,
    required this.hargaJual,
    this.gambar,
    this.kategori,
    required this.tanggalPesan,
    this.tanggalLunas,
    required this.status,
    this.statusPenitipan,
    required this.komisiPenitip,
    required this.komisiHunter,
    required this.komisiReuse,
  });

  factory HistoryTransaksiPenitip.fromJson(Map<String, dynamic> json) {
    return HistoryTransaksiPenitip(
      idTransaksiPenitipan: _parseInt(json['idTransaksiPenitipan']) ?? 0,
      idTransaksiPenjualan: _parseInt(json['idTransaksiPenjualan']),
      idProduk: _parseInt(json['idProduk']) ?? 0,
      namaProduk: json['nama_produk']?.toString() ?? '',
      hargaJual: _parseDouble(json['harga_jual']) ?? 0.0,
      gambar: json['gambar']?.toString(),
      kategori: json['kategori']?.toString(),
      tanggalPesan: json['tanggal_pesan']?.toString() ?? '',
      tanggalLunas: json['tanggal_lunas']?.toString(),
      status: json['status']?.toString() ?? '',
      statusPenitipan: json['status_penitipan']?.toString(),
      komisiPenitip: _parseDouble(json['komisi_penitip']) ?? 0.0,
      komisiHunter: _parseDouble(json['komisi_hunter']) ?? 0.0,
      komisiReuse: _parseDouble(json['komisi_reuse']) ?? 0.0,
    );
  }

  bool get isTerjual =>
      idTransaksiPenjualan != null && tanggalLunas != null && komisiPenitip > 0;

  String get statusDisplay {
    if (isTerjual) {
      switch (status) {
        case 'terjual':
          return 'Terjual';
        case 'selesai':
          return 'Selesai';
        case 'diambil':
          return 'Diambil';
        case 'kirim':
          return 'Dikirim';
        case 'disiapkan':
          return 'Disiapkan';
        default:
          return status;
      }
    } else {
      switch (status) {
        case 'Tersedia':
          return 'Tersedia di Toko';
        case 'Didonasikan':
          return 'Sudah Didonasikan';
        case 'Terjual':
          return 'Menunggu Pembayaran';
        default:
          return status;
      }
    }
  }

  String get statusColorType {
    if (isTerjual) {
      switch (status) {
        case 'terjual':
        case 'selesai':
        case 'diambil':
          return 'success';
        case 'kirim':
        case 'disiapkan':
          return 'info';
        default:
          return 'secondary';
      }
    } else {
      switch (status) {
        case 'Tersedia':
          return 'warning';
        case 'Didonasikan':
          return 'info';
        case 'Terjual':
          return 'primary';
        default:
          return 'secondary';
      }
    }
  }

  String get penitipanInfo {
    if (statusPenitipan != null) {
      switch (statusPenitipan) {
        case 'Aktif':
          return 'Sedang Dititipkan';
        case 'Selesai':
          return 'Penitipan Selesai';
        case 'Diambil':
          return 'Sudah Diambil';
        case 'Hangus':
          return 'Masa Penitipan Habis';
        default:
          return statusPenitipan!;
      }
    }
    return '';
  }
}

class PaginationInfo {
  final int currentPage;
  final int perPage;
  final int total;
  final int lastPage;
  final bool hasMore;

  PaginationInfo({
    required this.currentPage,
    required this.perPage,
    required this.total,
    required this.lastPage,
    required this.hasMore,
  });

  factory PaginationInfo.fromJson(Map<String, dynamic> json) {
    return PaginationInfo(
      currentPage: _parseInt(json['current_page']) ?? 1,
      perPage: _parseInt(json['per_page']) ?? 10,
      total: _parseInt(json['total']) ?? 0,
      lastPage: _parseInt(json['last_page']) ?? 1,
      hasMore: json['has_more'] == true,
    );
  }
}

class FilterInfo {
  final String startDate;
  final String endDate;

  FilterInfo({required this.startDate, required this.endDate});

  factory FilterInfo.fromJson(Map<String, dynamic> json) {
    return FilterInfo(
      startDate: json['start_date']?.toString() ?? '',
      endDate: json['end_date']?.toString() ?? '',
    );
  }

  String get displayPeriod {
    try {
      if (startDate.isEmpty || endDate.isEmpty) {
        return 'Semua Periode';
      }

      final start = DateTime.parse(startDate);
      final end = DateTime.parse(endDate);

      final startFormatted = '${start.day}/${start.month}/${start.year}';
      final endFormatted = '${end.day}/${end.month}/${end.year}';

      return '$startFormatted - $endFormatted';
    } catch (e) {
      if (startDate.isEmpty && endDate.isEmpty) {
        return 'Semua Periode';
      }
      return '$startDate - $endDate';
    }
  }
}

class DetailTransaksiPenitip {
  final TransaksiInfo transaksi;
  final PembeliInfo? pembeli;
  final List<ProdukInfo> produk;
  final List<KomisiInfo> komisi;
  final double totalKomisiPenitip;
  final Map<String, dynamic>? penitipanSummary;

  DetailTransaksiPenitip({
    required this.transaksi,
    this.pembeli,
    required this.produk,
    required this.komisi,
    required this.totalKomisiPenitip,
    this.penitipanSummary,
  });

  factory DetailTransaksiPenitip.fromJson(Map<String, dynamic> json) {
    return DetailTransaksiPenitip(
      transaksi: TransaksiInfo.fromJson(json['transaksi'] ?? {}),
      pembeli: json['pembeli'] != null
          ? PembeliInfo.fromJson(json['pembeli'])
          : null,
      produk: (json['produk'] as List? ?? [])
          .map((item) => ProdukInfo.fromJson(item))
          .toList(),
      komisi: (json['komisi'] as List? ?? [])
          .map((item) => KomisiInfo.fromJson(item))
          .toList(),
      totalKomisiPenitip: _parseDouble(json['total_komisi_penitip']) ?? 0.0,
      penitipanSummary: json['penitipan_summary'] as Map<String, dynamic>?,
    );
  }
}

class TransaksiInfo {
  final int? idTransaksiPenjualan;
  final String tanggalPesan;
  final String? tanggalLunas;
  final String status;
  final String? metodePengiriman;
  final dynamic alamatPengiriman;

  TransaksiInfo({
    this.idTransaksiPenjualan,
    required this.tanggalPesan,
    this.tanggalLunas,
    required this.status,
    this.metodePengiriman,
    this.alamatPengiriman,
  });

  factory TransaksiInfo.fromJson(Map<String, dynamic> json) {
    return TransaksiInfo(
      idTransaksiPenjualan: _parseInt(json['idTransaksiPenjualan']),
      tanggalPesan: json['tanggal_pesan']?.toString() ?? '',
      tanggalLunas: json['tanggal_lunas']?.toString(),
      status: json['status']?.toString() ?? '',
      metodePengiriman: json['metode_pengiriman']?.toString(),
      alamatPengiriman: json['alamat_pengiriman'],
    );
  }
}

class PembeliInfo {
  final String nama;
  final String email;

  PembeliInfo({required this.nama, required this.email});

  factory PembeliInfo.fromJson(Map<String, dynamic> json) {
    return PembeliInfo(
      nama: json['nama']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
    );
  }
}

class ProdukInfo {
  final int idProduk;
  final int? idTransaksiPenitipan;
  final String nama;
  final String? kategori;
  final double hargaJual;
  final String? gambar;
  final String? tanggalPenitipan;
  final String? statusPenitipan;

  ProdukInfo({
    required this.idProduk,
    this.idTransaksiPenitipan,
    required this.nama,
    this.kategori,
    required this.hargaJual,
    this.gambar,
    this.tanggalPenitipan,
    this.statusPenitipan,
  });

  factory ProdukInfo.fromJson(Map<String, dynamic> json) {
    return ProdukInfo(
      idProduk: _parseInt(json['idProduk']) ?? 0,
      idTransaksiPenitipan: _parseInt(json['idTransaksiPenitipan']),
      nama: json['nama']?.toString() ?? '',
      kategori: json['kategori']?.toString(),
      hargaJual: _parseDouble(json['harga_jual']) ?? 0.0,
      gambar: json['gambar']?.toString(),
      tanggalPenitipan: json['tanggal_penitipan']?.toString(),
      statusPenitipan: json['status_penitipan']?.toString(),
    );
  }
}

class KomisiInfo {
  final int? idProduk;
  final double komisiPenitip;
  final double komisiHunter;
  final double komisiReuse;

  KomisiInfo({
    this.idProduk,
    required this.komisiPenitip,
    required this.komisiHunter,
    required this.komisiReuse,
  });

  factory KomisiInfo.fromJson(Map<String, dynamic> json) {
    return KomisiInfo(
      idProduk: _parseInt(json['idProduk']),
      komisiPenitip: _parseDouble(json['komisi_penitip']) ?? 0.0,
      komisiHunter: _parseDouble(json['komisi_hunter']) ?? 0.0,
      komisiReuse: _parseDouble(json['komisi_reuse']) ?? 0.0,
    );
  }
}

int? _parseInt(dynamic value) {
  if (value == null) return null;
  if (value is int) return value;
  if (value is double) return value.toInt();
  if (value is String) {
    try {
      return int.parse(value);
    } catch (e) {
      return null;
    }
  }
  return null;
}

double? _parseDouble(dynamic value) {
  if (value == null) return null;
  if (value is double) return value;
  if (value is int) return value.toDouble();
  if (value is String) {
    try {
      return double.parse(value);
    } catch (e) {
      return null;
    }
  }
  return null;
}
