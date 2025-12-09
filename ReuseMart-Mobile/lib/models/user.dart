class User {
  final int? id;
  final String nama;
  final String email;
  final String role;
  final String? alamat;
  final int? poin;
  final double? saldo;
  final double? rating;
  final String? nik;
  final String? fotoKTP;
  final String? noTelp;
  final DateTime? tanggalLahir;
  final int? idJabatan;

  User({
    this.id,
    required this.nama,
    required this.email,
    required this.role,
    this.alamat,
    this.poin,
    this.saldo,
    this.rating,
    this.nik,
    this.fotoKTP,
    this.noTelp,
    this.tanggalLahir,
    this.idJabatan,
  });

  factory User.fromJson(Map<String, dynamic> json, String role) {
    switch (role) {
      case 'pembeli':
        return User(
          id: _parseInt(json['idPembeli']),
          nama: json['nama']?.toString() ?? '',
          email: json['email']?.toString() ?? '',
          role: role,
          poin: _parseInt(json['poin']),
        );
      case 'penitip':
        return User(
          id: _parseInt(json['idPenitip']),
          nama: json['nama']?.toString() ?? '',
          email: json['email']?.toString() ?? '',
          role: role,
          alamat: json['alamat']?.toString(),
          nik: json['nik']?.toString(),
          fotoKTP: json['fotoKTP']?.toString(),
          poin: _parseInt(json['poin']),
          saldo: _parseDouble(json['saldo']),
          rating: _parseDouble(json['rating']),
        );
      case 'organisasi':
        return User(
          id: _parseInt(json['idOrganisasi']),
          nama: json['nama']?.toString() ?? '',
          email: json['email']?.toString() ?? '',
          role: role,
          alamat: json['alamat']?.toString(),
        );
      default: // pegawai roles
        return User(
          id: _parseInt(json['idPegawai']),
          nama: json['nama']?.toString() ?? '',
          email: json['email']?.toString() ?? '',
          role: role,
          alamat: json['alamat']?.toString(),
          noTelp: json['noTelp']?.toString(),
          tanggalLahir: _parseDateTime(json['tanggalLahir']),
          idJabatan: _parseInt(json['idJabatan']),
        );
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nama': nama,
      'email': email,
      'role': role,
      'alamat': alamat,
      'poin': poin,
      'saldo': saldo,
      'rating': rating,
      'nik': nik,
      'fotoKTP': fotoKTP,
      'noTelp': noTelp,
      'tanggalLahir': tanggalLahir?.toIso8601String(),
      'idJabatan': idJabatan,
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
