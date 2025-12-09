class HunterProfile {
  final int idPegawai;
  final String nama;
  final String email;
  final String? noTelp;
  final String? alamat;
  final String? tanggalLahir;
  final String jabatan;
  final double totalKomisi;
  final double komisiBulanIni;
  final int totalTransaksi;
  final String? joinDate;

  HunterProfile({
    required this.idPegawai,
    required this.nama,
    required this.email,
    this.noTelp,
    this.alamat,
    this.tanggalLahir,
    required this.jabatan,
    required this.totalKomisi,
    required this.komisiBulanIni,
    required this.totalTransaksi,
    this.joinDate,
  });

  factory HunterProfile.fromJson(Map<String, dynamic> json) {
    return HunterProfile(
      idPegawai: _parseInt(json['idPegawai']) ?? 0,
      nama: json['nama']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      noTelp: json['noTelp']?.toString(),
      alamat: json['alamat']?.toString(),
      tanggalLahir: json['tanggalLahir']?.toString(),
      jabatan: json['jabatan']?.toString() ?? 'Hunter',
      totalKomisi: _parseDouble(json['totalKomisi']) ?? 0.0,
      komisiBulanIni: _parseDouble(json['komisiBulanIni']) ?? 0.0,
      totalTransaksi: _parseInt(json['totalTransaksi']) ?? 0,
      joinDate: json['joinDate']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'idPegawai': idPegawai,
      'nama': nama,
      'email': email,
      'noTelp': noTelp,
      'alamat': alamat,
      'tanggalLahir': tanggalLahir,
      'jabatan': jabatan,
      'totalKomisi': totalKomisi,
      'komisiBulanIni': komisiBulanIni,
      'totalTransaksi': totalTransaksi,
      'joinDate': joinDate,
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
