import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_constants.dart';
import '../models/penitip_profile.dart';
import '../models/history_transaksi_penitip.dart'; // Updated models

class PenitipService {
  // ✅ FIXED: Get auth token from SharedPreferences
  static Future<String?> _getAuthToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      // ✅ NOTE: Pastikan key ini sesuai dengan yang digunakan di auth service
      final token = prefs.getString('auth_token') ?? prefs.getString('token');
      print('🔍 Penitip Token from storage: $token');
      return token;
    } catch (e) {
      print('❌ Error getting token: $e');
      return null;
    }
  }

  // Get common headers for API requests
  static Future<Map<String, String>> _getHeaders() async {
    final token = await _getAuthToken();

    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };

    print('📋 Penitip Headers: $headers');
    return headers;
  }

  // ✅ ENHANCED: Handle API response and check for errors
  static Map<String, dynamic> _handleResponse(http.Response response) {
    print('📊 Penitip Response Status: ${response.statusCode}');
    print('📄 Penitip Response Body: ${response.body}');

    try {
      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return data;
      } else if (response.statusCode == 401) {
        throw Exception('Sesi telah berakhir, silakan login kembali');
      } else if (response.statusCode == 403) {
        throw Exception('Anda tidak memiliki akses ke data ini');
      } else if (response.statusCode == 404) {
        throw Exception('Data tidak ditemukan');
      } else if (response.statusCode == 422) {
        // Handle validation errors
        final errors = data['errors'];
        if (errors != null && errors.isNotEmpty) {
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            throw Exception(firstError.first);
          }
        }
        throw Exception(data['message'] ?? 'Data tidak valid');
      } else {
        throw Exception(data['message'] ?? 'Terjadi kesalahan server');
      }
    } catch (e) {
      if (e.toString().contains('Exception:')) {
        rethrow; // Re-throw our custom exceptions
      }
      print('❌ Penitip JSON Decode Error: $e');
      print('❌ Penitip Raw Response: ${response.body}');
      throw Exception('Invalid response format: $e');
    }
  }

  // Get Profile Penitip (tanpa recent transactions)
  static Future<Map<String, dynamic>> getProfile() async {
    try {
      final headers = await _getHeaders();

      final url = '${AppConstants.apiUrl}/penitip/profile';
      print('🔗 Penitip Profile URL: $url');

      final response = await http.get(Uri.parse(url), headers: headers);

      final data = _handleResponse(response);

      print('🎯 Profile Raw Data: ${data['data']['profile']}');

      PenitipProfile profile;

      try {
        profile = PenitipProfile.fromJson(data['data']['profile']);
        print('✅ Profile parsed successfully');
      } catch (e) {
        print('❌ Error parsing profile: $e');
        throw Exception('Gagal parsing data profil: $e');
      }

      return {'profile': profile};
    } catch (e) {
      print('💥 Penitip Profile Error: $e');
      rethrow;
    }
  }

  // ✅ FIXED: Get History Transaksi Penitip dengan endpoint yang benar
  static Future<Map<String, dynamic>> getHistoryTransaksi({
    String? startDate,
    String? endDate,
    int page = 1,
    int perPage = 10,
  }) async {
    try {
      final headers = await _getHeaders();

      // Build query parameters
      final queryParams = <String, String>{
        'page': page.toString(),
        'per_page': perPage.toString(),
        if (startDate != null && startDate.isNotEmpty) 'start_date': startDate,
        if (endDate != null && endDate.isNotEmpty) 'end_date': endDate,
      };

      // ✅ FIXED: Menggunakan endpoint yang benar sesuai controller
      final uri = Uri.parse(
        '${AppConstants.apiUrl}/penitip/history-transaksi',
      ).replace(queryParameters: queryParams);

      print('🔗 Penitip History URL: $uri');
      print('📋 Query Parameters: $queryParams');

      final response = await http.get(uri, headers: headers);

      final data = _handleResponse(response);

      // ✅ Enhanced parsing dengan safe parsing
      List<HistoryTransaksiPenitip> transactions = [];
      PaginationInfo? pagination;
      FilterInfo? filter;

      try {
        // Parse transactions dengan safe parsing
        final transactionsData = data['data']['transactions'] as List?;
        if (transactionsData != null) {
          transactions = transactionsData
              .map((item) => HistoryTransaksiPenitip.fromJson(item))
              .toList();
        }

        // Parse pagination
        final paginationData = data['data']['pagination'];
        if (paginationData != null) {
          pagination = PaginationInfo.fromJson(paginationData);
        }

        // Parse filter info
        final filterData = data['data']['filter'];
        if (filterData != null) {
          filter = FilterInfo.fromJson(filterData);
        }

        print('✅ Parsed ${transactions.length} transactions');
        print('📄 Pagination: ${pagination?.total} total items');
        print('📅 Filter period: ${filter?.displayPeriod}');
      } catch (e) {
        print('❌ Error parsing history data: $e');
        print('🔍 Raw data structure: ${data['data']}');
        throw Exception('Gagal parsing data history: $e');
      }

      return {
        'transactions': transactions,
        'pagination': pagination,
        'filter': filter,
      };
    } catch (e) {
      print('💥 Penitip History Error: $e');
      rethrow;
    }
  }

  // ✅ FIXED: Get Detail Transaksi dengan endpoint yang benar
  static Future<DetailTransaksiPenitip> getDetailTransaksi(
    int idTransaksiPenjualan,
  ) async {
    try {
      final headers = await _getHeaders();

      // ✅ FIXED: Menggunakan endpoint yang benar sesuai controller
      final url =
          '${AppConstants.apiUrl}/penitip/transaksi/$idTransaksiPenjualan';
      print('🔗 Penitip Detail URL: $url');

      final response = await http.get(Uri.parse(url), headers: headers);

      final data = _handleResponse(response);

      // ✅ Parse dengan DetailTransaksiPenitip model yang sudah diperbaiki
      try {
        final detailTransaksi = DetailTransaksiPenitip.fromJson(data['data']);
        print('✅ Detail transaksi parsed successfully');
        return detailTransaksi;
      } catch (e) {
        print('❌ Error parsing detail transaksi: $e');
        print('🔍 Raw detail data: ${data['data']}');
        throw Exception('Gagal parsing detail transaksi: $e');
      }
    } catch (e) {
      print('💥 Penitip Detail Error: $e');
      rethrow;
    }
  }

  // ✅ OPTIONAL: Method untuk mendapatkan statistik cepat
  static Future<Map<String, dynamic>> getStatistik() async {
    try {
      final headers = await _getHeaders();

      final url = '${AppConstants.apiUrl}/penitip/statistik';
      print('🔗 Penitip Statistik URL: $url');

      final response = await http.get(Uri.parse(url), headers: headers);

      // ✅ Handle case dimana endpoint statistik mungkin belum ada
      if (response.statusCode == 404) {
        print('ℹ️ Statistik endpoint not available, returning defaults');
        return {
          'total_barang_aktif': 0,
          'total_terjual': 0,
          'total_komisi': 0.0,
          'rata_rata_komisi': 0.0,
        };
      }

      final data = _handleResponse(response);

      return {
        'total_barang_aktif': data['data']['total_barang_aktif'] ?? 0,
        'total_terjual': data['data']['total_terjual'] ?? 0,
        'total_komisi': data['data']['total_komisi'] ?? 0.0,
        'rata_rata_komisi': data['data']['rata_rata_komisi'] ?? 0.0,
      };
    } catch (e) {
      print('💥 Penitip Statistik Error: $e');
      // Return default values jika gagal
      return {
        'total_barang_aktif': 0,
        'total_terjual': 0,
        'total_komisi': 0.0,
        'rata_rata_komisi': 0.0,
      };
    }
  }

  // ✅ ENHANCED: Method untuk quick filter dengan validasi
  static Map<String, String> getQuickFilterDates(String filterType) {
    final now = DateTime.now();

    try {
      switch (filterType) {
        case 'bulan_ini':
          final startOfMonth = DateTime(now.year, now.month, 1);
          final endOfMonth = DateTime(now.year, now.month + 1, 0);
          return {
            'start_date': _formatDateForApi(startOfMonth),
            'end_date': _formatDateForApi(endOfMonth),
          };

        case '3_bulan':
          final threeMonthsAgo = DateTime(now.year, now.month - 3, now.day);
          return {
            'start_date': _formatDateForApi(threeMonthsAgo),
            'end_date': _formatDateForApi(now),
          };

        case '6_bulan':
          final sixMonthsAgo = DateTime(now.year, now.month - 6, now.day);
          return {
            'start_date': _formatDateForApi(sixMonthsAgo),
            'end_date': _formatDateForApi(now),
          };

        case 'tahun_ini':
          final startOfYear = DateTime(now.year, 1, 1);
          final endOfYear = DateTime(now.year, 12, 31);
          return {
            'start_date': _formatDateForApi(startOfYear),
            'end_date': _formatDateForApi(endOfYear),
          };

        case 'tahun_lalu':
          final startOfLastYear = DateTime(now.year - 1, 1, 1);
          final endOfLastYear = DateTime(now.year - 1, 12, 31);
          return {
            'start_date': _formatDateForApi(startOfLastYear),
            'end_date': _formatDateForApi(endOfLastYear),
          };

        default:
          // Default: 3 bulan terakhir
          final threeMonthsAgo = DateTime(now.year, now.month - 3, now.day);
          return {
            'start_date': _formatDateForApi(threeMonthsAgo),
            'end_date': _formatDateForApi(now),
          };
      }
    } catch (e) {
      print('❌ Error generating filter dates: $e');
      // Fallback: 1 bulan terakhir
      final oneMonthAgo = DateTime(now.year, now.month - 1, now.day);
      return {
        'start_date': _formatDateForApi(oneMonthAgo),
        'end_date': _formatDateForApi(now),
      };
    }
  }

  // ✅ Helper method untuk format tanggal ke API
  static String _formatDateForApi(DateTime date) {
    return '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
  }

  // ✅ Helper method untuk format tanggal display
  static String formatDisplayDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
    } catch (e) {
      return dateStr;
    }
  }

  // ✅ Helper method untuk format date time lengkap
  static String formatDateTime(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year} ${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
    } catch (e) {
      return dateStr;
    }
  }

  // ✅ ENHANCED: Helper method untuk format currency Indonesia
  static String formatCurrency(double amount) {
    try {
      return amount
          .toStringAsFixed(0)
          .replaceAllMapped(
            RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
            (Match m) => '${m[1]}.',
          );
    } catch (e) {
      return amount.toString();
    }
  }

  // ✅ NEW: Helper method untuk validasi tanggal
  static bool isValidDateRange(String? startDate, String? endDate) {
    if (startDate == null || endDate == null) return true;

    try {
      final start = DateTime.parse(startDate);
      final end = DateTime.parse(endDate);
      return start.isBefore(end) || start.isAtSameMomentAs(end);
    } catch (e) {
      return false;
    }
  }

  // ✅ NEW: Helper method untuk generate filter label
  static String getFilterLabel(String? startDate, String? endDate) {
    if (startDate == null || endDate == null) {
      return 'Semua Periode';
    }

    try {
      final start = formatDisplayDate(startDate);
      final end = formatDisplayDate(endDate);
      return '$start - $end';
    } catch (e) {
      return 'Periode Tidak Valid';
    }
  }

  // ✅ NEW: Helper method untuk cek apakah token valid
  static Future<bool> isTokenValid() async {
    try {
      final token = await _getAuthToken();
      return token != null && token.isNotEmpty;
    } catch (e) {
      return false;
    }
  }
}
