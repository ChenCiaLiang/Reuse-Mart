import 'package:flutter/material.dart';
import '../services/websocket_service.dart';

class NotificationList extends StatefulWidget {
  @override
  _NotificationListState createState() => _NotificationListState();
}

class _NotificationListState extends State<NotificationList> {
  List<Map<String, dynamic>> notifications = [];
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    try {
      final loadedNotifications = await WebSocketService().getLocalNotifications();
      setState(() {
        notifications = loadedNotifications;
        isLoading = false;
      });
    } catch (e) {
      setState(() {
        isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Center(child: CircularProgressIndicator());
    }

    if (notifications.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.notifications_none, size: 64, color: Colors.grey),
            Text('Belum ada notifikasi', style: TextStyle(color: Colors.grey)),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadNotifications,
      child: ListView.builder(
        itemCount: notifications.length,
        itemBuilder: (context, index) {
          final notification = notifications[index];
          final isRead = notification['isRead'] ?? false;
          
          return Card(
            margin: EdgeInsets.symmetric(horizontal: 16, vertical: 4),
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: _getNotificationColor(notification['type']),
                child: Icon(_getNotificationIcon(notification['type']), color: Colors.white),
              ),
              title: Text(
                notification['title'] ?? 'Notifikasi',
                style: TextStyle(
                  fontWeight: isRead ? FontWeight.normal : FontWeight.bold,
                ),
              ),
              subtitle: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(notification['message'] ?? ''),
                  SizedBox(height: 4),
                  Text(
                    _formatTimestamp(notification['timestamp']),
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                ],
              ),
              trailing: isRead ? null : Container(
                width: 8,
                height: 8,
                decoration: BoxDecoration(
                  color: Colors.blue,
                  shape: BoxShape.circle,
                ),
              ),
              onTap: () async {
                if (!isRead) {
                  await WebSocketService().markNotificationAsRead(index);
                  _loadNotifications();
                }
                _handleNotificationTap(notification);
              },
            ),
          );
        },
      ),
    );
  }

  Color _getNotificationColor(String? type) {
    switch (type) {
      case 'warning':
        return Colors.orange;
      case 'success':
        return Colors.green;
      case 'error':
        return Colors.red;
      default:
        return Colors.blue;
    }
  }

  IconData _getNotificationIcon(String? type) {
    switch (type) {
      case 'penitipan_expire':
        return Icons.schedule;
      case 'barang_laku':
        return Icons.shopping_cart;
      case 'jadwal_pengiriman':
        return Icons.local_shipping;
      case 'status_pengiriman':
        return Icons.track_changes;
      case 'barang_didonasikan':
        return Icons.favorite;
      default:
        return Icons.notifications;
    }
  }

  String _formatTimestamp(String? timestamp) {
    if (timestamp == null) return '';
    
    try {
      final dateTime = DateTime.parse(timestamp);
      final now = DateTime.now();
      final difference = now.difference(dateTime);
      
      if (difference.inDays > 0) {
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

  void _handleNotificationTap(Map<String, dynamic> notification) {
    final type = notification['data']?['type'];
    
    switch (type) {
      case 'penitipan_expire':
        // Navigate to penitipan detail
        Navigator.pushNamed(context, '/penitipan-detail', 
          arguments: notification['data']['produk_id']);
        break;
      case 'barang_laku':
        // Navigate to transaction detail
        Navigator.pushNamed(context, '/transaksi-detail',
          arguments: notification['data']['produk_id']);
        break;
      // Add more navigation cases
    }
  }
}