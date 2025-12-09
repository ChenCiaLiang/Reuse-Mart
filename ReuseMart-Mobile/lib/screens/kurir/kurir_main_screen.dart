import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_constants.dart';
import '../../providers/auth_provider.dart';
import '../informasi/informasi_umum_screen.dart';
import '../produk/produk_screen.dart';
import 'kurir_history_screen.dart';
import 'kurir_profile_screen.dart';
import 'kurir_dashboard_screen.dart';

class KurirMainScreen extends StatefulWidget {
  const KurirMainScreen({super.key});

  @override
  State<KurirMainScreen> createState() => _KurirMainScreenState();
}

class _KurirMainScreenState extends State<KurirMainScreen> {
  int _currentBottomNavIndex = 0;

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, child) {
        final user = authProvider.user;

        if (user == null || user.role != 'kurir') {
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
        }

        final List<Widget> screens = [
          const KurirDashboardScreen(), // Dashboard dengan ringkasan tugas
          ProdukScreen(), // Kurir bisa lihat produk
          const KurirHistoryScreen(), // History tugas pengiriman (Fungsionalitas 118 & 119)
          const KurirProfileScreen(), // Profile kurir (Fungsionalitas 117)
        ];

        return Scaffold(
          backgroundColor: AppConstants.backgroundColor,
          appBar: _buildAppBar(authProvider),
          body: IndexedStack(
            index: _currentBottomNavIndex,
            children: screens,
          ),
          bottomNavigationBar: BottomNavigationBar(
            type: BottomNavigationBarType.fixed,
            selectedItemColor: AppConstants.primaryColor,
            unselectedItemColor: AppConstants.textSecondaryColor,
            currentIndex: _currentBottomNavIndex,
            onTap: (index) {
              setState(() {
                _currentBottomNavIndex = index;
              });
            },
            items: const [
              BottomNavigationBarItem(
                icon: Icon(Icons.dashboard_outlined),
                activeIcon: Icon(Icons.dashboard),
                label: 'Dashboard',
              ),
              BottomNavigationBarItem(
                icon: Icon(Icons.shopping_bag_outlined),
                activeIcon: Icon(Icons.shopping_bag),
                label: 'Produk',
              ),
              BottomNavigationBarItem(
                icon: Icon(Icons.local_shipping_outlined),
                activeIcon: Icon(Icons.local_shipping),
                label: 'Tugas',
              ),
              BottomNavigationBarItem(
                icon: Icon(Icons.person_outlined),
                activeIcon: Icon(Icons.person),
                label: 'Profil',
              ),
            ],
          ),
        );
      },
    );
  }

  AppBar _buildAppBar(AuthProvider authProvider) {
    return AppBar(
      title: const Text('ReuseMart - Kurir'),
      backgroundColor: AppConstants.primaryColor,
      foregroundColor: Colors.white,
      actions: [
        IconButton(
          icon: const Icon(Icons.info_outline),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => const InformasiUmumScreen(),
              ),
            );
          },
          tooltip: 'Tentang ReUseMart',
        ),
        PopupMenuButton<String>(
          onSelected: (value) {
            switch (value) {
              case 'logout':
                _showLogoutDialog(context, authProvider);
                break;
            }
          },
          itemBuilder: (BuildContext context) => [
            const PopupMenuItem<String>(
              value: 'logout',
              child: Row(
                children: [
                  Icon(Icons.logout_outlined),
                  SizedBox(width: 8),
                  Text('Keluar'),
                ],
              ),
            ),
          ],
        ),
      ],
    );
  }

  void _showLogoutDialog(BuildContext context, AuthProvider authProvider) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Keluar'),
        content: const Text('Apakah Anda yakin ingin keluar dari aplikasi?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () async {
              Navigator.of(context).pop();
              await authProvider.logout();
            },
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
  }
}
