import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_constants.dart';
import '../models/hunter_profile.dart';
import '../models/history_komisi.dart';

class HunterService {
  // Get auth token from SharedPreferences
  static Future<String?> _getAuthToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
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
    print('📊 Response Status: ${response.statusCode}');
    print('📄 Response Body: ${response.body}');

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

  // Get Hunter Profile
  static Future<HunterProfile> getProfile() async {
    try {
      final headers = await _getHeaders();

      final url = '${AppConstants.apiUrl}/hunter/profile';
      print('🔗 Hunter Profile URL: $url');

      final response = await http.get(Uri.parse(url), headers: headers);

      final data = _handleResponse(response);
      return HunterProfile.fromJson(data['data']);
    } catch (e) {
      print('💥 Hunter Profile Error: $e');
      throw Exception('Gagal mengambil profil hunter: $e');
    }
  }

  // Get Hunter History Komisi (All)
  static Future<Map<String, dynamic>> getHistoryKomisi() async {
    try {
      final token = await _getAuthToken();
      print('🔑 Token from storage: $token');

      if (token == null || token.isEmpty) {
        print('❌ No token found! User might not be logged in');
        throw Exception('No authentication token found');
      }

      final headers = await _getHeaders();
      print('📋 Headers being sent: $headers');

      final uri = Uri.parse('${AppConstants.apiUrl}/hunter/history-komisi');

      print('🔗 History Komisi URL: $uri');

      final response = await http.get(uri, headers: headers);
      final data = _handleResponse(response);

      return {
        'summary': KomisiSummary.fromJson(data['summary']),
        'historyKomisi': (data['data'] as List)
            .map((item) => HistoryKomisi.fromJson(item))
            .toList(),
      };
    } catch (e) {
      print('💥 History Komisi Error: $e');
      throw Exception('Gagal mengambil history komisi: $e');
    }
  }

  // Get Hunter Stats
  static Future<HunterStats> getStats() async {
    try {
      final headers = await _getHeaders();

      final url = '${AppConstants.apiUrl}/hunter/stats';
      print('🔗 Hunter Stats URL: $url');

      final response = await http.get(Uri.parse(url), headers: headers);

      final data = _handleResponse(response);
      return HunterStats.fromJson(data['data']);
    } catch (e) {
      print('💥 Hunter Stats Error: $e');
      throw Exception('Gagal mengambil stats hunter: $e');
    }
  }

  static Future<void> debugHunterInfo() async {
    try {
      final headers = await _getHeaders();

      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/user'),
        headers: headers,
      );

      print('🔍 Current Hunter Info: ${response.body}');
    } catch (e) {
      print('❌ Debug Hunter Error: $e');
    }
  }
}
