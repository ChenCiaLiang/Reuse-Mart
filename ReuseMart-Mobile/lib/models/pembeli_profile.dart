class PembeliProfile {
  final int idPembeli;
  final String nama;
  final String email;
  final int poin;
  final int totalTransaksi;
  final double totalPembelian;

  PembeliProfile({
    required this.idPembeli,
    required this.nama,
    required this.email,
    required this.poin,
    required this.totalTransaksi,
    required this.totalPembelian,
  });

  factory PembeliProfile.fromJson(Map<String, dynamic> json) {
    return PembeliProfile(
      idPembeli: _parseInt(json['idPembeli']) ?? 0,
      nama: json['nama']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      poin: _parseInt(json['poin']) ?? 0,
      totalTransaksi: _parseInt(json['total_transaksi']) ?? 0,
      totalPembelian: _parseDouble(json['total_pembelian']) ?? 0.0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'idPembeli': idPembeli,
      'nama': nama,
      'email': email,
      'poin': poin,
      'total_transaksi': totalTransaksi,
      'total_pembelian': totalPembelian,
    };
  }

  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is double) return value.toInt();
    if (value is String) return int.tryParse(value);
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
