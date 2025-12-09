import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_constants.dart'; // ✅ Import AppConstants
import '../models/pembeli_profile.dart';
import '../models/transaksi_history.dart';

class PembeliService {
  // ✅ HAPUS baseUrl, gunakan AppConstants.apiUrl langsung

  // Get auth token from SharedPreferences
  static Future<String?> _getAuthToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      // ✅ PERBAIKAN: Ganti 'auth_token' jadi 'token'
      final token = prefs.getString('token'); // ← Sama dengan ApiService
      print('🔍 Raw token from SharedPreferences: $token');
      return token;
    } catch (e) {
      print('❌ Error getting token: $e');
      return null;
    }
  }

  // Get common headers for API requests
  static Future<Map<String, String>> _getHeaders() async {
    try {
      final token = await _getAuthToken();

      final headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

      print('🔍 Final headers: $headers');
      return headers;
    } catch (e) {
      print('❌ Error creating headers: $e');
      return {'Content-Type': 'application/json', 'Accept': 'application/json'};
    }
  }

  // Handle API response and check for errors
  static Map<String, dynamic> _handleResponse(http.Response response) {
    print('📊 Response Status: ${response.statusCode}'); // Debug
    print('📄 Response Body: ${response.body}'); // Debug

    try {
      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return data;
      } else {
        throw Exception(data['message'] ?? 'Terjadi kesalahan');
      }
    } catch (e) {
      print('❌ JSON Decode Error: $e');
      print('❌ Raw Response: ${response.body}');
      throw Exception('Invalid response format: $e');
    }
  }

  // Get Profile Pembeli
  static Future<PembeliProfile> getProfile() async {
    try {
      final headers = await _getHeaders();

      // ✅ Gunakan AppConstants.apiUrl
      final url = '${AppConstants.apiUrl}/pembeli/profile';
      print('🔗 Profile URL: $url'); // Debug

      final response = await http.get(Uri.parse(url), headers: headers);

      final data = _handleResponse(response);
      return PembeliProfile.fromJson(data['data']);
    } catch (e) {
      print('💥 Profile Error: $e');
      throw Exception('Gagal mengambil profil: $e');
    }
  }

  // Get History Transaksi
  static Future<Map<String, dynamic>> getHistoryTransaksi({
    String? tanggalLunasMulai,
    String? tanggalLunasSelesai,
    int? limit,
  }) async {
    try {
      final token = await _getAuthToken();
      print('🔑 Token from storage: $token');

      if (token == null || token.isEmpty) {
        print('❌ No token found! User might not be logged in');
        throw Exception('No authentication token found');
      }
      final headers = await _getHeaders();
      print('📋 Headers being sent: $headers');

      // Build query parameters
      final queryParams = <String, String>{};
      if (tanggalLunasMulai != null)
        queryParams['tanggal_lunas_mulai'] = tanggalLunasMulai;
      if (tanggalLunasSelesai != null)
        queryParams['tanggal_lunas_selesai'] = tanggalLunasSelesai;
      if (limit != null) queryParams['limit'] = limit.toString();

      // ✅ PERBAIKAN: Gunakan AppConstants.apiUrl, HAPUS /api extra
      final uri = Uri.parse(
        '${AppConstants.apiUrl}/pembeli/history-transaksi', // ✅ Sudah benar
      ).replace(queryParameters: queryParams.isEmpty ? null : queryParams);

      print(
        '🔗 History URL: $uri',
      ); // Debug - akan print: http://10.0.2.2:8000/api/pembeli/history-transaksi

      final response = await http.get(uri, headers: headers);
      final data = _handleResponse(response);

      return {
        'summary': HistorySummary.fromJson(data['summary']),
        'transactions': (data['data'] as List)
            .map((item) => TransaksiHistory.fromJson(item))
            .toList(),
      };
    } catch (e) {
      print('💥 History Error: $e');
      throw Exception('Gagal mengambil history transaksi: $e');
    }
  }

  static Future<void> debugUserInfo() async {
    try {
      final headers = await _getHeaders();

      // Test endpoint yang tidak memerlukan role khusus dulu
      final response = await http.get(
        Uri.parse(
          '${AppConstants.apiUrl}/user',
        ), // atau endpoint untuk get current user
        headers: headers,
      );

      print('🔍 Current User Info: ${response.body}');
    } catch (e) {
      print('❌ Debug User Error: $e');
    }
  }
}
