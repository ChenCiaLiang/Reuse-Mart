class PenitipProfile {
  final int idPenitip;
  final String nama;
  final String email;
  final String alamat;
  final int poin;
  final double saldo;
  final double komisi;
  final double bonus;
  final double rating;

  PenitipProfile({
    required this.idPenitip,
    required this.nama,
    required this.email,
    required this.alamat,
    required this.poin,
    required this.saldo,
    required this.komisi,
    required this.bonus,
    required this.rating,
  });

  factory PenitipProfile.fromJson(Map<String, dynamic> json) {
    return PenitipProfile(
      idPenitip: _parseInt(json['idPenitip']) ?? 0,
      nama: json['nama']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      alamat: json['alamat']?.toString() ?? '',
      poin: _parseInt(json['poin']) ?? 0,
      saldo: _parseDouble(json['saldo']) ?? 0.0,
      komisi: _parseDouble(json['komisi']) ?? 0.0,
      bonus: _parseDouble(json['bonus']) ?? 0.0,
      rating: _parseDouble(json['rating']) ?? 0.0,
    );
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
