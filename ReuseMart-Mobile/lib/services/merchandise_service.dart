// lib/services/merchandise_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_constants.dart';
import '../models/merchandise.dart';

class MerchandiseService {
  static Future<String?> _getAuthToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  // Mendapatkan katalog merchandise
  static Future<ApiResponse<List<Merchandise>>> getKatalogMerchandise() async {
    try {
      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/merchandise/katalog'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      final Map<String, dynamic> jsonData = json.decode(response.body);

      if (response.statusCode == 200) {
        if (jsonData['success'] == true) {
          List<dynamic> dataList = jsonData['data'] ?? [];
          List<Merchandise> merchandiseList = dataList
              .map((item) => Merchandise.fromJson(item))
              .toList();

          return ApiResponse<List<Merchandise>>(
            success: true,
            message: jsonData['message'] ?? 'Berhasil',
            data: merchandiseList,
          );
        } else {
          return ApiResponse<List<Merchandise>>(
            success: false,
            message: jsonData['message'] ?? 'Gagal memuat katalog',
          );
        }
      } else {
        return ApiResponse<List<Merchandise>>(
          success: false,
          message: 'HTTP Error: ${response.statusCode}',
        );
      }
    } catch (e) {
      return ApiResponse<List<Merchandise>>(
        success: false,
        message: 'Koneksi bermasalah: $e',
      );
    }
  }

  // Melakukan klaim merchandise
  static Future<ApiResponse<KlaimResponse>> klaimMerchandise(
    int idMerchandise,
  ) async {
    try {
      final token = await _getAuthToken();
      if (token == null) {
        return ApiResponse<KlaimResponse>(
          success: false,
          message: 'Silakan login terlebih dahulu',
        );
      }

      final request = KlaimRequest(idMerchandise: idMerchandise);

      final response = await http.post(
        Uri.parse('${AppConstants.apiUrl}/pembeli/merchandise/klaim'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode(request.toJson()),
      );

      final Map<String, dynamic> jsonData = json.decode(response.body);

      if (response.statusCode == 201) {
        // Klaim berhasil
        if (jsonData['success'] == true) {
          KlaimResponse klaimResponse = KlaimResponse.fromJson(
            jsonData['data'],
          );
          return ApiResponse<KlaimResponse>(
            success: true,
            message: jsonData['message'] ?? 'Klaim berhasil',
            data: klaimResponse,
          );
        } else {
          return ApiResponse<KlaimResponse>(
            success: false,
            message: jsonData['message'] ?? 'Gagal melakukan klaim',
          );
        }
      } else if (response.statusCode == 400) {
        // Error: poin tidak cukup atau stok habis
        return ApiResponse<KlaimResponse>(
          success: false,
          message: jsonData['message'] ?? 'Klaim gagal',
          error: jsonData['error'],
        );
      } else if (response.statusCode == 401) {
        // Unauthorized
        return ApiResponse<KlaimResponse>(
          success: false,
          message: 'Sesi Anda telah berakhir. Silakan login kembali.',
        );
      } else {
        return ApiResponse<KlaimResponse>(
          success: false,
          message: 'HTTP Error: ${response.statusCode}',
        );
      }
    } catch (e) {
      return ApiResponse<KlaimResponse>(
        success: false,
        message: 'Koneksi bermasalah: $e',
      );
    }
  }

  // Mendapatkan history klaim
  static Future<ApiResponse<List<HistoryKlaim>>> getHistoryKlaim() async {
    try {
      final token = await _getAuthToken();
      if (token == null) {
        return ApiResponse<List<HistoryKlaim>>(
          success: false,
          message: 'Silakan login terlebih dahulu',
        );
      }

      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/pembeli/merchandise/history'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final Map<String, dynamic> jsonData = json.decode(response.body);

      if (response.statusCode == 200) {
        if (jsonData['success'] == true) {
          List<dynamic> dataList = jsonData['data'] ?? [];
          List<HistoryKlaim> historyList = dataList
              .map((item) => HistoryKlaim.fromJson(item))
              .toList();

          return ApiResponse<List<HistoryKlaim>>(
            success: true,
            message: jsonData['message'] ?? 'Berhasil',
            data: historyList,
          );
        } else {
          return ApiResponse<List<HistoryKlaim>>(
            success: false,
            message: jsonData['message'] ?? 'Gagal memuat history',
          );
        }
      } else if (response.statusCode == 401) {
        return ApiResponse<List<HistoryKlaim>>(
          success: false,
          message: 'Sesi Anda telah berakhir. Silakan login kembali.',
        );
      } else {
        return ApiResponse<List<HistoryKlaim>>(
          success: false,
          message: 'HTTP Error: ${response.statusCode}',
        );
      }
    } catch (e) {
      return ApiResponse<List<HistoryKlaim>>(
        success: false,
        message: 'Koneksi bermasalah: $e',
      );
    }
  }

  // Mendapatkan detail klaim
  static Future<ApiResponse<DetailKlaim>> getDetailKlaim(
    int idPenukaran,
  ) async {
    try {
      final token = await _getAuthToken();
      if (token == null) {
        return ApiResponse<DetailKlaim>(
          success: false,
          message: 'Silakan login terlebih dahulu',
        );
      }

      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/merchandise/klaim/$idPenukaran'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final Map<String, dynamic> jsonData = json.decode(response.body);

      if (response.statusCode == 200) {
        if (jsonData['success'] == true) {
          DetailKlaim detailKlaim = DetailKlaim.fromJson(jsonData['data']);
          return ApiResponse<DetailKlaim>(
            success: true,
            message: jsonData['message'] ?? 'Berhasil',
            data: detailKlaim,
          );
        } else {
          return ApiResponse<DetailKlaim>(
            success: false,
            message: jsonData['message'] ?? 'Gagal memuat detail',
          );
        }
      } else if (response.statusCode == 404) {
        return ApiResponse<DetailKlaim>(
          success: false,
          message: 'Data klaim tidak ditemukan',
        );
      } else if (response.statusCode == 401) {
        return ApiResponse<DetailKlaim>(
          success: false,
          message: 'Sesi Anda telah berakhir. Silakan login kembali.',
        );
      } else {
        return ApiResponse<DetailKlaim>(
          success: false,
          message: 'HTTP Error: ${response.statusCode}',
        );
      }
    } catch (e) {
      return ApiResponse<DetailKlaim>(
        success: false,
        message: 'Koneksi bermasalah: $e',
      );
    }
  }

  // Mendapatkan poin pembeli
  static Future<ApiResponse<PoinData>> getPoinPembeli() async {
    try {
      final token = await _getAuthToken();
      if (token == null) {
        return ApiResponse<PoinData>(
          success: false,
          message: 'Silakan login terlebih dahulu',
        );
      }

      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/pembeli/poin'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final Map<String, dynamic> jsonData = json.decode(response.body);

      if (response.statusCode == 200) {
        if (jsonData['success'] == true) {
          PoinData poinData = PoinData.fromJson(jsonData['data']);
          return ApiResponse<PoinData>(
            success: true,
            message: jsonData['message'] ?? 'Berhasil',
            data: poinData,
          );
        } else {
          return ApiResponse<PoinData>(
            success: false,
            message: jsonData['message'] ?? 'Gagal memuat data poin',
          );
        }
      } else if (response.statusCode == 401) {
        return ApiResponse<PoinData>(
          success: false,
          message: 'Sesi Anda telah berakhir. Silakan login kembali.',
        );
      } else {
        return ApiResponse<PoinData>(
          success: false,
          message: 'HTTP Error: ${response.statusCode}',
        );
      }
    } catch (e) {
      return ApiResponse<PoinData>(
        success: false,
        message: 'Koneksi bermasalah: $e',
      );
    }
  }
}
