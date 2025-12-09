import 'dart:convert';
import 'package:awesome_notifications/awesome_notifications.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  Future<void> initialize() async {
    await AwesomeNotifications().initialize(
      null,
      [
        NotificationChannel(
          channelKey: 'reusemart_general',
          channelName: 'ReuseMart Notifications',
          channelDescription: 'Notifikasi umum dari aplikasi ReuseMart',
          defaultColor: const Color(0xFF2E7D32),
          ledColor: const Color(0xFF2E7D32),
          importance: NotificationImportance.High,
          channelShowBadge: true,
          playSound: true,
          enableVibration: true,
        ),
        NotificationChannel(
          channelKey: 'reusemart_penitipan',
          channelName: 'Penitipan Notifications',
          channelDescription: 'Notifikasi terkait masa penitipan barang',
          defaultColor: const Color(0xFFFF9800),
          ledColor: const Color(0xFFFF9800),
          importance: NotificationImportance.High,
          channelShowBadge: true,
          playSound: true,
          enableVibration: true,
        ),
        NotificationChannel(
          channelKey: 'reusemart_transaksi',
          channelName: 'Transaksi Notifications',
          channelDescription: 'Notifikasi terkait transaksi penjualan',
          defaultColor: const Color(0xFF4CAF50),
          ledColor: const Color(0xFF4CAF50),
          importance: NotificationImportance.High,
          channelShowBadge: true,
          playSound: true,
          enableVibration: true,
        ),
        NotificationChannel(
          channelKey: 'reusemart_pengiriman',
          channelName: 'Pengiriman Notifications',
          channelDescription: 'Notifikasi terkait status pengiriman',
          defaultColor: const Color(0xFF2196F3),
          ledColor: const Color(0xFF2196F3),
          importance: NotificationImportance.High,
          channelShowBadge: true,
          playSound: true,
          enableVibration: true,
        ),
        NotificationChannel(
          channelKey: 'reusemart_donasi',
          channelName: 'Donasi Notifications',
          channelDescription: 'Notifikasi terkait donasi barang',
          defaultColor: const Color(0xFFE91E63),
          ledColor: const Color(0xFFE91E63),
          importance: NotificationImportance.High,
          channelShowBadge: true,
          playSound: true,
          enableVibration: true,
        ),
      ],
    );

    // Request permissions
    await AwesomeNotifications().isNotificationAllowed().then((isAllowed) {
      if (!isAllowed) {
        AwesomeNotifications().requestPermissionToSendNotifications();
      }
    });

    // Set up listeners
    AwesomeNotifications().setListeners(
      onActionReceivedMethod: _onActionReceivedMethod,
      onNotificationCreatedMethod: _onNotificationCreatedMethod,
      onNotificationDisplayedMethod: _onNotificationDisplayedMethod,
      onDismissActionReceivedMethod: _onDismissActionReceivedMethod,
    );
  }

  static Future<void> _onActionReceivedMethod(
      ReceivedAction receivedAction) async {
    debugPrint(
        'Notification action received: ${receivedAction.buttonKeyPressed}');

    if (receivedAction.payload != null) {
      try {
        final data = jsonDecode(receivedAction.payload!['data'] ?? '{}');
        _handleNotificationTap(data);
      } catch (e) {
        debugPrint('Error handling notification tap: $e');
      }
    }
  }

  static Future<void> _onNotificationCreatedMethod(
      ReceivedNotification receivedNotification) async {
    debugPrint('Notification created: ${receivedNotification.title}');
  }

  static Future<void> _onNotificationDisplayedMethod(
      ReceivedNotification receivedNotification) async {
    debugPrint('Notification displayed: ${receivedNotification.title}');
  }

  static Future<void> _onDismissActionReceivedMethod(
      ReceivedAction receivedAction) async {
    debugPrint('Notification dismissed: ${receivedAction.id}');
  }

  static void _handleNotificationTap(Map<String, dynamic> data) {
    final type = data['type'];

    switch (type) {
      case 'penitipan_h3':
      case 'penitipan_hari_h':
        debugPrint('Navigate to penitipan detail');
        break;
      case 'barang_laku':
        debugPrint('Navigate to transaksi detail');
        break;
      case 'jadwal_pengiriman':
      case 'jadwal_pengambilan':
      case 'status_pengiriman':
        debugPrint('Navigate to tracking page');
        break;
      case 'barang_didonasikan':
        debugPrint('Navigate to donasi detail');
        break;
      default:
        debugPrint('Unknown notification type: $type');
    }
  }

  Future<void> showNotification({
    required String title,
    required String body,
    Map<String, String>? payload,
    String channelKey = 'reusemart_general',
  }) async {
    try {
      await AwesomeNotifications().createNotification(
        content: NotificationContent(
          id: DateTime.now().millisecondsSinceEpoch.remainder(100000),
          channelKey: channelKey,
          title: title,
          body: body,
          payload: payload,
          notificationLayout: NotificationLayout.Default,
        ),
      );
    } catch (e) {
      debugPrint('Error showing notification: $e');
    }
  }

  Future<void> showPenitipanNotification({
    required String title,
    required String body,
    required Map<String, dynamic> data,
  }) async {
    await showNotification(
      title: title,
      body: body,
      payload: {'data': jsonEncode(data)},
      channelKey: 'reusemart_penitipan',
    );
  }

  Future<void> showTransaksiNotification({
    required String title,
    required String body,
    required Map<String, dynamic> data,
  }) async {
    await showNotification(
      title: title,
      body: body,
      payload: {'data': jsonEncode(data)},
      channelKey: 'reusemart_transaksi',
    );
  }

  Future<void> showPengirimanNotification({
    required String title,
    required String body,
    required Map<String, dynamic> data,
  }) async {
    await showNotification(
      title: title,
      body: body,
      payload: {'data': jsonEncode(data)},
      channelKey: 'reusemart_pengiriman',
    );
  }

  Future<void> showDonasiNotification({
    required String title,
    required String body,
    required Map<String, dynamic> data,
  }) async {
    await showNotification(
      title: title,
      body: body,
      payload: {'data': jsonEncode(data)},
      channelKey: 'reusemart_donasi',
    );
  }

  Future<bool> areNotificationsEnabled() async {
    return await AwesomeNotifications().isNotificationAllowed();
  }

  Future<void> openNotificationSettings() async {
    await AwesomeNotifications().showNotificationConfigPage();
  }

  Future<void> cancelNotification(int id) async {
    await AwesomeNotifications().cancel(id);
  }

  Future<void> cancelAllNotifications() async {
    await AwesomeNotifications().cancelAll();
  }
}
