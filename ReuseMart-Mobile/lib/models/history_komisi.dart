class HistoryKomisi {
  final int idKomisi;
  final String? tanggal;
  final double komisiHunter;
  final double komisiReuse;
  final double komisiPenitip;
  final KomisiProduk produk;
  final KomisiPenitip penitip;
  final KomisiTransaksi transaksi;

  HistoryKomisi({
    required this.idKomisi,
    this.tanggal,
    required this.komisiHunter,
    required this.komisiReuse,
    required this.komisiPenitip,
    required this.produk,
    required this.penitip,
    required this.transaksi,
  });

  factory HistoryKomisi.fromJson(Map<String, dynamic> json) {
    return HistoryKomisi(
      idKomisi: _parseInt(json['idKomisi']) ?? 0,
      tanggal: json['tanggal']?.toString() ?? '',
      komisiHunter: _parseDouble(json['komisiHunter']) ?? 0.0,
      komisiReuse: _parseDouble(json['komisiReuse']) ?? 0.0,
      komisiPenitip: _parseDouble(json['komisiPenitip']) ?? 0.0,
      produk: KomisiProduk.fromJson(json['produk'] ?? {}),
      penitip: KomisiPenitip.fromJson(json['penitip'] ?? {}),
      transaksi: KomisiTransaksi.fromJson(json['transaksi'] ?? {}),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'idKomisi': idKomisi,
      'tanggal': tanggal,
      'komisiHunter': komisiHunter,
      'komisiReuse': komisiReuse,
      'komisiPenitip': komisiPenitip,
      'produk': produk.toJson(),
      'penitip': penitip.toJson(),
      'transaksi': transaksi.toJson(),
    };
  }
}

class KomisiProduk {
  final int? idProduk;
  final String nama;
  final double hargaJual;
  final String? kategori;
  final String? gambarUtama;

  KomisiProduk({
    this.idProduk,
    required this.nama,
    required this.hargaJual,
    this.kategori,
    this.gambarUtama,
  });

  factory KomisiProduk.fromJson(Map<String, dynamic> json) {
    return KomisiProduk(
      idProduk: _parseInt(json['idProduk']) ?? 0,
      nama: json['nama'] ?? 'Produk tidak ditemukan',
      hargaJual: _parseDouble(json['hargaJual']) ?? 0.0,
      kategori: json['kategori'],
      gambarUtama: json['gambarUtama'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'idProduk': idProduk,
      'nama': nama,
      'hargaJual': hargaJual,
      'kategori': kategori,
      'gambarUtama': gambarUtama,
    };
  }
}

class KomisiPenitip {
  final int? idPenitip;
  final String nama;

  KomisiPenitip({this.idPenitip, required this.nama});

  factory KomisiPenitip.fromJson(Map<String, dynamic> json) {
    return KomisiPenitip(
      idPenitip: _parseInt(json['idPenitip']) ?? 0,
      nama: json['nama'] ?? 'Penitip tidak ditemukan',
    );
  }

  Map<String, dynamic> toJson() {
    return {'idPenitip': idPenitip, 'nama': nama};
  }
}

class KomisiTransaksi {
  final int? idTransaksiPenjualan;
  final String? tanggalLaku;
  final String? status;

  KomisiTransaksi({this.idTransaksiPenjualan, this.tanggalLaku, this.status});

  factory KomisiTransaksi.fromJson(Map<String, dynamic> json) {
    return KomisiTransaksi(
      idTransaksiPenjualan: _parseInt(json['idTransaksiPenjualan']) ?? 0,
      tanggalLaku: json['tanggalLaku']?.toString() ?? '',
      status: json['status'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'idTransaksiPenjualan': idTransaksiPenjualan,
      'tanggalLaku': tanggalLaku,
      'status': status,
    };
  }
}

class KomisiSummary {
  final double totalKomisi;
  final int totalTransaksi;
  final double rataRataKomisi;

  KomisiSummary({
    required this.totalKomisi,
    required this.totalTransaksi,
    required this.rataRataKomisi,
  });

  factory KomisiSummary.fromJson(Map<String, dynamic> json) {
    return KomisiSummary(
      totalKomisi: _parseDouble(json['totalKomisi']) ?? 0.0,
      totalTransaksi: _parseInt(json['totalTransaksi']) ?? 0,
      rataRataKomisi: _parseDouble(json['rataRataKomisi']) ?? 0.0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'totalKomisi': totalKomisi,
      'totalTransaksi': totalTransaksi,
      'rataRataKomisi': rataRataKomisi,
    };
  }
}

class HunterStats {
  final double komisiHariIni;
  final double komisiMingguIni;
  final double komisiBulanIni;
  final double totalKomisi;
  final int transaksiBulanIni;

  HunterStats({
    required this.komisiHariIni,
    required this.komisiMingguIni,
    required this.komisiBulanIni,
    required this.totalKomisi,
    required this.transaksiBulanIni,
  });

  factory HunterStats.fromJson(Map<String, dynamic> json) {
    return HunterStats(
      komisiHariIni: _parseDouble(json['komisiHariIni']) ?? 0.0,
      komisiMingguIni: _parseDouble(json['komisiMingguIni']) ?? 0.0,
      komisiBulanIni: _parseDouble(json['komisiBulanIni']) ?? 0.0,
      totalKomisi: _parseDouble(json['totalKomisi']) ?? 0.0,
      transaksiBulanIni: json['transaksiBulanIni'] ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'komisiHariIni': komisiHariIni,
      'komisiMingguIni': komisiMingguIni,
      'komisiBulanIni': komisiBulanIni,
      'totalKomisi': totalKomisi,
      'transaksiBulanIni': transaksiBulanIni,
    };
  }
}

int? _parseInt(dynamic value) {
  if (value == null) return null;
  if (value is int) return value;
  if (value is double) return value.toInt();
  if (value is String) return int.tryParse(value);
  return null;
}

double? _parseDouble(dynamic value) {
  if (value == null) return null;
  if (value is double) return value;
  if (value is int) return value.toDouble();
  if (value is String) return double.tryParse(value);
  return null;
}
