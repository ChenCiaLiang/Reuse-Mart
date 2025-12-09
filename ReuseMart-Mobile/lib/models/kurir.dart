class KurirProfile {
  final int idPegawai;
  final String nama;
  final String email;
  final String noTelp;
  final String alamat;
  final DateTime tanggalLahir;
  final String jabatan;
  final DateTime? joinDate;
  final int totalPengiriman;
  final int pengirimanBulanIni;
  final int pengirimanHariIni;

  KurirProfile({
    required this.idPegawai,
    required this.nama,
    required this.email,
    required this.noTelp,
    required this.alamat,
    required this.tanggalLahir,
    required this.jabatan,
    this.joinDate,
    required this.totalPengiriman,
    required this.pengirimanBulanIni,
    required this.pengirimanHariIni,
  });

  factory KurirProfile.fromJson(Map<String, dynamic> json) {
    return KurirProfile(
      idPegawai: _parseInt(json['idPegawai']) ?? 0,
      nama: json['nama']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      noTelp: json['noTelp']?.toString() ?? '',
      alamat: json['alamat']?.toString() ?? '',
      tanggalLahir: _parseDateTime(json['tanggalLahir']) ?? DateTime.now(),
      jabatan: json['jabatan']?.toString() ?? 'Kurir',
      joinDate: _parseDateTime(json['joinDate']),
      totalPengiriman: _parseInt(json['totalPengiriman']) ?? 0,
      pengirimanBulanIni: _parseInt(json['pengirimanBulanIni']) ?? 0,
      pengirimanHariIni: _parseInt(json['pengirimanHariIni']) ?? 0,
    );
  }

  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is double) return value.toInt();
    if (value is String) return int.tryParse(value);
    return null;
  }

  static DateTime? _parseDateTime(dynamic value) {
    if (value == null) return null;
    if (value is String) {
      try {
        return DateTime.parse(value);
      } catch (e) {
        return null;
      }
    }
    return null;
  }
}

class TugasPengiriman {
  final int idTransaksiPenjualan;
  final String nomorNota;
  final DateTime tanggalPesan;
  final DateTime? tanggalKirim;
  final DateTime? tanggalSelesai;
  final String status;
  final String namaPembeli;
  final String alamatPengiriman;
  final String metodePengiriman;
  final List<ItemPengiriman> items;
  final double totalHarga;

  TugasPengiriman({
    required this.idTransaksiPenjualan,
    required this.nomorNota,
    required this.tanggalPesan,
    this.tanggalKirim,
    this.tanggalSelesai,
    required this.status,
    required this.namaPembeli,
    required this.alamatPengiriman,
    required this.metodePengiriman,
    required this.items,
    required this.totalHarga,
  });

  factory TugasPengiriman.fromJson(Map<String, dynamic> json) {
    return TugasPengiriman(
      idTransaksiPenjualan:
          KurirProfile._parseInt(json['idTransaksiPenjualan']) ?? 0,
      nomorNota: json['nomorNota']?.toString() ?? '',
      tanggalPesan:
          KurirProfile._parseDateTime(json['tanggalPesan']) ?? DateTime.now(),
      tanggalKirim: KurirProfile._parseDateTime(json['tanggalKirim']),
      tanggalSelesai: KurirProfile._parseDateTime(json['tanggalSelesai']),
      status: json['status']?.toString() ?? '',
      namaPembeli: json['namaPembeli']?.toString() ?? '',
      alamatPengiriman: json['alamatPengiriman']?.toString() ?? '',
      metodePengiriman: json['metodePengiriman']?.toString() ?? 'kurir',
      items:
          (json['items'] as List<dynamic>?)
              ?.map((item) => ItemPengiriman.fromJson(item))
              .toList() ??
          [],
      totalHarga: _parseDouble(json['totalHarga']) ?? 0.0,
    );
  }

  // ✅ Helper method
  static double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }

  String get statusDisplayName {
    switch (status.toLowerCase()) {
      case 'disiapkan':
        return 'Menunggu Pengiriman';
      case 'kirim':
        return 'Sedang Dikirim';
      case 'terjual':
        return 'Terkirim';
      default:
        return status;
    }
  }

  bool get canUpdateToSelesai {
    return status.toLowerCase() == 'kirim';
  }
}

class ItemPengiriman {
  final String namaProduk;
  final int quantity;
  final double harga;
  final String? gambar;

  ItemPengiriman({
    required this.namaProduk,
    required this.quantity,
    required this.harga,
    this.gambar,
  });

  factory ItemPengiriman.fromJson(Map<String, dynamic> json) {
    return ItemPengiriman(
      namaProduk: json['namaProduk']?.toString() ?? '',
      quantity: KurirProfile._parseInt(json['quantity']) ?? 1,
      harga: TugasPengiriman._parseDouble(json['harga']) ?? 0.0,
      gambar: json['gambar']?.toString(),
    );
  }
}

class KurirStats {
  final int totalPengiriman;
  final int pengirimanHariIni;
  final int pengirimanMingguIni;
  final int pengirimanBulanIni;
  final int pengirimanSelesai;
  final int pengirimanDalamProses;

  KurirStats({
    required this.totalPengiriman,
    required this.pengirimanHariIni,
    required this.pengirimanMingguIni,
    required this.pengirimanBulanIni,
    required this.pengirimanSelesai,
    required this.pengirimanDalamProses,
  });

  factory KurirStats.fromJson(Map<String, dynamic> json) {
    return KurirStats(
      totalPengiriman: KurirProfile._parseInt(json['totalPengiriman']) ?? 0,
      pengirimanHariIni: KurirProfile._parseInt(json['pengirimanHariIni']) ?? 0,
      pengirimanMingguIni:
          KurirProfile._parseInt(json['pengirimanMingguIni']) ?? 0,
      pengirimanBulanIni:
          KurirProfile._parseInt(json['pengirimanBulanIni']) ?? 0,
      pengirimanSelesai: KurirProfile._parseInt(json['pengirimanSelesai']) ?? 0,
      pengirimanDalamProses:
          KurirProfile._parseInt(json['pengirimanDalamProses']) ?? 0,
    );
  }
}
