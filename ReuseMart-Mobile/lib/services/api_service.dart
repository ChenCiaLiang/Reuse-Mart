import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_constants.dart';
import '../models/user.dart';
import '../models/produk.dart';

class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final Map<String, dynamic>? errors;

  ApiResponse({
    required this.success,
    required this.message,
    this.data,
    this.errors,
  });
}

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  String? _token;

  // Headers untuk request
  Map<String, String> get _headers {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (_token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }

    return headers;
  }

  // Set token untuk authenticated requests
  void setToken(String token) {
    _token = token;
  }

  // Clear token
  void clearToken() {
    _token = null;
  }

  // Login function
  Future<ApiResponse<Map<String, dynamic>>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConstants.apiUrl}/login'),
        headers: _headers,
        body: jsonEncode({'email': email, 'password': password}),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        // Login berhasil
        final token = responseData['data']['token'];
        final userData = responseData['data']['user'];

        // Tentukan role berdasarkan response
        String role = 'pembeli'; // default

        // Cek tipe user berdasarkan field yang ada
        if (userData.containsKey('idPenitip')) {
          role = 'penitip';
        } else if (userData.containsKey('idOrganisasi')) {
          role = 'organisasi';
        } else if (userData.containsKey('idPegawai')) {
          final jabatanId = _parseInt(userData['idJabatan']);
          switch (jabatanId) {
            case 1:
              role = 'owner';
              break;
            case 2:
              role = 'admin';
              break;
            case 3:
              role = 'cs';
              break;
            case 4:
              role = 'gudang';
              break;
            case 5:
              role = 'hunter';
              break;
            case 6:
              role = 'kurir';
              break;
            default:
              role = 'pegawai';
          }
        }

        // Set token untuk request selanjutnya
        setToken(token);

        // Simpan token ke SharedPreferences
        await _saveToken(token);
        await _saveUserData(userData, role);

        return ApiResponse<Map<String, dynamic>>(
          success: true,
          message: responseData['message'] ?? 'Login berhasil',
          data: {'token': token, 'user': userData, 'role': role},
        );
      } else {
        // Login gagal
        return ApiResponse<Map<String, dynamic>>(
          success: false,
          message: responseData['message'] ?? 'Login gagal',
          errors: responseData['errors'],
        );
      }
    } catch (e) {
      return ApiResponse<Map<String, dynamic>>(
        success: false,
        message: 'Terjadi kesalahan: ${e.toString()}',
      );
    }
  }

  // Register pembeli
  Future<ApiResponse<Map<String, dynamic>>> registerPembeli({
    required String nama,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConstants.apiUrl}/register/pembeli'),
        headers: _headers,
        body: jsonEncode({
          'nama': nama,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
        }),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 201) {
        return ApiResponse<Map<String, dynamic>>(
          success: true,
          message: responseData['message'] ?? 'Registrasi berhasil',
          data: responseData['data'],
        );
      } else {
        return ApiResponse<Map<String, dynamic>>(
          success: false,
          message: responseData['message'] ?? 'Registrasi gagal',
          errors: responseData['errors'],
        );
      }
    } catch (e) {
      return ApiResponse<Map<String, dynamic>>(
        success: false,
        message: 'Terjadi kesalahan: ${e.toString()}',
      );
    }
  }

  // Logout
  Future<ApiResponse<void>> logout() async {
    try {
      final response = await http.post(
        Uri.parse('${AppConstants.apiUrl}/logout'),
        headers: _headers,
      );

      // Clear token dan data lokal
      clearToken();
      await _clearStoredData();

      if (response.statusCode == 200) {
        return ApiResponse<void>(success: true, message: 'Logout berhasil');
      } else {
        return ApiResponse<void>(success: true, message: 'Logout berhasil');
      }
    } catch (e) {
      // Clear data lokal meski terjadi error
      clearToken();
      await _clearStoredData();

      return ApiResponse<void>(success: true, message: 'Logout berhasil');
    }
  }

  // Check apakah user sudah login
  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    if (token != null) {
      setToken(token);
      return true;
    }

    return false;
  }

  // Get stored user data
  Future<User?> getStoredUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userDataString = prefs.getString('user_data');
    final userRole = prefs.getString('user_role');

    if (userDataString != null && userRole != null) {
      final userData = jsonDecode(userDataString);
      return User.fromJson(userData, userRole);
    }

    return null;
  }

  // Private methods untuk menyimpan data
  Future<void> _saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('token', token);
  }

  Future<void> _saveUserData(Map<String, dynamic> userData, String role) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('user_data', jsonEncode(userData));
    await prefs.setString('user_role', role);
  }

  Future<void> _clearStoredData() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('user_data');
    await prefs.remove('user_role');
  }

  // PERBAIKAN: Ubah dari static ke instance method dan gunakan proper headers
  Future<Map<String, dynamic>> getProduk({
    String? search,
    int? kategori,
    int page = 1,
    int limit = 10,
  }) async {
    try {
      final Map<String, String> queryParams = {
        'page': page.toString(),
        'limit': limit.toString(),
      };

      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
      }

      if (kategori != null) {
        queryParams['kategori'] = kategori.toString();
      }

      final uri = Uri.parse(
        '${AppConstants.apiUrl}/produk',
      ).replace(queryParameters: queryParams);

      print('Request URL: $uri'); // Debug log

      final response = await http.get(
        uri,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      print('Response status: ${response.statusCode}'); // Debug log
      print('Response body: ${response.body}'); // Debug log

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);

        // Validasi struktur response
        if (data['status'] == 'success' && data.containsKey('data')) {
          return {
            'success': true,
            'message': data['message'],
            'data': data['data'],
          };
        } else {
          throw Exception('Response format tidak sesuai: ${data.toString()}');
        }
      } else {
        final errorData = json.decode(response.body);
        throw Exception(
          'HTTP ${response.statusCode}: ${errorData['message'] ?? 'Unknown error'}',
        );
      }
    } catch (e) {
      print('Error in getProduk: $e'); // Debug log
      throw Exception('Error fetching products: $e');
    }
  }

  /// PERBAIKAN: Ubah dari static ke instance method
  Future<Map<String, dynamic>> getProdukDetail(int idProduk) async {
    try {
      final uri = Uri.parse('${AppConstants.apiUrl}/produk/$idProduk');

      print('Request URL: $uri'); // Debug log

      final response = await http.get(
        uri,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      print('Response status: ${response.statusCode}'); // Debug log
      print('Response body: ${response.body}'); // Debug log

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);

        if (data['status'] == 'success' && data.containsKey('data')) {
          return {
            'success': true,
            'message': data['message'],
            'data': data['data'],
          };
        } else {
          throw Exception('Response format tidak sesuai');
        }
      } else if (response.statusCode == 404) {
        throw Exception('Produk tidak ditemukan');
      } else {
        final errorData = json.decode(response.body);
        throw Exception(
          'HTTP ${response.statusCode}: ${errorData['message'] ?? 'Unknown error'}',
        );
      }
    } catch (e) {
      print('Error in getProdukDetail: $e'); // Debug log
      throw Exception('Error fetching product detail: $e');
    }
  }

  /// PERBAIKAN: Ubah dari static ke instance method dan perbaiki endpoint
  Future<List<KategoriModel>> getKategori() async {
    try {
      final uri = Uri.parse('${AppConstants.apiUrl}/kategori');

      print('Request URL: $uri'); // Debug log

      final response = await http.get(
        uri,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      print('Response status: ${response.statusCode}'); // Debug log
      print('Response body: ${response.body}'); // Debug log

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);

        if (data['status'] == 'success' && data.containsKey('data')) {
          final List<dynamic> kategoriData = data['data'];
          return kategoriData
              .map((item) => KategoriModel.fromJson(item))
              .toList();
        } else {
          throw Exception('Response format tidak sesuai');
        }
      } else {
        final errorData = json.decode(response.body);
        throw Exception(
          'HTTP ${response.statusCode}: ${errorData['message'] ?? 'Unknown error'}',
        );
      }
    } catch (e) {
      print('Error in getKategori: $e'); // Debug log
      throw Exception('Error fetching categories: $e');
    }
  }

  /// Mengambil informasi aplikasi
  Future<Map<String, dynamic>> getAppInfo() async {
    try {
      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/api/info'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        return data;
      } else {
        throw Exception('Failed to load app info: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error fetching app info: $e');
    }
  }

  // TAMBAHAN: Instance getter untuk akses global
  static ApiService get instance => _instance;
}

int? _parseInt(dynamic value) {
  if (value == null) return null;
  if (value is int) return value;
  if (value is double) return value.toInt();
  if (value is String) return int.tryParse(value); // "5" → 5
  return null;
}
