import 'package:flutter/material.dart';
import 'package:p3l_mobile/services/awsome_notification_services.dart';
import 'package:provider/provider.dart';
import '../../../constants/app_constants.dart';
import '../../../providers/auth_provider.dart';
import '../informasi/informasi_umum_screen.dart';
import '../produk/produk_screen.dart';
import '../pembeli/profile_screen.dart';
import '../pembeli/history_screen.dart';
import '../penitip/penitip_profile_screen.dart';
import '../penitip/penitip_history_screen.dart';
import '../hunter/hunter_profile_screen.dart';
import '../hunter/history_komisi_screen.dart';
import '../kurir/kurir_history_screen.dart'; // ✅ Import kurir history
import '../kurir/kurir_profile_screen.dart'; // ✅ Import kurir profile
import '../../services/top_seller_service.dart';
import '../../models/top_seller.dart';
import '../../widgets/top_seller_card.dart';
import '../pembeli/katalogMerchandise_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentBottomNavIndex = 0;
  TopSeller? _currentTopSeller;
  bool _isLoadingTopSeller = false;

  @override
  void initState() {
    super.initState();
    _loadTopSeller();
  }

  Future<void> _loadTopSeller() async {
    setState(() {
      _isLoadingTopSeller = true;
    });

    try {
      final topSeller = await TopSellerService.getCurrentTopSeller();
      if (mounted) {
        setState(() {
          _currentTopSeller = topSeller;
          _isLoadingTopSeller = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoadingTopSeller = false;
        });
      }
      print('Error loading top seller: $e');
    }
  }

  Widget _buildTopSellerSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text('🏆 Top Seller', style: AppConstants.titleStyle),
            const Spacer(),
            if (!_isLoadingTopSeller)
              IconButton(
                onPressed: _loadTopSeller,
                icon: Icon(
                  Icons.refresh,
                  color: AppConstants.textSecondaryColor,
                  size: 20,
                ),
                tooltip: 'Refresh',
              ),
          ],
        ),
        const SizedBox(height: AppConstants.paddingMedium),
        if (_isLoadingTopSeller)
          _buildLoadingTopSeller()
        else if (_currentTopSeller != null)
          TopSellerCard(topSeller: _currentTopSeller!)
        else
          _buildNoTopSeller(),
      ],
    );
  }

  Widget _buildLoadingTopSeller() {
    return Container(
      width: double.infinity,
      height: 120,
      decoration: BoxDecoration(
        color: AppConstants.surfaceColor,
        borderRadius: BorderRadius.circular(AppConstants.radiusLarge),
        boxShadow: AppConstants.defaultShadow,
      ),
      child: const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: AppConstants.paddingSmall),
            Text('Memuat Top Seller...'),
          ],
        ),
      ),
    );
  }

  Widget _buildNoTopSeller() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppConstants.paddingLarge),
      decoration: BoxDecoration(
        color: AppConstants.surfaceColor,
        borderRadius: BorderRadius.circular(AppConstants.radiusLarge),
        boxShadow: AppConstants.defaultShadow,
      ),
      child: Column(
        children: [
          Icon(
            Icons.emoji_events_outlined,
            size: 48,
            color: AppConstants.textSecondaryColor,
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          Text(
            'Belum Ada Top Seller',
            style: AppConstants.bodyStyle.copyWith(fontWeight: FontWeight.w500),
          ),
          const SizedBox(height: AppConstants.paddingSmall),
          Text(
            'Top seller bulan ini belum ditentukan',
            style: AppConstants.captionStyle.copyWith(
              color: AppConstants.textSecondaryColor,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, child) {
        final user = authProvider.user;

        if (user == null) {
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
        }

        // ✅ PERBAIKAN: Samakan semua role menggunakan pattern yang sama
        if (user.role == 'pembeli') {
          return _buildPembeliScreen(authProvider);
        } else if (user.role == 'penitip') {
          return _buildPenitipScreen(authProvider);
        } else if (user.role == 'hunter') {
          return _buildHunterScreen(authProvider);
        } else if (user.role == 'kurir') {
          return _buildKurirScreen(authProvider);
        }

        return _buildNormalScreen(authProvider);
      },
    );
  }

  Widget _buildPembeliScreen(AuthProvider authProvider) {
    final List<Widget> screens = [
      _buildHomeContent(authProvider),
      ProdukScreen(),
      const KatalogMerchandiseScreen(),
      const HistoryScreen(),
      const ProfileScreen(),
    ];

    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: _buildAppBar(authProvider),
      body: IndexedStack(index: _currentBottomNavIndex, children: screens),
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
            icon: Icon(Icons.home_outlined),
            activeIcon: Icon(Icons.home),
            label: 'Beranda',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.shopping_bag_outlined),
            activeIcon: Icon(Icons.shopping_bag),
            label: 'Produk',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.card_giftcard_outlined),
            activeIcon: Icon(Icons.card_giftcard),
            label: 'Merchandise',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.history_outlined),
            activeIcon: Icon(Icons.history),
            label: 'Riwayat',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_outlined),
            activeIcon: Icon(Icons.person),
            label: 'Profil',
          ),
        ],
      ),
    );
  }

  Widget _buildPenitipScreen(AuthProvider authProvider) {
    final List<Widget> screens = [
      _buildHomeContent(authProvider),
      ProdukScreen(),
      const PenitipHistoryScreen(),
      const PenitipProfileScreen(),
    ];

    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: _buildAppBar(authProvider),
      body: IndexedStack(index: _currentBottomNavIndex, children: screens),
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
            icon: Icon(Icons.home_outlined),
            activeIcon: Icon(Icons.home),
            label: 'Beranda',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.shopping_bag_outlined),
            activeIcon: Icon(Icons.shopping_bag),
            label: 'Produk',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.history_outlined),
            activeIcon: Icon(Icons.history),
            label: 'Riwayat',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_outlined),
            activeIcon: Icon(Icons.person),
            label: 'Profil',
          ),
        ],
      ),
    );
  }

  Widget _buildHunterScreen(AuthProvider authProvider) {
    final List<Widget> screens = [
      _buildHomeContent(authProvider),
      ProdukScreen(),
      const HistoryKomisiScreen(),
      const HunterProfileScreen(),
    ];

    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: _buildAppBar(authProvider),
      body: IndexedStack(index: _currentBottomNavIndex, children: screens),
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
            icon: Icon(Icons.home_outlined),
            activeIcon: Icon(Icons.home),
            label: 'Beranda',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.shopping_bag_outlined),
            activeIcon: Icon(Icons.shopping_bag),
            label: 'Produk',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.money_outlined),
            activeIcon: Icon(Icons.money),
            label: 'Komisi',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_outlined),
            activeIcon: Icon(Icons.person),
            label: 'Profil',
          ),
        ],
      ),
    );
  }

  // ✅ KURIR SCREEN - Implementasi tanpa tab Produk
  Widget _buildKurirScreen(AuthProvider authProvider) {
    final List<Widget> screens = [
      _buildHomeContent(authProvider),
      const KurirHistoryScreen(), // History tugas pengiriman
      const KurirProfileScreen(), // Profile kurir
    ];

    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: _buildAppBar(authProvider),
      body: IndexedStack(index: _currentBottomNavIndex, children: screens),
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
            icon: Icon(Icons.home_outlined),
            activeIcon: Icon(Icons.home),
            label: 'Beranda',
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
  }

  Widget _buildNormalScreen(AuthProvider authProvider) {
    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: _buildAppBar(authProvider),
      body: _buildHomeContent(authProvider),
      bottomNavigationBar: _buildOldBottomNavigation(authProvider.user!.role),
    );
  }

  AppBar _buildAppBar(AuthProvider authProvider) {
    return AppBar(
      title: const Text('ReuseMart'),
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
        IconButton(
          icon: const Icon(Icons.notifications_active),
          onPressed: () async {
            try {
              await NotificationService().showNotification(
                title: 'Test Notification',
                body: 'P3L susah wak! 🚀',
              );

              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('✅ Test notification berhasil dikirim!'),
                  backgroundColor: AppConstants.primaryColor,
                ),
              );
            } catch (e) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('❌ Error: $e'),
                  backgroundColor: AppConstants.errorColor,
                ),
              );
            }
          },
        ),
        PopupMenuButton<String>(
          onSelected: (value) {
            switch (value) {
              case 'profile':
                _showProfile(context, authProvider);
                break;
              case 'logout':
                _showLogoutDialog(context, authProvider);
                break;
            }
          },
          itemBuilder: (BuildContext context) => [
            const PopupMenuItem<String>(
              value: 'profile',
              child: Row(
                children: [
                  Icon(Icons.person_outlined),
                  SizedBox(width: 8),
                  Text('Profil'),
                ],
              ),
            ),
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

  Widget _buildHomeContent(AuthProvider authProvider) {
    final user = authProvider.user!;

    return SafeArea(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(AppConstants.paddingMedium),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildWelcomeCard(user.nama, authProvider.getRoleDisplayName()),
            const SizedBox(height: AppConstants.paddingLarge),

            // Tambahkan Top Seller Section di sini
            _buildTopSellerSection(),
            const SizedBox(height: AppConstants.paddingLarge),

            _buildRoleSpecificContent(user.role),
            const SizedBox(height: AppConstants.paddingLarge),
            _buildQuickActions(context, user.role),
          ],
        ),
      ),
    );
  }

  Widget _buildWelcomeCard(String userName, String userRole) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppConstants.paddingLarge),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppConstants.primaryColor, AppConstants.secondaryColor],
        ),
        borderRadius: BorderRadius.circular(AppConstants.radiusLarge),
        boxShadow: AppConstants.defaultShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Selamat Datang,',
            style: AppConstants.bodyStyle.copyWith(color: Colors.white70),
          ),
          const SizedBox(height: AppConstants.paddingSmall),
          Text(
            userName,
            style: AppConstants.headingStyle.copyWith(color: Colors.white),
          ),
          const SizedBox(height: AppConstants.paddingSmall),
          Container(
            padding: const EdgeInsets.symmetric(
              horizontal: AppConstants.paddingMedium,
              vertical: AppConstants.paddingSmall,
            ),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
            ),
            child: Text(
              userRole,
              style: AppConstants.bodyStyle.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRoleSpecificContent(String role) {
    switch (role) {
      case 'pembeli':
        return _buildPembeliContent();
      case 'penitip':
        return _buildPenitipContent();
      case 'hunter':
        return _buildHunterContent();
      case 'kurir':
        return _buildKurirContent();
      case 'organisasi':
        return _buildOrganisasiContent();
      default:
        return _buildEmployeeContent(role);
    }
  }

  Widget _buildPembeliContent() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Jelajahi Produk', style: AppConstants.titleStyle),
        const SizedBox(height: AppConstants.paddingMedium),
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: AppConstants.paddingMedium,
          mainAxisSpacing: AppConstants.paddingMedium,
          childAspectRatio: 1.2,
          children: [
            _buildCategoryCard(
              'Elektronik',
              Icons.devices_outlined,
              AppConstants.primaryColor,
              () => _goToProductTab(),
            ),
            _buildCategoryCard(
              'Furniture',
              Icons.chair_outlined,
              AppConstants.secondaryColor,
              () => _goToProductTab(),
            ),
            _buildCategoryCard(
              'Fashion',
              Icons.checkroom_outlined,
              AppConstants.accentColor,
              () => _goToProductTab(),
            ),
            _buildCategoryCard(
              'Lainnya',
              Icons.category_outlined,
              AppConstants.primaryColor,
              () => _goToProductTab(),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildPenitipContent() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Dashboard Penitip', style: AppConstants.titleStyle),
        const SizedBox(height: AppConstants.paddingMedium),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Barang Aktif',
                '12',
                Icons.inventory_outlined,
              ),
            ),
            const SizedBox(width: AppConstants.paddingMedium),
            Expanded(
              child: _buildStatCard('Terjual', '8', Icons.sell_outlined),
            ),
          ],
        ),
        const SizedBox(height: AppConstants.paddingMedium),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Saldo',
                'Rp 2.500.000',
                Icons.account_balance_wallet_outlined,
              ),
            ),
            const SizedBox(width: AppConstants.paddingMedium),
            Expanded(
              child: _buildStatCard('Rating', '4.8 ⭐', Icons.star_outline),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildHunterContent() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Dashboard Hunter', style: AppConstants.titleStyle),
        const SizedBox(height: AppConstants.paddingMedium),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Total Komisi',
                'Rp 1.525.000',
                Icons.account_balance_wallet_outlined,
              ),
            ),
            const SizedBox(width: AppConstants.paddingMedium),
            Expanded(
              child: _buildStatCard(
                'Bulan Ini',
                'Rp 254.000',
                Icons.money_outlined,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppConstants.paddingMedium),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Transaksi',
                '7',
                Icons.shopping_bag_outlined,
              ),
            ),
            const SizedBox(width: AppConstants.paddingMedium),
            Expanded(
              child: _buildStatCard(
                'Hari Ini',
                'Rp 52.152',
                Icons.today_outlined,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildKurirContent() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Dashboard Kurir', style: AppConstants.titleStyle),
        const SizedBox(height: AppConstants.paddingMedium),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Tugas Hari Ini',
                '5',
                Icons.today_outlined,
              ),
            ),
            const SizedBox(width: AppConstants.paddingMedium),
            Expanded(
              child: _buildStatCard(
                'Dalam Proses',
                '3',
                Icons.local_shipping_outlined,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppConstants.paddingMedium),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Total Pengiriman',
                '150',
                Icons.check_circle_outline,
              ),
            ),
            const SizedBox(width: AppConstants.paddingMedium),
            Expanded(
              child: _buildStatCard(
                'Bulan Ini',
                '25',
                Icons.calendar_month_outlined,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildOrganisasiContent() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Request Donasi', style: AppConstants.titleStyle),
        const SizedBox(height: AppConstants.paddingMedium),
        _buildStatCard('Request Aktif', '3', Icons.volunteer_activism_outlined),
      ],
    );
  }

  Widget _buildEmployeeContent(String role) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Dashboard $role', style: AppConstants.titleStyle),
        const SizedBox(height: AppConstants.paddingMedium),
        Text(
          'Fitur khusus untuk $role akan tersedia di update selanjutnya.',
          style: AppConstants.bodyStyle.copyWith(
            color: AppConstants.textSecondaryColor,
          ),
        ),
      ],
    );
  }

  Widget _buildQuickActions(BuildContext context, String role) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Aksi Cepat', style: AppConstants.titleStyle),
        const SizedBox(height: AppConstants.paddingMedium),
        if (role == 'pembeli') ...[
          _buildActionButton(
            'Lihat Semua Produk',
            Icons.shopping_bag_outlined,
            () {
              _goToProductTab();
            },
          ),
          const SizedBox(height: AppConstants.paddingSmall),
          _buildActionButton('Riwayat Pembelian', Icons.history_outlined, () {
            _goToHistoryTab();
          }),
          const SizedBox(height: AppConstants.paddingSmall),
          _buildActionButton('Profil & Poin', Icons.person_outlined, () {
            _goToProfileTab();
          }),
        ] else if (role == 'penitip') ...[
          _buildActionButton('Lihat Produk', Icons.inventory_outlined, () {
            _goToProductTab();
          }),
          const SizedBox(height: AppConstants.paddingSmall),
          _buildActionButton('Riwayat Penjualan', Icons.sell_outlined, () {
            _goToHistoryTab();
          }),
          const SizedBox(height: AppConstants.paddingSmall),
          _buildActionButton('Profil & Saldo', Icons.person_outlined, () {
            _goToProfileTab();
          }),
        ] else if (role == 'hunter') ...[
          _buildActionButton('Lihat Produk', Icons.shopping_bag_outlined, () {
            _goToProductTab();
          }),
          const SizedBox(height: AppConstants.paddingSmall),
          _buildActionButton('History Komisi', Icons.money_outlined, () {
            _goToHistoryTab();
          }),
          const SizedBox(height: AppConstants.paddingSmall),
          _buildActionButton('Profil & Stats', Icons.person_outlined, () {
            _goToProfileTab();
          }),
        ] else if (role == 'kurir') ...[
          // ✅ Quick Actions untuk Kurir sama seperti Hunter
          _buildActionButton(
            'Tugas Pengiriman',
            Icons.local_shipping_outlined,
            () {
              _goToHistoryTab();
            },
          ),
          const SizedBox(height: AppConstants.paddingSmall),
          _buildActionButton('Profil & Stats', Icons.person_outlined, () {
            _goToProfileTab();
          }),
        ] else if (role == 'organisasi') ...[
          _buildActionButton(
            'Buat Request Donasi',
            Icons.add_circle_outline,
            () {},
          ),
        ],
      ],
    );
  }

  void _goToProductTab() {
    if (mounted) {
      setState(() {
        _currentBottomNavIndex = 1;
      });
    }
  }

  void _goToHistoryTab() {
    if (mounted) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      setState(() {
        // Untuk kurir, history ada di index 1, untuk role lain di index 2
        _currentBottomNavIndex = (user?.role == 'kurir') ? 1 : 2;
      });
    }
  }

  void _goToProfileTab() {
    if (mounted) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      setState(() {
        // Untuk kurir, profile ada di index 2, untuk role lain di index 3
        _currentBottomNavIndex = (user?.role == 'kurir') ? 2 : 3;
      });
    }
  }

  Widget _buildCategoryCard(
    String title,
    IconData icon,
    Color color, [
    VoidCallback? onTap,
  ]) {
    return Container(
      decoration: BoxDecoration(
        color: AppConstants.surfaceColor,
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
        boxShadow: AppConstants.defaultShadow,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.all(AppConstants.paddingMedium),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(icon, size: 32, color: color),
                ),
                const SizedBox(height: AppConstants.paddingSmall),
                Text(
                  title,
                  style: AppConstants.bodyStyle.copyWith(
                    fontWeight: FontWeight.w500,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(AppConstants.paddingMedium),
      decoration: BoxDecoration(
        color: AppConstants.surfaceColor,
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
        boxShadow: AppConstants.defaultShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: AppConstants.primaryColor, size: 20),
              const SizedBox(width: AppConstants.paddingSmall),
              Text(title, style: AppConstants.captionStyle),
            ],
          ),
          const SizedBox(height: AppConstants.paddingSmall),
          Text(value, style: AppConstants.titleStyle),
        ],
      ),
    );
  }

  Widget _buildActionButton(String title, IconData icon, VoidCallback onTap) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: AppConstants.surfaceColor,
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
        boxShadow: AppConstants.defaultShadow,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            child: Row(
              children: [
                Icon(icon, color: AppConstants.primaryColor),
                const SizedBox(width: AppConstants.paddingMedium),
                Text(
                  title,
                  style: AppConstants.bodyStyle.copyWith(
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const Spacer(),
                const Icon(
                  Icons.arrow_forward_ios,
                  size: 16,
                  color: AppConstants.textSecondaryColor,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget? _buildOldBottomNavigation(String role) {
    if (role == 'pembeli' ||
        role == 'penitip' ||
        role == 'hunter' ||
        role == 'kurir') {
      return null;
    }

    return BottomNavigationBar(
      type: BottomNavigationBarType.fixed,
      selectedItemColor: AppConstants.primaryColor,
      unselectedItemColor: AppConstants.textSecondaryColor,
      items: const [
        BottomNavigationBarItem(
          icon: Icon(Icons.home_outlined),
          label: 'Beranda',
        ),
        BottomNavigationBarItem(icon: Icon(Icons.work_outline), label: 'Kerja'),
      ],
    );
  }

  void _showProfile(BuildContext context, AuthProvider authProvider) {
    final user = authProvider.user!;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Profil'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Nama: ${user.nama}'),
            Text('Email: ${user.email}'),
            Text('Role: ${authProvider.getRoleDisplayName()}'),
            if (user.poin != null) Text('Poin: ${user.poin}'),
            if (user.saldo != null) Text('Saldo: Rp ${user.saldo}'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Tutup'),
          ),
        ],
      ),
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
