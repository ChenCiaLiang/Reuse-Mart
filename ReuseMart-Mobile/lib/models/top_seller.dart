class TopSeller {
  final int idTopSeller;
  final PenitipProfile penitip;
  final TopSellerPeriode periode;
  final TopSellerBadge badge;

  TopSeller({
    required this.idTopSeller,
    required this.penitip,
    required this.periode,
    required this.badge,
  });

  factory TopSeller.fromJson(Map<String, dynamic> json) {
    return TopSeller(
      idTopSeller: _parseInt(json['id_top_seller']) ?? 0, // ✅ Safe!
      penitip: PenitipProfile.fromJson(json['penitip'] ?? {}),
      periode: TopSellerPeriode.fromJson(json['periode'] ?? {}),
      badge: TopSellerBadge.fromJson(json['badge'] ?? {}),
    );
  }
}

class PenitipProfile {
  final int id;
  final String nama;
  final String email;
  final double rating;
  final double totalSaldo;
  final double totalBonus;

  PenitipProfile({
    required this.id,
    required this.nama,
    required this.email,
    required this.rating,
    required this.totalSaldo,
    required this.totalBonus,
  });

  factory PenitipProfile.fromJson(Map<String, dynamic> json) {
    return PenitipProfile(
      id: _parseInt(json['id']) ?? 0, // ✅ Safe!
      nama: json['nama']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      rating: _parseDouble(json['rating']) ?? 0.0, // ✅ Safe!
      totalSaldo: _parseDouble(json['total_saldo']) ?? 0.0, // ✅ Safe!
      totalBonus: _parseDouble(json['total_bonus']) ?? 0.0, // ✅ Safe!
    );
  }
}

class TopSellerPeriode {
  final String tanggalMulai;
  final String tanggalSelesai;
  final int sisaHari;
  final bool isActive;

  TopSellerPeriode({
    required this.tanggalMulai,
    required this.tanggalSelesai,
    required this.sisaHari,
    required this.isActive,
  });

  factory TopSellerPeriode.fromJson(Map<String, dynamic> json) {
    return TopSellerPeriode(
      tanggalMulai: json['tanggal_mulai']?.toString() ?? '',
      tanggalSelesai: json['tanggal_selesai']?.toString() ?? '',
      sisaHari: _parseInt(json['sisa_hari']) ?? 0, // ✅ Safe!
      isActive: _parseBool(json['is_active']) ?? false, // ✅ Safe!
    );
  }
}

class TopSellerBadge {
  final String title;
  final String description;
  final String color;
  final String icon;

  TopSellerBadge({
    required this.title,
    required this.description,
    required this.color,
    required this.icon,
  });

  factory TopSellerBadge.fromJson(Map<String, dynamic> json) {
    return TopSellerBadge(
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      color: json['color']?.toString() ?? '#FFD700',
      icon: json['icon']?.toString() ?? '🏆',
    );
  }
}

// ✅ Helper methods (tambahkan di bawah file)
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

bool? _parseBool(dynamic value) {
  if (value == null) return null;
  if (value is bool) return value;
  if (value is String) return value.toLowerCase() == 'true';
  if (value is int) return value != 0;
  return null;
}
