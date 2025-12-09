import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../services/api_service.dart';

enum AuthStatus { initial, loading, authenticated, unauthenticated }

class AuthProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();

  AuthStatus _status = AuthStatus.initial;
  User? _user;
  String? _errorMessage;
  bool _isLoading = false;

  // Getters
  AuthStatus get status => _status;
  User? get user => _user;
  String? get errorMessage => _errorMessage;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _status == AuthStatus.authenticated;

  // Initialize - check if user already logged in
  Future<void> initialize() async {
    _setLoading(true);

    try {
      final isLoggedIn = await _apiService.isLoggedIn();

      if (isLoggedIn) {
        final storedUser = await _apiService.getStoredUser();
        if (storedUser != null) {
          _user = storedUser;
          _status = AuthStatus.authenticated;
        } else {
          _status = AuthStatus.unauthenticated;
        }
      } else {
        _status = AuthStatus.unauthenticated;
      }
    } catch (e) {
      _status = AuthStatus.unauthenticated;
      _errorMessage = 'Terjadi kesalahan saat inisialisasi';
    }

    _setLoading(false);
  }

  // Login
  Future<bool> login({required String email, required String password}) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.login(
        email: email,
        password: password,
      );

      if (response.success && response.data != null) {
        final userData = response.data!['user'];
        final role = response.data!['role'];

        _user = User.fromJson(userData, role);
        _status = AuthStatus.authenticated;
        _setLoading(false);
        return true;
      } else {
        _errorMessage = response.message;
        _status = AuthStatus.unauthenticated;
        _setLoading(false);
        return false;
      }
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan saat login';
      _status = AuthStatus.unauthenticated;
      _setLoading(false);
      return false;
    }
  }

  // Register pembeli
  Future<bool> registerPembeli({
    required String nama,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.registerPembeli(
        nama: nama,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
      );

      if (response.success) {
        _setLoading(false);
        return true;
      } else {
        _errorMessage = response.message;
        _setLoading(false);
        return false;
      }
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan saat registrasi';
      _setLoading(false);
      return false;
    }
  }

  // Logout
  Future<void> logout() async {
    _setLoading(true);

    try {
      await _apiService.logout();
      _setLoading(false);
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan saat logout';
      _setLoading(false);
    }

    _user = null;
    _status = AuthStatus.unauthenticated;
    _clearError();
    _setLoading(false);
  }

  // Helper methods
  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  void clearError() {
    _clearError();
  }

  // Get user role-specific information
  String getUserDisplayName() {
    return _user?.nama ?? 'User';
  }

  String getUserRole() {
    return _user?.role ?? 'unknown';
  }

  String getRoleDisplayName() {
    switch (_user?.role) {
      case 'pembeli':
        return 'Pembeli';
      case 'penitip':
        return 'Penitip';
      case 'organisasi':
        return 'Organisasi';
      case 'admin':
        return 'Admin';
      case 'cs':
        return 'Customer Service';
      case 'gudang':
        return 'Pegawai Gudang';
      case 'owner':
        return 'Owner';
      case 'hunter':
        return 'Hunter';
      case 'kurir':
        return 'Kurir';
      default:
        return 'User';
    }
  }

  // Check if user has specific role
  bool hasRole(String role) {
    return _user?.role == role;
  }

  // Check if user is customer (pembeli or penitip)
  bool isCustomer() {
    return hasRole('pembeli') || hasRole('penitip') || hasRole('organisasi');
  }

  // Check if user is employee
  bool isEmployee() {
    return hasRole('admin') ||
        hasRole('cs') ||
        hasRole('gudang') ||
        hasRole('owner') ||
        hasRole('hunter') ||
        hasRole('kurir');
  }
}
