// lib/models/merchandise.dart
class Merchandise {
  final int idMerchandise;
  final String nama;
  final int jumlahPoin;
  final int stok;
  final String gambar;
  final bool tersedia;

  Merchandise({
    required this.idMerchandise,
    required this.nama,
    required this.jumlahPoin,
    required this.stok,
    required this.gambar,
    required this.tersedia,
  });

  factory Merchandise.fromJson(Map<String, dynamic> json) {
    return Merchandise(
      idMerchandise: _parseInt(json['idMerchandise']) ?? 0,
      nama: json['nama'],
      jumlahPoin: _parseInt(json['jumlahPoin']) ?? 0,
      stok: _parseInt(json['stok']) ?? 0,
      gambar: json['gambar'] ?? '',
      tersedia: json['tersedia'] ?? (json['stok'] > 0),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'idMerchandise': idMerchandise,
      'nama': nama,
      'jumlahPoin': jumlahPoin,
      'stok': stok,
      'gambar': gambar,
      'tersedia': tersedia,
    };
  }
}

// lib/models/klaim_response.dart
class KlaimResponse {
  final int idPenukaran;
  final MerchandiseInfo merchandise;
  final int poinDigunakan;
  final int sisaPoin;
  final String tanggalKlaim;
  final String statusPenukaran;
  final String instruksi;

  KlaimResponse({
    required this.idPenukaran,
    required this.merchandise,
    required this.poinDigunakan,
    required this.sisaPoin,
    required this.tanggalKlaim,
    required this.statusPenukaran,
    required this.instruksi,
  });

  factory KlaimResponse.fromJson(Map<String, dynamic> json) {
    return KlaimResponse(
      idPenukaran: _parseInt(json['idPenukaran']) ?? 0,
      merchandise: MerchandiseInfo.fromJson(json['merchandise']),
      poinDigunakan: _parseInt(json['poinDigunakan']) ?? 0,
      sisaPoin: _parseInt(json['sisaPoin']) ?? 0,
      tanggalKlaim: json['tanggalKlaim']?.toString() ?? '',
      statusPenukaran: json['statusPenukaran'],
      instruksi: json['instruksi'],
    );
  }
}

// lib/models/merchandise_info.dart
class MerchandiseInfo {
  final String nama;
  final String gambar;

  MerchandiseInfo({required this.nama, required this.gambar});

  factory MerchandiseInfo.fromJson(Map<String, dynamic> json) {
    return MerchandiseInfo(nama: json['nama'], gambar: json['gambar'] ?? '');
  }
}

// lib/models/history_klaim.dart
class HistoryKlaim {
  final int idPenukaran;
  final String namaMerchandise;
  final int jumlahPoin;
  final String tanggalPengajuan;
  final String? tanggalPenerimaan;
  final String statusPenukaran;
  final String statusLabel;
  final String gambar;

  HistoryKlaim({
    required this.idPenukaran,
    required this.namaMerchandise,
    required this.jumlahPoin,
    required this.tanggalPengajuan,
    this.tanggalPenerimaan,
    required this.statusPenukaran,
    required this.statusLabel,
    required this.gambar,
  });

  factory HistoryKlaim.fromJson(Map<String, dynamic> json) {
    return HistoryKlaim(
      idPenukaran: _parseInt(json['idPenukaran']) ?? 0,
      namaMerchandise: json['namaMerchandise'],
      jumlahPoin: _parseInt(json['jumlahPoin']) ?? 0,
      tanggalPengajuan: json['tanggalPengajuan']?.toString() ?? '',
      tanggalPenerimaan: json['tanggalPenerimaan']?.toString() ?? '',
      statusPenukaran: json['statusPenukaran'],
      statusLabel: json['statusLabel'],
      gambar: json['gambar'] ?? '',
    );
  }
}

// lib/models/detail_klaim.dart
class DetailKlaim {
  final int idPenukaran;
  final MerchandiseDetail merchandise;
  final String tanggalPengajuan;
  final String? tanggalPenerimaan;
  final String statusPenukaran;
  final String statusLabel;
  final String catatan;

  DetailKlaim({
    required this.idPenukaran,
    required this.merchandise,
    required this.tanggalPengajuan,
    this.tanggalPenerimaan,
    required this.statusPenukaran,
    required this.statusLabel,
    required this.catatan,
  });

  factory DetailKlaim.fromJson(Map<String, dynamic> json) {
    return DetailKlaim(
      idPenukaran: _parseInt(json['idPenukaran']) ?? 0,
      merchandise: MerchandiseDetail.fromJson(json['merchandise']),
      tanggalPengajuan: json['tanggalPengajuan']?.toString() ?? '',
      tanggalPenerimaan: json['tanggalPenerimaan']?.toString() ?? '',
      statusPenukaran: json['statusPenukaran'],
      statusLabel: json['statusLabel'],
      catatan: json['catatan'] ?? '',
    );
  }
}

// lib/models/merchandise_detail.dart
class MerchandiseDetail {
  final String nama;
  final int jumlahPoin;
  final String gambar;

  MerchandiseDetail({
    required this.nama,
    required this.jumlahPoin,
    required this.gambar,
  });

  factory MerchandiseDetail.fromJson(Map<String, dynamic> json) {
    return MerchandiseDetail(
      nama: json['nama'],
      jumlahPoin: _parseInt(json['jumlahPoin']) ?? 0,
      gambar: json['gambar'] ?? '',
    );
  }
}

// lib/models/poin_data.dart
class PoinData {
  final String nama;
  final int poin;

  PoinData({required this.nama, required this.poin});

  factory PoinData.fromJson(Map<String, dynamic> json) {
    return PoinData(nama: json['nama'], poin: _parseInt(json['poin']) ?? 0);
  }
}

// lib/models/api_response.dart
class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final String? error;

  ApiResponse({
    required this.success,
    required this.message,
    this.data,
    this.error,
  });

  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(dynamic) fromJsonT,
  ) {
    return ApiResponse<T>(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'] != null ? fromJsonT(json['data']) : null,
      error: json['error'],
    );
  }
}

// lib/models/klaim_request.dart
class KlaimRequest {
  final int idMerchandise;

  KlaimRequest({required this.idMerchandise});

  Map<String, dynamic> toJson() {
    return {'idMerchandise': idMerchandise};
  }
}

// lib/models/error_response.dart
class ErrorResponse {
  final bool success;
  final String message;
  final ErrorData? data;

  ErrorResponse({required this.success, required this.message, this.data});

  factory ErrorResponse.fromJson(Map<String, dynamic> json) {
    return ErrorResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'] != null ? ErrorData.fromJson(json['data']) : null,
    );
  }
}

// lib/models/error_data.dart
class ErrorData {
  final int? poinDibutuhkan;
  final int? poinAnda;
  final int? kekurangan;

  ErrorData({this.poinDibutuhkan, this.poinAnda, this.kekurangan});

  factory ErrorData.fromJson(Map<String, dynamic> json) {
    return ErrorData(
      poinDibutuhkan: json['poinDibutuhkan'],
      poinAnda: _parseInt(json['poinAnda']) ?? 0,
      kekurangan: json['kekurangan'],
    );
  }
}

int? _parseInt(dynamic value) {
  if (value == null) return null;
  if (value is int) return value;
  if (value is double) return value.toInt();
  if (value is String) return int.tryParse(value);
  return null;
}
