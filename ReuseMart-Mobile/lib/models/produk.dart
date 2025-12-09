class ProdukModel {
  final int idProduk;
  final String deskripsi;
  final double hargaJual;
  final double harga;
  final String thumbnail;
  final String kategori;
  final int idKategori;
  final String status;
  final double rating;
  final double berat;
  final GaransiModel garansi;
  final String createdAt;

  ProdukModel({
    required this.idProduk,
    required this.deskripsi,
    required this.hargaJual,
    required this.harga,
    required this.thumbnail,
    required this.kategori,
    required this.idKategori,
    required this.status,
    required this.rating,
    required this.berat,
    required this.garansi,
    required this.createdAt,
  });

  factory ProdukModel.fromJson(Map<String, dynamic> json) {
    try {
      print('Parsing ProdukModel with JSON: $json'); // Debug log

      // Parse garansi dengan lebih hati-hati
      Map<String, dynamic> garansiData = {};
      if (json['garansi'] != null) {
        if (json['garansi'] is Map<String, dynamic>) {
          garansiData = json['garansi'];
        } else if (json['garansi'] is String) {
          // Jika garansi berupa string, buat map sederhana
          garansiData = {
            'status': json['garansi'],
            'keterangan': json['garansi'],
          };
        }
      } else if (json['statusGaransi'] != null) {
        if (json['statusGaransi'] is String) {
          garansiData = {
            'status': json['statusGaransi'],
            'keterangan': json['statusGaransi'],
            'tanggal': json['tanggalGaransi'],
          };
        }
      }

      return ProdukModel(
        idProduk: _parseInt(json['idProduk']),
        deskripsi: _parseString(json['deskripsi']),
        hargaJual: _parseDouble(json['hargaJual']),
        harga: _parseDouble(json['harga']),
        thumbnail: _parseString(json['thumbnailFoto'] ?? json['thumbnail']),
        kategori: _parseString(json['kategori']),
        idKategori: _parseInt(json['idKategori']),
        status: _parseString(json['status']),
        rating: _parseDouble(json['ratingProduk'] ?? json['rating']),
        berat: _parseDouble(json['berat']),
        garansi: GaransiModel.fromJson(garansiData),
        createdAt: _parseString(json['created_at'] ?? json['createdAt']),
      );
    } catch (e) {
      print('Error parsing ProdukModel: $e');
      print('JSON data: $json');
      rethrow;
    }
  }

  // Helper methods untuk parsing yang aman
  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  static String _parseString(dynamic value) {
    if (value == null) return '';
    return value.toString();
  }
}

class ProdukDetailModel {
  final int idProduk;
  final String deskripsi;
  final double hargaJual;
  final double harga;
  final List<String> gambar;
  final KategoriDetailModel kategori;
  final String status;
  final double rating;
  final double berat;
  final GaransiModel garansi;
  final PenitipModel? penitip;
  final List<ProdukModel> produkTerkait;
  final String createdAt;

  ProdukDetailModel({
    required this.idProduk,
    required this.deskripsi,
    required this.hargaJual,
    required this.harga,
    required this.gambar,
    required this.kategori,
    required this.status,
    required this.rating,
    required this.berat,
    required this.garansi,
    this.penitip,
    required this.produkTerkait,
    required this.createdAt,
  });

  factory ProdukDetailModel.fromJson(Map<String, dynamic> json) {
    try {
      print('Parsing ProdukDetailModel with JSON: $json'); // Debug log

      // Parse kategori dengan lebih hati-hati
      Map<String, dynamic> kategoriData = {};
      if (json['kategori'] != null) {
        if (json['kategori'] is Map<String, dynamic>) {
          kategoriData = json['kategori'];
        } else if (json['kategori'] is String) {
          kategoriData = {
            'id': json['idKategori'] ?? 0,
            'nama': json['kategori'],
          };
        }
      }

      // Parse garansi dengan lebih hati-hati
      Map<String, dynamic> garansiData = {};
      if (json['garansi'] != null) {
        if (json['garansi'] is Map<String, dynamic>) {
          garansiData = json['garansi'];
        } else if (json['garansi'] is String) {
          garansiData = {
            'status': json['garansi'],
            'keterangan': json['garansi'],
          };
        }
      }

      return ProdukDetailModel(
        idProduk: ProdukModel._parseInt(json['idProduk']),
        deskripsi: ProdukModel._parseString(json['deskripsi']),
        hargaJual: ProdukModel._parseDouble(json['hargaJual']),
        harga: ProdukModel._parseDouble(json['harga']),
        gambar: _parseImageList(json['fotoProduk'] ?? json['gambar']),
        kategori: KategoriDetailModel.fromJson(kategoriData),
        status: ProdukModel._parseString(json['status']),
        rating: ProdukModel._parseDouble(
          json['ratingProduk'] ?? json['rating'],
        ),
        berat: ProdukModel._parseDouble(json['berat']),
        garansi: GaransiModel.fromJson(garansiData),
        penitip:
            json['penitip'] != null && json['penitip'] is Map<String, dynamic>
            ? PenitipModel.fromJson(json['penitip'])
            : null,
        produkTerkait: _parseProdukList(json['produkTerkait'] ?? []),
        createdAt: ProdukModel._parseString(
          json['created_at'] ?? json['createdAt'],
        ),
      );
    } catch (e) {
      print('Error parsing ProdukDetailModel: $e');
      print('JSON data: $json');
      rethrow;
    }
  }

  static List<String> _parseImageList(dynamic value) {
    if (value == null) return [];
    if (value is List) {
      return value.map((item) => item.toString()).toList();
    }
    if (value is String) {
      return [value];
    }
    return [];
  }

  static List<ProdukModel> _parseProdukList(dynamic value) {
    if (value == null) return [];

    try {
      if (value is List) {
        return value
            .map((item) {
              try {
                if (item is Map<String, dynamic>) {
                  return ProdukModel.fromJson(item);
                } else {
                  print('Item produk bukan Map: $item');
                  return null;
                }
              } catch (e) {
                print('Error parsing produk terkait: $e');
                print('Item data: $item');
                return null;
              }
            })
            .where((item) => item != null)
            .cast<ProdukModel>()
            .toList();
      }
    } catch (e) {
      print('Error parsing produk list: $e');
    }

    return [];
  }
}

class GaransiModel {
  final String? tanggal;
  final String status;
  final String keterangan;

  GaransiModel({this.tanggal, required this.status, required this.keterangan});

  factory GaransiModel.fromJson(Map<String, dynamic> json) {
    // Handle different response formats
    String status = '';
    String keterangan = '';
    String? tanggal;

    if (json.containsKey('statusGaransi')) {
      // Format dari API list produk
      status = ProdukModel._parseString(json['statusGaransi']);
      keterangan = status;
      tanggal = json['tanggalGaransi']?.toString();
    } else if (json.containsKey('masihBerlaku')) {
      // Format dari API detail produk
      bool masihBerlaku = json['masihBerlaku'] ?? false;
      status = masihBerlaku ? 'Bergaransi' : 'Tidak Bergaransi';
      keterangan = status;
      tanggal = json['tanggalGaransi']?.toString();
    } else {
      // Format fallback
      status = ProdukModel._parseString(json['status']);
      keterangan = ProdukModel._parseString(json['keterangan']);
      tanggal = json['tanggal']?.toString();
    }

    // Normalisasi status
    if (status.toLowerCase().contains('bergaransi') &&
        !status.toLowerCase().contains('tidak')) {
      status = 'Bergaransi';
      keterangan = 'Bergaransi';
    } else if (status.toLowerCase().contains('habis')) {
      status = 'Garansi Habis';
      keterangan = 'Garansi Habis';
    } else {
      status = 'Tidak Bergaransi';
      keterangan = 'Tidak Bergaransi';
    }

    return GaransiModel(
      tanggal: tanggal,
      status: status,
      keterangan: keterangan,
    );
  }

  bool get isBergaransi => status == 'Bergaransi';
  bool get isGaransiHabis => status == 'Garansi Habis';
  bool get isTidakBergaransi => status == 'Tidak Bergaransi';
}

class KategoriModel {
  final int idKategori;
  final String nama;

  KategoriModel({required this.idKategori, required this.nama});

  factory KategoriModel.fromJson(Map<String, dynamic> json) {
    try {
      return KategoriModel(
        idKategori: ProdukModel._parseInt(json['idKategori']),
        nama: ProdukModel._parseString(json['nama']),
      );
    } catch (e) {
      print('Error parsing KategoriModel: $e');
      print('JSON data: $json');
      rethrow;
    }
  }
}

class KategoriDetailModel {
  final int id;
  final String nama;

  KategoriDetailModel({required this.id, required this.nama});

  factory KategoriDetailModel.fromJson(Map<String, dynamic> json) {
    try {
      print('Parsing KategoriDetailModel with JSON: $json'); // Debug log

      if (json.isEmpty) {
        return KategoriDetailModel(id: 0, nama: 'Tidak Diketahui');
      }

      return KategoriDetailModel(
        id: ProdukModel._parseInt(json['id'] ?? json['idKategori']),
        nama: ProdukModel._parseString(json['nama']),
      );
    } catch (e) {
      print('Error parsing KategoriDetailModel: $e');
      print('JSON data: $json');

      // Return default jika parsing gagal
      return KategoriDetailModel(id: 0, nama: 'Tidak Diketahui');
    }
  }
}

class PenitipModel {
  final String nama;
  final double rating;

  PenitipModel({required this.nama, required this.rating});

  factory PenitipModel.fromJson(Map<String, dynamic> json) {
    try {
      print('Parsing PenitipModel with JSON: $json'); // Debug log

      if (json.isEmpty) {
        return PenitipModel(nama: 'Tidak Diketahui', rating: 0.0);
      }

      return PenitipModel(
        nama: ProdukModel._parseString(json['nama']),
        rating: ProdukModel._parseDouble(json['rating']),
      );
    } catch (e) {
      print('Error parsing PenitipModel: $e');
      print('JSON data: $json');

      // Return default jika parsing gagal
      return PenitipModel(nama: 'Tidak Diketahui', rating: 0.0);
    }
  }
}

class PaginationModel {
  final int currentPage;
  final int totalPages;
  final int totalItems;
  final int itemsPerPage;
  final bool hasNext;
  final bool hasPrev;

  PaginationModel({
    required this.currentPage,
    required this.totalPages,
    required this.totalItems,
    required this.itemsPerPage,
    required this.hasNext,
    required this.hasPrev,
  });

  factory PaginationModel.fromJson(Map<String, dynamic> json) {
    try {
      print('Parsing PaginationModel with JSON: $json'); // Debug log

      return PaginationModel(
        currentPage: ProdukModel._parseInt(
          json['current_page'] ?? json['currentPage'],
        ),
        totalPages: ProdukModel._parseInt(
          json['total_pages'] ?? json['totalPages'],
        ),
        totalItems: ProdukModel._parseInt(
          json['total_items'] ?? json['totalItems'],
        ),
        itemsPerPage: ProdukModel._parseInt(
          json['items_per_page'] ?? json['itemsPerPage'],
        ),
        hasNext: _parseBool(json['has_next'] ?? json['hasNext']),
        hasPrev: _parseBool(json['has_prev'] ?? json['hasPrev']),
      );
    } catch (e) {
      print('Error parsing PaginationModel: $e');
      print('JSON data: $json');

      // Return default pagination jika parsing gagal
      return PaginationModel(
        currentPage: 1,
        totalPages: 1,
        totalItems: 0,
        itemsPerPage: 10,
        hasNext: false,
        hasPrev: false,
      );
    }
  }

  static bool _parseBool(dynamic value) {
    if (value == null) return false;
    if (value is bool) return value;
    if (value is String) return value.toLowerCase() == 'true';
    if (value is int) return value != 0;
    return false;
  }
}
