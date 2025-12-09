import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../constants/app_constants.dart';
import '../../../providers/auth_provider.dart';
import '../../../services/hunter_service.dart';
import '../../../models/hunter_profile.dart';
import '../../../models/history_komisi.dart';
import 'history_komisi_screen.dart';

class HunterProfileScreen extends StatefulWidget {
  const HunterProfileScreen({Key? key}) : super(key: key);

  @override
  State<HunterProfileScreen> createState() => _HunterProfileScreenState();
}

class _HunterProfileScreenState extends State<HunterProfileScreen> {
  HunterProfile? hunterProfile;
  HunterStats? hunterStats;
  bool isLoading = true;
  String? error;

  @override
  void initState() {
    super.initState();
    _loadHunterData();
  }

  Future<void> _loadHunterData() async {
    if (!mounted) return;

    try {
      setState(() {
        isLoading = true;
        error = null;
      });

      // Load profile dan stats secara parallel
      final profileFuture = HunterService.getProfile();
      final statsFuture = HunterService.getStats();

      final results = await Future.wait([profileFuture, statsFuture]);

      if (!mounted) return;

      setState(() {
        hunterProfile = results[0] as HunterProfile;
        hunterStats = results[1] as HunterStats;
        isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        error = e.toString();
        isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, child) {
        return Scaffold(
          backgroundColor: AppConstants.backgroundColor,
          appBar: AppBar(
            title: const Text('Profil Hunter'),
            backgroundColor: AppConstants.primaryColor,
            foregroundColor: Colors.white,
            automaticallyImplyLeading: false,
            actions: [
              IconButton(
                icon: const Icon(Icons.refresh),
                onPressed: _loadHunterData,
              ),
            ],
          ),
          body: _buildBody(),
        );
      },
    );
  }

  Widget _buildBody() {
    if (isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 64, color: Colors.red[300]),
            const SizedBox(height: 16),
            const Text('Terjadi Kesalahan'),
            const SizedBox(height: 8),
            Text(error!),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _loadHunterData,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (hunterProfile == null) {
      return const Center(child: Text('Data hunter tidak ditemukan'));
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          _buildProfileHeader(),
          const SizedBox(height: 20),
          if (hunterStats != null) _buildStatsCards(),
          const SizedBox(height: 20),
          _buildMenuItems(),
        ],
      ),
    );
  }

  Widget _buildProfileHeader() {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            CircleAvatar(
              radius: 40,
              backgroundColor: AppConstants.primaryColor,
              child: Text(
                hunterProfile!.nama.isNotEmpty
                    ? hunterProfile!.nama[0].toUpperCase()
                    : 'H',
                style: const TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              hunterProfile!.nama,
              style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              hunterProfile!.email,
              style: TextStyle(fontSize: 16, color: Colors.grey[600]),
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: AppConstants.primaryColor,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                hunterProfile!.jabatan,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            if (hunterProfile!.joinDate != null) ...[
              const SizedBox(height: 8),
              Text(
                'Bergabung: ${_formatDate(hunterProfile!.joinDate!)}',
                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatsCards() {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Total Komisi',
                'Rp ${_formatCurrency(hunterProfile!.totalKomisi)}',
                Icons.account_balance_wallet,
                Colors.green,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildStatCard(
                'Bulan Ini',
                'Rp ${_formatCurrency(hunterProfile!.komisiBulanIni)}',
                Icons.calendar_month,
                Colors.blue,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Transaksi',
                '${hunterProfile!.totalTransaksi}',
                Icons.shopping_bag,
                Colors.orange,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildStatCard(
                'Hari Ini',
                'Rp ${_formatCurrency(hunterStats!.komisiHariIni)}',
                Icons.today,
                Colors.purple,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatCard(
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Icon(icon, color: color, size: 32),
            const SizedBox(height: 8),
            Text(
              value,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            Text(
              title,
              style: TextStyle(fontSize: 12, color: Colors.grey[600]),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMenuItems() {
    return Column(
      children: [
        _buildMenuItem(
          'History Komisi',
          'Lihat riwayat komisi yang didapat',
          Icons.history,
          () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => const HistoryKomisiScreen(),
              ),
            );
          },
        ),
        _buildMenuItem(
          'Kontak',
          hunterProfile!.noTelp ?? 'Belum ada nomor',
          Icons.phone,
          null,
        ),
        _buildMenuItem(
          'Alamat',
          hunterProfile!.alamat ?? 'Belum ada alamat',
          Icons.location_on,
          null,
        ),
      ],
    );
  }

  Widget _buildMenuItem(
    String title,
    String subtitle,
    IconData icon,
    VoidCallback? onTap,
  ) {
    return Card(
      elevation: 1,
      margin: const EdgeInsets.only(bottom: 8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: AppConstants.primaryColor.withOpacity(0.1),
          child: Icon(icon, color: AppConstants.primaryColor),
        ),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.w500)),
        subtitle: Text(subtitle),
        trailing: onTap != null ? const Icon(Icons.chevron_right) : null,
        onTap: onTap,
      ),
    );
  }

  String _formatCurrency(double amount) {
    return amount
        .toStringAsFixed(0)
        .replaceAllMapped(
          RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
          (Match m) => '${m[1]}.',
        );
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      final months = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'Mei',
        'Jun',
        'Jul',
        'Agu',
        'Sep',
        'Okt',
        'Nov',
        'Des',
      ];
      return '${date.day} ${months[date.month - 1]} ${date.year}';
    } catch (e) {
      return dateStr;
    }
  }
}
