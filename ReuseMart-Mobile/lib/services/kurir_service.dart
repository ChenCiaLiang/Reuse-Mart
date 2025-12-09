import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_constants.dart';
import '../models/kurir.dart';

class KurirService {
  static Future<String?> _getAuthToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  /// Helper method untuk mendapatkan headers dengan token
  static Future<Map<String, String>> _getHeaders() async {
    final token = await _getAuthToken();
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token != null) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  /// Fungsi 117: Menampilkan profil diri sendiri (Kurir)
  static Future<KurirProfile> getKurirProfile() async {
    try {
      final headers = await _getHeaders();
      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/kurir/profile'),
        headers: headers,
      );

      print('Kurir Profile Response: ${response.statusCode}');
      print('Response body: ${response.body}');

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);

        if (data['success'] == true && data.containsKey('data')) {
          return KurirProfile.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load kurir profile');
        }
      } else if (response.statusCode == 403) {
        throw Exception('Unauthorized. Kurir access required.');
      } else {
        final errorData = json.decode(response.body);
        throw Exception(errorData['message'] ?? 'Error loading kurir profile');
      }
    } catch (e) {
      print('Error in getKurirProfile: $e');
      throw Exception('Error fetching kurir profile: $e');
    }
  }

  /// Fungsi 118: Menampilkan history tugas pengiriman (Kurir)
  static Future<List<TugasPengiriman>> getHistoryTugasPengiriman({
    String? tanggalMulai,
    String? tanggalSelesai,
    String? status,
    int limit = 50,
  }) async {
    try {
      final Map<String, String> queryParams = {
        'limit': limit.toString(),
      };

      if (tanggalMulai != null) {
        queryParams['tanggal_mulai'] = tanggalMulai;
      }
      if (tanggalSelesai != null) {
        queryParams['tanggal_selesai'] = tanggalSelesai;
      }
      if (status != null) {
        queryParams['status'] = status;
      }

      final uri = Uri.parse('${AppConstants.apiUrl}/kurir/history-pengiriman')
          .replace(queryParameters: queryParams);

      final headers = await _getHeaders();
      final response = await http.get(
        uri,
        headers: headers,
      );

      print('History Tugas Response: ${response.statusCode}');
      print('Response body: ${response.body}');

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);

        if (data['success'] == true && data.containsKey('data')) {
          final List<dynamic> tugasData = data['data'];
          return tugasData
              .map((item) => TugasPengiriman.fromJson(item))
              .toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load history tugas');
        }
      } else if (response.statusCode == 403) {
        throw Exception('Unauthorized. Kurir access required.');
      } else {
        final errorData = json.decode(response.body);
        throw Exception(errorData['message'] ?? 'Error loading history tugas');
      }
    } catch (e) {
      print('Error in getHistoryTugasPengiriman: $e');
      throw Exception('Error fetching history tugas pengiriman: $e');
    }
  }

  /// Fungsi 119: Mengupdate status pengiriman menjadi "Selesai" (Kurir)
  static Future<bool> updateStatusPengirimanSelesai(
      int idTransaksiPenjualan) async {
    try {
      final headers = await _getHeaders();
      final response = await http.post(
        Uri.parse('${AppConstants.apiUrl}/kurir/update-status-selesai'),
        headers: headers,
        body: json.encode({
          'idTransaksiPenjualan': idTransaksiPenjualan,
        }),
      );

      print('Update Status Response: ${response.statusCode}');
      print('Response body: ${response.body}');

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        return data['success'] == true;
      } else if (response.statusCode == 403) {
        throw Exception('Unauthorized. Kurir access required.');
      } else if (response.statusCode == 404) {
        throw Exception('Transaksi pengiriman tidak ditemukan.');
      } else {
        final errorData = json.decode(response.body);
        throw Exception(
            errorData['message'] ?? 'Error updating status pengiriman');
      }
    } catch (e) {
      print('Error in updateStatusPengirimanSelesai: $e');
      throw Exception('Error updating status pengiriman: $e');
    }
  }

  /// Mendapatkan statistik kurir
  static Future<KurirStats> getKurirStats() async {
    try {
      final headers = await _getHeaders();
      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/kurir/stats'),
        headers: headers,
      );

      print('Kurir Stats Response: ${response.statusCode}');
      print('Response body: ${response.body}');

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);

        if (data['success'] == true && data.containsKey('data')) {
          return KurirStats.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load kurir stats');
        }
      } else {
        final errorData = json.decode(response.body);
        throw Exception(errorData['message'] ?? 'Error loading kurir stats');
      }
    } catch (e) {
      print('Error in getKurirStats: $e');
      throw Exception('Error fetching kurir stats: $e');
    }
  }

  /// Mendapatkan tugas pengiriman aktif hari ini
  static Future<List<TugasPengiriman>> getTugasHariIni() async {
    try {
      final headers = await _getHeaders();
      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/kurir/tugas-hari-ini'),
        headers: headers,
      );

      print('Tugas Hari Ini Response: ${response.statusCode}');
      print('Response body: ${response.body}');

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);

        if (data['success'] == true && data.containsKey('data')) {
          final List<dynamic> tugasData = data['data'];
          return tugasData
              .map((item) => TugasPengiriman.fromJson(item))
              .toList();
        } else {
          return []; // Return empty list if no tasks today
        }
      } else {
        final errorData = json.decode(response.body);
        throw Exception(errorData['message'] ?? 'Error loading tugas hari ini');
      }
    } catch (e) {
      print('Error in getTugasHariIni: $e');
      throw Exception('Error fetching tugas hari ini: $e');
    }
  }
}
