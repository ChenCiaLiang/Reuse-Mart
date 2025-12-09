// lib/services/websocket_service.dart - ENHANCED VERSION
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'notification_service.dart';

class WebSocketService extends ChangeNotifier {
  static final WebSocketService _instance = WebSocketService._internal();
  factory WebSocketService() => _instance;
  WebSocketService._internal();

  PusherChannelsFlutter? _pusher;
  bool _isConnected = false;
  String? _authToken;
  String? _userType;
  int? _userId;

  // PRODUCTION CONFIG - Sesuaikan dengan server production
  final String _appKey = 'your-laravel-app-key';
  final String _cluster = 'ap1'; // Sesuaikan region
  final String _wsHost = '127.0.0.1'; // Ganti dengan host production
  final int _wsPort = 8080;

  bool get isConnected => _isConnected;

  Future<void> initialize({
    required String authToken,
    required String userType,
    required int userId,
  }) async {
    try {
      _authToken = authToken;
      _userType = userType;
      _userId = userId;

      await _initializePusher();
      await _subscribeToChannels();

      debugPrint(
          'WebSocket service initialized for user: $_userId, type: $_userType');
    } catch (e) {
      debugPrint('Error initializing WebSocket service: $e');
    }
  }

  Future<void> _initializePusher() async {
    try {
      _pusher = PusherChannelsFlutter.getInstance();

      await _pusher!.init(
        apiKey: _appKey,
        cluster: _cluster,
        onConnectionStateChange: _onConnectionStateChange,
        onError: _onError,
        onSubscriptionSucceeded: _onSubscriptionSucceeded,
        onEvent: _onEvent,
        onSubscriptionError: _onSubscriptionError,
        onDecryptionFailure: _onDecryptionFailure,
        onMemberAdded: _onMemberAdded,
        onMemberRemoved: _onMemberRemoved,
        authEndpoint: 'http://$_wsHost:8000/api/broadcasting/auth',
        onAuthorizer: _onAuthorizer,
      );

      await _pusher!.connect();
    } catch (e) {
      debugPrint('Error initializing pusher: $e');
    }
  }

  Map<String, String>? _onAuthorizer(
      String channelName, String socketId, dynamic options) {
    return {
      'Authorization': 'Bearer $_authToken',
      'Content-Type': 'application/json',
    };
  }

  Future<void> _subscribeToChannels() async {
    if (_pusher == null || _userId == null) return;

    try {
      // Subscribe berdasarkan user type
      String channelName = '';

      switch (_userType) {
        case 'pembeli':
          channelName = 'private-pembeli.$_userId';
          break;
        case 'penitip':
          channelName = 'private-penitip.$_userId';
          break;
        case 'kurir':
          channelName = 'private-kurir.$_userId';
          break;
        case 'hunter':
          channelName = 'private-hunter.$_userId';
          break;
        default:
          channelName = 'private-user.$_userId';
      }

      await _pusher!.subscribe(channelName: channelName);
      debugPrint('Subscribed to channel: $channelName');
    } catch (e) {
      debugPrint('Error subscribing to channels: $e');
    }
  }

  void _onConnectionStateChange(dynamic currentState, dynamic previousState) {
    debugPrint('Connection state changed: $previousState -> $currentState');
    _isConnected = currentState == 'connected';
    notifyListeners();
  }

  void _onError(String message, int? code, dynamic e) {
    debugPrint('WebSocket error: $message (code: $code)');
  }

  void _onEvent(PusherEvent event) {
    debugPrint('Received event: ${event.eventName}');
    debugPrint('Event data: ${event.data}');

    // Handle berbagai jenis event
    switch (event.eventName) {
      case 'PenitipanH3Notification':
        _handlePenitipanNotification(event.data, isH3: true);
        break;
      case 'PenitipanHariHNotification':
        _handlePenitipanNotification(event.data, isH3: false);
        break;
      case 'BarangLakuNotification':
        _handleTransaksiNotification(event.data);
        break;

      // FUNGSIONALITAS 126: Notifikasi Jadwal Pengiriman
      case 'JadwalPengirimanNotification':
        _handleJadwalPengirimanNotification(event.data);
        break;

      // FUNGSIONALITAS 127: Notifikasi Jadwal Pengambilan
      case 'JadwalPengambilanNotification':
        _handleJadwalPengambilanNotification(event.data);
        break;

      // Status pengiriman update
      case 'StatusPengirimanNotification':
        _handleStatusPengirimanNotification(event.data);
        break;

      case 'BarangDidonasikanNotification':
        _handleDonasiNotification(event.data);
        break;

      default:
        _handleGeneralNotification(event.data);
    }
  }

  // FUNGSIONALITAS 126: Notifikasi Jadwal Pengiriman
  void _handleJadwalPengirimanNotification(String data) {
    try {
      final notificationData = jsonDecode(data);

      // Validasi data yang diterima
      final idTransaksi = notificationData['data']?['idTransaksi'];
      final tanggalKirim = notificationData['data']?['tanggalKirim'];
      final namaPembeli = notificationData['data']?['namaPembeli'];
      final namaKurir = notificationData['data']?['namaKurir'];
      final alamatPengiriman = notificationData['data']?['alamatPengiriman'];
      final nomorNota = notificationData['data']?['nomorNota'];

      // Format pesan berdasarkan role user
      String title = '';
      String body = '';
      String actionText = '';

      switch (_userType) {
        case 'pembeli':
          title = '🚚 Jadwal Pengiriman Pesanan';
          body =
              'Pesanan Anda (Nota: $nomorNota) akan dikirim pada $tanggalKirim oleh kurir $namaKurir';
          actionText = 'Lihat Detail Pesanan';
          break;
        case 'penitip':
          title = '📦 Barang Akan Dikirim';
          body =
              'Barang Anda akan dikirim kepada pembeli $namaPembeli pada $tanggalKirim';
          actionText = 'Lihat Status Barang';
          break;
        case 'kurir':
          title = '🚛 Tugas Pengiriman Baru';
          body =
              'Anda memiliki jadwal pengiriman pada $tanggalKirim ke alamat: $alamatPengiriman';
          actionText = 'Lihat Detail Pengiriman';
          break;
        default:
          title = '📋 Jadwal Pengiriman';
          body = 'Ada jadwal pengiriman baru pada $tanggalKirim';
          actionText = 'Lihat Detail';
      }

      // Tampilkan notifikasi pengiriman
      NotificationService().showPengirimanNotification(
        title: title,
        body: body,
        data: {
          'type': 'jadwal_pengiriman',
          'idTransaksi': idTransaksi,
          'tanggalKirim': tanggalKirim,
          'userType': _userType,
          'action': 'view_delivery_schedule',
          'actionText': actionText,
          'nomorNota': nomorNota,
          ...notificationData['data'] ?? {},
        },
      );

      // Simpan ke local storage
      _saveNotificationToLocal({
        'title': title,
        'message': body,
        'type': 'jadwal_pengiriman',
        'actionText': actionText,
        'data': notificationData['data'] ?? {},
        'priority': 'high',
        'category': 'pengiriman',
      });

      debugPrint(
          '✅ Jadwal pengiriman notification handled for $_userType user ID: $_userId');
    } catch (e) {
      debugPrint('❌ Error handling jadwal pengiriman notification: $e');
    }
  }

  // FUNGSIONALITAS 127: Notifikasi Jadwal Pengambilan
  void _handleJadwalPengambilanNotification(String data) {
    try {
      final notificationData = jsonDecode(data);

      // Validasi data yang diterima
      final idTransaksi = notificationData['data']?['idTransaksi'];
      final tanggalAmbil = notificationData['data']?['tanggalAmbil'];
      final tanggalBatasAmbil = notificationData['data']?['tanggalBatasAmbil'];
      final namaPembeli = notificationData['data']?['namaPembeli'];
      final nomorNota = notificationData['data']?['nomorNota'];
      final alamatGudang = notificationData['data']?['alamatGudang'] ??
          'Jl. Green Eco Park No. 456 Yogyakarta';
      final jamOperasional =
          notificationData['data']?['jamOperasional'] ?? '08:00 - 20:00';

      // Format pesan berdasarkan role user
      String title = '';
      String body = '';
      String actionText = '';

      switch (_userType) {
        case 'pembeli':
          title = '🏪 Pesanan Siap Diambil';
          body =
              'Pesanan Anda (Nota: $nomorNota) dapat diambil mulai $tanggalAmbil di $alamatGudang. '
              'Jam operasional: $jamOperasional. Batas pengambilan: $tanggalBatasAmbil';
          actionText = 'Lihat Lokasi & Jadwal';
          break;
        case 'penitip':
          title = '👥 Barang Akan Diambil Pembeli';
          body =
              'Barang Anda akan diambil oleh pembeli $namaPembeli mulai tanggal $tanggalAmbil';
          actionText = 'Lihat Status Barang';
          break;
        default:
          title = '📋 Jadwal Pengambilan';
          body = 'Ada jadwal pengambilan barang pada $tanggalAmbil';
          actionText = 'Lihat Detail';
      }

      // Tampilkan notifikasi pengambilan
      NotificationService().showPengirimanNotification(
        title: title,
        body: body,
        data: {
          'type': 'jadwal_pengambilan',
          'idTransaksi': idTransaksi,
          'tanggalAmbil': tanggalAmbil,
          'tanggalBatasAmbil': tanggalBatasAmbil,
          'userType': _userType,
          'action': 'view_pickup_schedule',
          'actionText': actionText,
          'nomorNota': nomorNota,
          'alamatGudang': alamatGudang,
          'jamOperasional': jamOperasional,
          ...notificationData['data'] ?? {},
        },
      );

      // Simpan ke local storage
      _saveNotificationToLocal({
        'title': title,
        'message': body,
        'type': 'jadwal_pengambilan',
        'actionText': actionText,
        'data': notificationData['data'] ?? {},
        'priority': 'high',
        'category': 'pengambilan',
      });

      debugPrint(
          '✅ Jadwal pengambilan notification handled for $_userType user ID: $_userId');
    } catch (e) {
      debugPrint('❌ Error handling jadwal pengambilan notification: $e');
    }
  }

  // Status pengiriman (enhanced dengan action yang lebih spesifik)
  void _handleStatusPengirimanNotification(String data) {
    try {
      final notificationData = jsonDecode(data);

      final status = notificationData['data']?['status'];
      final idTransaksi = notificationData['data']?['idTransaksi'];
      final nomorNota = notificationData['data']?['nomorNota'];
      final kurir = notificationData['data']?['namaKurir'];

      String title = '';
      String body = '';
      String actionText = 'Lihat Detail';
      String emoji = '';

      switch (status) {
        case 'disiapkan':
          emoji = '📦';
          title = 'Pesanan Sedang Disiapkan';
          body =
              'Pesanan Anda (Nota: $nomorNota) sedang disiapkan untuk pengiriman';
          actionText = 'Lihat Status';
          break;
        case 'dikirim':
          emoji = '🚚';
          title = 'Pesanan Sedang Dikirim';
          body =
              'Pesanan Anda sedang dalam perjalanan dengan kurir ${kurir ?? 'ReUseMart'}';
          actionText = 'Lacak Pengiriman';
          break;
        case 'sampai':
          emoji = '📍';
          title = 'Pesanan Sudah Sampai';
          body = 'Pesanan Anda telah sampai di lokasi tujuan';
          actionText = 'Konfirmasi Penerimaan';
          break;
        case 'selesai':
          emoji = '✅';
          title = 'Pesanan Berhasil Diterima';
          body =
              'Pesanan Anda telah berhasil diterima. Terima kasih telah berbelanja di ReUseMart!';
          actionText = 'Beri Rating';
          break;
        default:
          emoji = '📋';
          title = 'Update Status Pengiriman';
          body = 'Ada update status pengiriman: $status';
      }

      // Tampilkan notifikasi status pengiriman
      NotificationService().showPengirimanNotification(
        title: '$emoji $title',
        body: body,
        data: {
          'type': 'status_pengiriman',
          'idTransaksi': idTransaksi,
          'status': status,
          'userType': _userType,
          'action': 'view_tracking',
          'actionText': actionText,
          'nomorNota': nomorNota,
          ...notificationData['data'] ?? {},
        },
      );

      // Simpan ke local storage
      _saveNotificationToLocal({
        'title': '$emoji $title',
        'message': body,
        'type': 'status_pengiriman',
        'actionText': actionText,
        'data': notificationData['data'] ?? {},
        'priority': status == 'selesai' ? 'high' : 'normal',
        'category': 'tracking',
      });

      debugPrint('✅ Status pengiriman notification handled: $status');
    } catch (e) {
      debugPrint('❌ Error handling status pengiriman notification: $e');
    }
  }

  // Enhanced notification handlers untuk konsistensi
  void _handlePenitipanNotification(String data, {required bool isH3}) {
    try {
      final notificationData = jsonDecode(data);
      final produkNames = notificationData['data']?['produk_names'] ?? [];
      final produkList = (produkNames as List).join(', ');

      String title =
          isH3 ? '⚠️ Peringatan Masa Penitipan' : '🚨 Masa Penitipan Berakhir';
      String body = isH3
          ? '3 hari lagi masa penitipan berakhir untuk: $produkList'
          : 'Hari ini masa penitipan berakhir untuk: $produkList';

      NotificationService().showPenitipanNotification(
        title: title,
        body: body,
        data: {
          'type': isH3 ? 'penitipan_h3' : 'penitipan_hari_h',
          'action': 'view_penitipan',
          'actionText': isH3 ? 'Perpanjang/Ambil' : 'Ambil Sekarang',
          ...notificationData['data'] ?? {},
        },
      );

      _saveNotificationToLocal({
        'title': title,
        'message': body,
        'type': isH3 ? 'penitipan_h3' : 'penitipan_hari_h',
        'actionText': isH3 ? 'Perpanjang/Ambil' : 'Ambil Sekarang',
        'data': notificationData['data'] ?? {},
        'priority': 'high',
        'category': 'penitipan',
      });
    } catch (e) {
      debugPrint('❌ Error handling penitipan notification: $e');
    }
  }

  void _handleTransaksiNotification(String data) {
    try {
      final notificationData = jsonDecode(data);
      final produkName = notificationData['data']?['produk_name'] ?? 'Barang';
      final harga = notificationData['data']?['harga'] ?? 0;

      String title = '🎉 Barang Terjual';
      String body =
          'Selamat! Barang "$produkName" berhasil terjual dengan harga Rp ${_formatCurrency(harga)}';

      NotificationService().showTransaksiNotification(
        title: title,
        body: body,
        data: {
          'type': 'barang_laku',
          'action': 'view_transaction',
          'actionText': 'Lihat Detail Penjualan',
          ...notificationData['data'] ?? {},
        },
      );

      _saveNotificationToLocal({
        'title': title,
        'message': body,
        'type': 'barang_laku',
        'actionText': 'Lihat Detail Penjualan',
        'data': notificationData['data'] ?? {},
        'priority': 'normal',
        'category': 'transaksi',
      });
    } catch (e) {
      debugPrint('❌ Error handling transaksi notification: $e');
    }
  }

  void _handleDonasiNotification(String data) {
    try {
      final notificationData = jsonDecode(data);
      final produkName = notificationData['data']?['produk_name'] ?? 'Barang';
      final organisasi =
          notificationData['data']?['nama_organisasi'] ?? 'Organisasi';

      String title = '❤️ Barang Didonasikan';
      String body =
          'Barang "$produkName" telah berhasil didonasikan kepada $organisasi';

      NotificationService().showDonasiNotification(
        title: title,
        body: body,
        data: {
          'type': 'barang_didonasikan',
          'action': 'view_donation',
          'actionText': 'Lihat Detail Donasi',
          ...notificationData['data'] ?? {},
        },
      );

      _saveNotificationToLocal({
        'title': title,
        'message': body,
        'type': 'barang_didonasikan',
        'actionText': 'Lihat Detail Donasi',
        'data': notificationData['data'] ?? {},
        'priority': 'normal',
        'category': 'donasi',
      });
    } catch (e) {
      debugPrint('❌ Error handling donasi notification: $e');
    }
  }

  void _handleGeneralNotification(String data) {
    try {
      final notificationData = jsonDecode(data);

      NotificationService().showNotification(
        title: notificationData['title'] ?? '📢 Notifikasi',
        body: notificationData['message'] ?? '',
        payload: {
          'data': jsonEncode({
            'type': 'general',
            'action': 'view_detail',
            'actionText': 'Lihat Detail',
            ...notificationData['data'] ?? {},
          }),
        },
      );

      _saveNotificationToLocal({
        'title': notificationData['title'] ?? '📢 Notifikasi',
        'message': notificationData['message'] ?? '',
        'type': 'general',
        'actionText': 'Lihat Detail',
        'data': notificationData['data'] ?? {},
        'priority': 'normal',
        'category': 'umum',
      });
    } catch (e) {
      debugPrint('❌ Error handling general notification: $e');
    }
  }

  Future<void> _saveNotificationToLocal(
      Map<String, dynamic> notificationData) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final existingNotifications = prefs.getStringList('notifications') ?? [];

      notificationData['timestamp'] = DateTime.now().toIso8601String();
      notificationData['isRead'] = false;
      notificationData['id'] = DateTime.now().millisecondsSinceEpoch;

      existingNotifications.insert(0, jsonEncode(notificationData));

      // Batasi hanya 200 notifikasi terakhir untuk performa
      if (existingNotifications.length > 200) {
        existingNotifications.removeRange(200, existingNotifications.length);
      }

      await prefs.setStringList('notifications', existingNotifications);
      notifyListeners();
    } catch (e) {
      debugPrint('❌ Error saving notification to local: $e');
    }
  }

  // Helper method untuk format currency
  String _formatCurrency(dynamic amount) {
    if (amount == null) return '0';
    try {
      final num value = num.parse(amount.toString());
      return value.toString().replaceAllMapped(
            RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
            (Match m) => '${m[1]}.',
          );
    } catch (e) {
      return amount.toString();
    }
  }

  // Connection event handlers
  void _onSubscriptionSucceeded(String channelName, dynamic data) {
    debugPrint('✅ Subscription succeeded: $channelName');
  }

  void _onSubscriptionError(String message, dynamic e) {
    debugPrint('❌ Subscription error: $message');
  }

  void _onDecryptionFailure(String event, String reason) {
    debugPrint('❌ Decryption failure: $event - $reason');
  }

  void _onMemberAdded(String channelName, PusherMember member) {
    debugPrint('➕ Member added: $channelName');
  }

  void _onMemberRemoved(String channelName, PusherMember member) {
    debugPrint('➖ Member removed: $channelName');
  }

  Future<void> disconnect() async {
    try {
      await _pusher?.disconnect();
      _isConnected = false;
      notifyListeners();
      debugPrint('🔌 WebSocket disconnected');
    } catch (e) {
      debugPrint('❌ Error disconnecting: $e');
    }
  }

  // Notification management methods
  Future<List<Map<String, dynamic>>> getLocalNotifications() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final notificationStrings = prefs.getStringList('notifications') ?? [];

      return notificationStrings
          .map((e) => jsonDecode(e) as Map<String, dynamic>)
          .toList();
    } catch (e) {
      debugPrint('❌ Error getting local notifications: $e');
      return [];
    }
  }

  Future<List<Map<String, dynamic>>> getNotificationsByCategory(
      String category) async {
    try {
      final allNotifications = await getLocalNotifications();
      return allNotifications
          .where((notif) => notif['category'] == category)
          .toList();
    } catch (e) {
      debugPrint('❌ Error getting notifications by category: $e');
      return [];
    }
  }

  Future<void> markNotificationAsRead(int id) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final notificationStrings = prefs.getStringList('notifications') ?? [];

      for (int i = 0; i < notificationStrings.length; i++) {
        final notification = jsonDecode(notificationStrings[i]);
        if (notification['id'] == id) {
          notification['isRead'] = true;
          notificationStrings[i] = jsonEncode(notification);
          break;
        }
      }

      await prefs.setStringList('notifications', notificationStrings);
      notifyListeners();
    } catch (e) {
      debugPrint('❌ Error marking notification as read: $e');
    }
  }

  Future<void> markAllNotificationsAsRead() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final notificationStrings = prefs.getStringList('notifications') ?? [];

      final updatedNotifications =
          notificationStrings.map((notificationString) {
        final notification = jsonDecode(notificationString);
        notification['isRead'] = true;
        return jsonEncode(notification);
      }).toList();

      await prefs.setStringList('notifications', updatedNotifications);
      notifyListeners();
    } catch (e) {
      debugPrint('❌ Error marking all notifications as read: $e');
    }
  }

  Future<void> clearAllNotifications() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('notifications');
      notifyListeners();
    } catch (e) {
      debugPrint('❌ Error clearing all notifications: $e');
    }
  }

  Future<int> getUnreadNotificationCount() async {
    try {
      final notifications = await getLocalNotifications();
      return notifications.where((notif) => !(notif['isRead'] ?? false)).length;
    } catch (e) {
      debugPrint('❌ Error getting unread notification count: $e');
      return 0;
    }
  }

  // ===== TESTING METHODS (Development Only) =====
  Future<void> testJadwalPengirimanNotification() async {
    debugPrint('🧪 Testing Jadwal Pengiriman Notification...');
    _handleJadwalPengirimanNotification(jsonEncode({
      'title': 'Test Jadwal Pengiriman',
      'message': 'Ini adalah test notifikasi jadwal pengiriman',
      'data': {
        'idTransaksi': 123,
        'tanggalKirim': '2025-06-03 10:00:00',
        'namaPembeli': 'John Doe',
        'namaKurir': 'Budi Santoso',
        'alamatPengiriman': 'Jl. Test No. 123, Jakarta',
        'nomorNota': '25.06.123',
      },
    }));
  }

  Future<void> testJadwalPengambilanNotification() async {
    debugPrint('🧪 Testing Jadwal Pengambilan Notification...');
    _handleJadwalPengambilanNotification(jsonEncode({
      'title': 'Test Jadwal Pengambilan',
      'message': 'Ini adalah test notifikasi jadwal pengambilan',
      'data': {
        'idTransaksi': 124,
        'tanggalAmbil': '2025-06-03 09:00:00',
        'tanggalBatasAmbil': '2025-06-05 20:00:00',
        'namaPembeli': 'Jane Doe',
        'nomorNota': '25.06.124',
        'alamatGudang': 'Jl. Green Eco Park No. 456 Yogyakarta',
        'jamOperasional': '08:00 - 20:00',
      },
    }));
  }

  Future<void> testAllNotificationTypes() async {
    debugPrint('🧪 Testing All Notification Types...');

    // Test jadwal pengiriman
    await testJadwalPengirimanNotification();
    await Future.delayed(Duration(seconds: 2));

    // Test jadwal pengambilan
    await testJadwalPengambilanNotification();
    await Future.delayed(Duration(seconds: 2));

    // Test status pengiriman
    _handleStatusPengirimanNotification(jsonEncode({
      'data': {
        'status': 'dikirim',
        'idTransaksi': 125,
        'nomorNota': '25.06.125',
        'namaKurir': 'Andi Kurniawan',
      },
    }));

    debugPrint('✅ All notification tests completed');
  }
}
