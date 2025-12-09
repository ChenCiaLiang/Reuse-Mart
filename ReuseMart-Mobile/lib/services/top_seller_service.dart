import 'dart:convert';
import 'package:http/http.dart' as http;
import '../constants/app_constants.dart';
import '../models/top_seller.dart';

class TopSellerService {
  static const String baseUrl = AppConstants.baseUrl; // Gunakan AppConstants

  static Future<TopSeller?> getCurrentTopSeller() async {
    try {
      final url = '${AppConstants.apiUrl}/top-seller/current';
      print('🔍 API URL: $url');

      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      print('📡 Response Status: ${response.statusCode}');
      print('📡 Response Body: ${response.body}');

      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = json.decode(response.body);

        if (responseData['success'] == true && responseData['data'] != null) {
          try {
            return TopSeller.fromJson(responseData['data']);
          } catch (parseError) {
            print('💥 Parse Error: $parseError');
            print('🔍 Raw Data: ${responseData['data']}');
            return null;
          }
        }
        return null;
      } else {
        throw Exception('HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      print('💥 Error fetching top seller: $e');
      return null;
    }
  }

  static Future<Map<String, dynamic>?> getTopSellerStats() async {
    try {
      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/top-seller/stats'), // Gunakan apiUrl
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = json.decode(response.body);

        if (responseData['success'] == true) {
          return responseData['data'];
        }
        return null;
      } else {
        throw Exception(
          'Failed to load top seller stats: ${response.statusCode}',
        );
      }
    } catch (e) {
      print('Error fetching top seller stats: $e');
      return null;
    }
  }

  static Future<bool> checkTopSellerStatus(int penitipId, String token) async {
    try {
      final response = await http.get(
        Uri.parse(
          '${AppConstants.apiUrl}/top-seller/check/$penitipId',
        ), // Gunakan apiUrl
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = json.decode(response.body);
        return responseData['data']['is_top_seller'] ?? false;
      }
      return false;
    } catch (e) {
      print('Error checking top seller status: $e');
      return false;
    }
  }
}
