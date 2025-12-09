// lib/screens/notifications/notification_screen.dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_constants.dart';
import '../../services/websocket_service.dart';
import '../../widgets/custom_button.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  List<Map<String, dynamic>> _allNotifications = [];
  List<Map<String, dynamic>> _filteredNotifications = [];
  bool _isLoading = true;
  String _selectedCategory = 'all';

  // Kategori notifikasi
  final List<Map<String, dynamic>> _categories = [
    {'key': 'all', 'label': 'Semua', 'icon': Icons.notifications},
    {'key': 'pengiriman', 'label': 'Pengiriman', 'icon': Icons.local_shipping},
    {'key': 'pengambilan', 'label': 'Pengambilan', 'icon': Icons.store},
    {'key': 'penitipan', 'label': 'Penitipan', 'icon': Icons.schedule},
    {'key': 'transaksi', 'label': 'Transaksi', 'icon': Icons.shopping_cart},
    {'key': 'donasi', 'label': 'Donasi', 'icon': Icons.favorite},
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _categories.length, vsync: this);
    _loadNotifications();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadNotifications() async {
    setState(() => _isLoading = true);

    try {
      final notifications = await WebSocketService().getLocalNotifications();
      setState(() {
        _allNotifications = notifications;
        _filterNotifications();
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      _showErrorSnackBar('Gagal memuat notifikasi: $e');
    }
  }

  void _filterNotifications() {
    if (_selectedCategory == 'all') {
      _filteredNotifications = _allNotifications;
    } else {
      _filteredNotifications = _allNotifications
          .where((notif) => notif['category'] == _selectedCategory)
          .toList();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: AppBar(
        title: const Text('Notifikasi'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          indicatorColor: Colors.white,
          onTap: (index) {
            setState(() {
              _selectedCategory = _categories[index]['key'];
              _filterNotifications();
            });
          },
          tabs: _categories.map((category) {
            final unreadCount = _getUnreadCountForCategory(category['key']);
            return Tab(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(category['icon'], size: 18),
                  const SizedBox(width: 8),
                  Text(category['label']),
                  if (unreadCount > 0) ...[
                    const SizedBox(width: 4),
                    Container(
                      padding: const EdgeInsets.all(4),
                      decoration: const BoxDecoration(
                        color: Colors.red,
                        shape: BoxShape.circle,
                      ),
                      child: Text(
                        unreadCount > 99 ? '99+' : unreadCount.toString(),
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            );
          }).toList(),
        ),
        actions: [
          // Mark all as read button
          IconButton(
            icon: const Icon(Icons.done_all),
            tooltip: 'Tandai semua sudah dibaca',
            onPressed:
                _filteredNotifications.any((n) => !(n['isRead'] ?? false))
                    ? _markAllAsRead
                    : null,
          ),
          // Test notifications button (development only)
          if (const bool.fromEnvironment('dart.vm.product') == false)
            PopupMenuButton<String>(
              icon: const Icon(Icons.bug_report),
              tooltip: 'Test Notifications',
              onSelected: _handleTestNotification,
              itemBuilder: (context) => [
                const PopupMenuItem(
                  value: 'test_pengiriman',
                  child: Text('Test Jadwal Pengiriman'),
                ),
                const PopupMenuItem(
                  value: 'test_pengambilan',
                  child: Text('Test Jadwal Pengambilan'),
                ),
                const PopupMenuItem(
                  value: 'test_all',
                  child: Text('Test Semua Notifikasi'),
                ),
              ],
            ),
        ],
      ),
      body: Consumer<WebSocketService>(
        builder: (context, webSocketService, child) {
          return RefreshIndicator(
            onRefresh: _loadNotifications,
            child: _buildNotificationContent(),
          );
        },
      ),
    );
  }

  Widget _buildNotificationContent() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(AppConstants.primaryColor),
        ),
      );
    }

    if (_filteredNotifications.isEmpty) {
      return _buildEmptyState();
    }

    return ListView.builder(
      padding: const EdgeInsets.all(AppConstants.paddingMedium),
      itemCount: _filteredNotifications.length,
      itemBuilder: (context, index) {
        final notification = _filteredNotifications[index];
        return _buildNotificationCard(notification, index);
      },
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            _getIconForCategory(_selectedCategory),
            size: 64,
            color: AppConstants.textSecondaryColor,
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          Text(
            _selectedCategory == 'all'
                ? 'Belum ada notifikasi'
                : 'Belum ada notifikasi ${_getCategoryLabel(_selectedCategory).toLowerCase()}',
            style: AppConstants.titleStyle.copyWith(
              color: AppConstants.textSecondaryColor,
            ),
          ),
          const SizedBox(height: AppConstants.paddingSmall),
          Text(
            'Notifikasi akan muncul di sini ketika ada aktivitas baru',
            style: AppConstants.bodyStyle.copyWith(
              color: AppConstants.textSecondaryColor,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildNotificationCard(Map<String, dynamic> notification, int index) {
    final isRead = notification['isRead'] ?? false;
    final type = notification['type'] ?? 'general';
    final priority = notification['priority'] ?? 'normal';
    final timestamp = notification['timestamp'];
    final actionText = notification['actionText'] ?? 'Lihat Detail';

    return Container(
      margin: const EdgeInsets.only(bottom: AppConstants.paddingMedium),
      decoration: BoxDecoration(
        color: AppConstants.surfaceColor,
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
        boxShadow: AppConstants.defaultShadow,
        border: !isRead
            ? Border.all(color: AppConstants.primaryColor.withOpacity(0.3))
            : null,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
          onTap: () => _handleNotificationTap(notification),
          child: Padding(
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header row
                Row(
                  children: [
                    // Icon with priority indicator
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: _getNotificationColor(type).withOpacity(0.1),
                        shape: BoxShape.circle,
                        border: priority == 'high'
                            ? Border.all(color: Colors.red, width: 2)
                            : null,
                      ),
                      child: Icon(
                        _getNotificationIcon(type),
                        color: _getNotificationColor(type),
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: AppConstants.paddingMedium),

                    // Title and subtitle
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            notification['title'] ?? 'Notifikasi',
                            style: AppConstants.titleStyle.copyWith(
                              fontWeight:
                                  isRead ? FontWeight.normal : FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            _formatTimestamp(timestamp),
                            style: AppConstants.captionStyle.copyWith(
                              color: AppConstants.textSecondaryColor,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Unread indicator and priority badge
                    Column(
                      children: [
                        if (!isRead)
                          Container(
                            width: 8,
                            height: 8,
                            decoration: const BoxDecoration(
                              color: AppConstants.primaryColor,
                              shape: BoxShape.circle,
                            ),
                          ),
                        if (priority == 'high')
                          Container(
                            margin: const EdgeInsets.only(top: 4),
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.red,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Text(
                              'PENTING',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 8,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ],
                ),

                // Message content
                const SizedBox(height: AppConstants.paddingMedium),
                Text(
                  notification['message'] ?? '',
                  style: AppConstants.bodyStyle.copyWith(
                    height: 1.4,
                  ),
                ),

                // Action button for specific notification types
                if (_shouldShowActionButton(type)) ...[
                  const SizedBox(height: AppConstants.paddingMedium),
                  Align(
                    alignment: Alignment.centerRight,
                    child: CustomButton(
                      text: actionText,
                      onPressed: () => _handleNotificationAction(notification),
                      backgroundColor: _getNotificationColor(type),
                      height: 32,
                      width: null,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  bool _shouldShowActionButton(String type) {
    return [
      'jadwal_pengiriman',
      'jadwal_pengambilan',
      'status_pengiriman',
      'barang_laku',
    ].contains(type);
  }

  Color _getNotificationColor(String type) {
    switch (type) {
      case 'jadwal_pengiriman':
      case 'status_pengiriman':
        return const Color(0xFF2196F3); // Blue
      case 'jadwal_pengambilan':
        return const Color(0xFF4CAF50); // Green
      case 'penitipan_h3':
      case 'penitipan_hari_h':
        return const Color(0xFFFF9800); // Orange
      case 'barang_laku':
        return const Color(0xFF009688); // Teal
      case 'barang_didonasikan':
        return const Color(0xFFE91E63); // Pink
      default:
        return AppConstants.primaryColor;
    }
  }

  IconData _getNotificationIcon(String type) {
    switch (type) {
      case 'jadwal_pengiriman':
        return Icons.local_shipping;
      case 'jadwal_pengambilan':
        return Icons.store;
      case 'status_pengiriman':
        return Icons.track_changes;
      case 'penitipan_h3':
      case 'penitipan_hari_h':
        return Icons.schedule;
      case 'barang_laku':
        return Icons.shopping_cart;
      case 'barang_didonasikan':
        return Icons.favorite;
      default:
        return Icons.notifications;
    }
  }

  IconData _getIconForCategory(String category) {
    final categoryData = _categories.firstWhere(
      (cat) => cat['key'] == category,
      orElse: () => _categories.first,
    );
    return categoryData['icon'];
  }

  String _getCategoryLabel(String category) {
    final categoryData = _categories.firstWhere(
      (cat) => cat['key'] == category,
      orElse: () => _categories.first,
    );
    return categoryData['label'];
  }

  int _getUnreadCountForCategory(String category) {
    if (category == 'all') {
      return _allNotifications
          .where((notif) => !(notif['isRead'] ?? false))
          .length;
    }

    return _allNotifications
        .where((notif) =>
            notif['category'] == category && !(notif['isRead'] ?? false))
        .length;
  }

  String _formatTimestamp(String? timestamp) {
    if (timestamp == null) return '';

    try {
      final dateTime = DateTime.parse(timestamp);
      final now = DateTime.now();
      final difference = now.difference(dateTime);

      if (difference.inDays > 7) {
        return '${dateTime.day}/${dateTime.month}/${dateTime.year}';
      } else if (difference.inDays > 0) {
        return '${difference.inDays} hari yang lalu';
      } else if (difference.inHours > 0) {
        return '${difference.inHours} jam yang lalu';
      } else if (difference.inMinutes > 0) {
        return '${difference.inMinutes} menit yang lalu';
      } else {
        return 'Baru saja';
      }
    } catch (e) {
      return '';
    }
  }

  void _handleNotificationTap(Map<String, dynamic> notification) async {
    // Mark as read if not already read
    if (!(notification['isRead'] ?? false)) {
      await WebSocketService().markNotificationAsRead(notification['id']);
      _loadNotifications();
    }

    // Handle navigation based on notification type
    _handleNotificationAction(notification);
  }

  void _handleNotificationAction(Map<String, dynamic> notification) {
    final type = notification['type'];
    final data = notification['data'] ?? {};

    switch (type) {
      case 'jadwal_pengiriman':
        _navigateToDeliveryTracking(data);
        break;
      case 'jadwal_pengambilan':
        _navigateToPickupSchedule(data);
        break;
      case 'status_pengiriman':
        _navigateToOrderTracking(data);
        break;
      case 'penitipan_h3':
      case 'penitipan_hari_h':
        _navigateToPenitipanDetail(data);
        break;
      case 'barang_laku':
        _navigateToTransactionDetail(data);
        break;
      case 'barang_didonasikan':
        _navigateToDonationDetail(data);
        break;
      default:
        _showInfoDialog(
            'Detail', 'Fitur navigasi untuk ${type} akan segera tersedia.');
    }
  }

  void _navigateToDeliveryTracking(Map<String, dynamic> data) {
    // TODO: Navigate to delivery tracking page
    _showInfoDialog(
      'Jadwal Pengiriman',
      'Navigasi ke halaman tracking pengiriman untuk transaksi ${data['idTransaksi']} akan segera tersedia.',
    );
  }

  void _navigateToPickupSchedule(Map<String, dynamic> data) {
    // TODO: Navigate to pickup schedule page
    _showInfoDialog(
      'Jadwal Pengambilan',
      'Navigasi ke halaman jadwal pengambilan untuk transaksi ${data['idTransaksi']} akan segera tersedia.',
    );
  }

  void _navigateToOrderTracking(Map<String, dynamic> data) {
    // TODO: Navigate to order tracking page
    _showInfoDialog(
      'Status Pengiriman',
      'Navigasi ke halaman tracking pesanan akan segera tersedia.',
    );
  }

  void _navigateToPenitipanDetail(Map<String, dynamic> data) {
    // TODO: Navigate to penitipan detail page
    _showInfoDialog(
      'Detail Penitipan',
      'Navigasi ke halaman detail penitipan akan segera tersedia.',
    );
  }

  void _navigateToTransactionDetail(Map<String, dynamic> data) {
    // TODO: Navigate to transaction detail page
    _showInfoDialog(
      'Detail Transaksi',
      'Navigasi ke halaman detail transaksi akan segera tersedia.',
    );
  }

  void _navigateToDonationDetail(Map<String, dynamic> data) {
    // TODO: Navigate to donation detail page
    _showInfoDialog(
      'Detail Donasi',
      'Navigasi ke halaman detail donasi akan segera tersedia.',
    );
  }

  void _markAllAsRead() async {
    await WebSocketService().markAllNotificationsAsRead();
    _loadNotifications();
    _showSuccessSnackBar('Semua notifikasi ditandai sudah dibaca');
  }

  void _handleTestNotification(String testType) async {
    switch (testType) {
      case 'test_pengiriman':
        await WebSocketService().testJadwalPengirimanNotification();
        break;
      case 'test_pengambilan':
        await WebSocketService().testJadwalPengambilanNotification();
        break;
      case 'test_all':
        await WebSocketService().testAllNotificationTypes();
        break;
    }

    // Reload notifications after test
    await Future.delayed(const Duration(seconds: 1));
    _loadNotifications();
    _showSuccessSnackBar('Test notifikasi berhasil dikirim!');
  }

  void _showInfoDialog(String title, String message) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  void _showSuccessSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle, color: Colors.white),
            const SizedBox(width: 8),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: AppConstants.primaryColor,
      ),
    );
  }

  void _showErrorSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.error, color: Colors.white),
            const SizedBox(width: 8),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: AppConstants.errorColor,
      ),
    );
  }
}
