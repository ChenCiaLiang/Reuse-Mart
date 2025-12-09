import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_constants.dart';
import '../../providers/auth_provider.dart';
import '../../models/kurir.dart';
import '../../services/kurir_service.dart';
import 'package:intl/intl.dart';

class KurirProfileScreen extends StatefulWidget {
  const KurirProfileScreen({super.key});

  @override
  State<KurirProfileScreen> createState() => _KurirProfileScreenState();
}

class _KurirProfileScreenState extends State<KurirProfileScreen> {
  KurirProfile? _kurirProfile;
  KurirStats? _kurirStats;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadKurirProfile();
  }

  Future<void> _loadKurirProfile() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final profile = await KurirService.getKurirProfile();
      final stats = await KurirService.getKurirStats();

      if (mounted) {
        setState(() {
          _kurirProfile = profile;
          _kurirStats = stats;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: AppBar(
        title: const Text('Profil Kurir'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadKurirProfile,
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: AppConstants.paddingMedium),
            Text('Memuat profil kurir...'),
          ],
        ),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.error_outline,
              size: 64,
              color: AppConstants.errorColor,
            ),
            const SizedBox(height: AppConstants.paddingMedium),
            Text(
              'Gagal memuat profil',
              style: AppConstants.titleStyle,
            ),
            const SizedBox(height: AppConstants.paddingSmall),
            Padding(
              padding: const EdgeInsets.symmetric(
                  horizontal: AppConstants.paddingLarge),
              child: Text(
                _errorMessage!,
                style: AppConstants.bodyStyle.copyWith(
                  color: AppConstants.textSecondaryColor,
                ),
                textAlign: TextAlign.center,
              ),
            ),
            const SizedBox(height: AppConstants.paddingLarge),
            ElevatedButton(
              onPressed: _loadKurirProfile,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (_kurirProfile == null) {
      return const Center(child: Text('Data profil tidak tersedia'));
    }

    return RefreshIndicator(
      onRefresh: _loadKurirProfile,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(AppConstants.paddingMedium),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildProfileHeader(),
            const SizedBox(height: AppConstants.paddingLarge),
            _buildStatsSection(),
            const SizedBox(height: AppConstants.paddingLarge),
            _buildPersonalInfoSection(),
            const SizedBox(height: AppConstants.paddingLarge),
            _buildActionButtons(),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileHeader() {
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
        children: [
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.local_shipping,
              size: 40,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          Text(
            _kurirProfile!.nama,
            style: AppConstants.headingStyle.copyWith(color: Colors.white),
            textAlign: TextAlign.center,
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
              _kurirProfile!.jabatan,
              style: AppConstants.bodyStyle.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.calendar_today,
                size: 16,
                color: Colors.white70,
              ),
              const SizedBox(width: AppConstants.paddingSmall),
              Text(
                'Bergabung: ${_kurirProfile!.joinDate != null ? DateFormat('dd MMM yyyy').format(_kurirProfile!.joinDate!) : 'N/A'}',
                style: AppConstants.bodyStyle.copyWith(
                  color: Colors.white70,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatsSection() {
    if (_kurirStats == null) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Statistik Pengiriman', style: AppConstants.titleStyle),
        const SizedBox(height: AppConstants.paddingMedium),

        // Stats cards in grid
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: AppConstants.paddingMedium,
          mainAxisSpacing: AppConstants.paddingMedium,
          childAspectRatio: 1.5,
          children: [
            _buildStatCard(
              'Total Pengiriman',
              _kurirStats!.totalPengiriman.toString(),
              Icons.local_shipping_outlined,
              AppConstants.primaryColor,
            ),
            _buildStatCard(
              'Hari Ini',
              _kurirStats!.pengirimanHariIni.toString(),
              Icons.today_outlined,
              AppConstants.secondaryColor,
            ),
            _buildStatCard(
              'Bulan Ini',
              _kurirStats!.pengirimanBulanIni.toString(),
              Icons.calendar_month_outlined,
              AppConstants.accentColor,
            ),
            _buildStatCard(
              'Selesai',
              _kurirStats!.pengirimanSelesai.toString(),
              Icons.check_circle_outline,
              Colors.green,
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatCard(
      String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(AppConstants.paddingMedium),
      decoration: BoxDecoration(
        color: AppConstants.surfaceColor,
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
        boxShadow: AppConstants.defaultShadow,
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: color, size: 32),
          const SizedBox(height: AppConstants.paddingSmall),
          Text(
            value,
            style: AppConstants.titleStyle.copyWith(
              color: color,
              fontSize: 20,
            ),
          ),
          const SizedBox(height: AppConstants.paddingSmall / 2),
          Text(
            title,
            style: AppConstants.captionStyle,
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildPersonalInfoSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Informasi Personal', style: AppConstants.titleStyle),
        const SizedBox(height: AppConstants.paddingMedium),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(AppConstants.paddingLarge),
          decoration: BoxDecoration(
            color: AppConstants.surfaceColor,
            borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
            boxShadow: AppConstants.defaultShadow,
          ),
          child: Column(
            children: [
              _buildInfoItem(
                Icons.email_outlined,
                'Email',
                _kurirProfile!.email,
              ),
              const Divider(),
              _buildInfoItem(
                Icons.phone_outlined,
                'No. Telepon',
                _kurirProfile!.noTelp,
              ),
              const Divider(),
              _buildInfoItem(
                Icons.location_on_outlined,
                'Alamat',
                _kurirProfile!.alamat,
              ),
              const Divider(),
              _buildInfoItem(
                Icons.cake_outlined,
                'Tanggal Lahir',
                DateFormat('dd MMMM yyyy').format(_kurirProfile!.tanggalLahir),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildInfoItem(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: AppConstants.paddingSmall),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: AppConstants.primaryColor, size: 20),
          const SizedBox(width: AppConstants.paddingMedium),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: AppConstants.captionStyle.copyWith(
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: AppConstants.paddingSmall / 2),
                Text(
                  value,
                  style: AppConstants.bodyStyle,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons() {
    return Column(
      children: [
        // Logout button
        SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: _showLogoutDialog,
            icon: const Icon(Icons.logout),
            label: const Text('Keluar'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppConstants.errorColor,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(
                  vertical: AppConstants.paddingMedium),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
              ),
            ),
          ),
        ),
      ],
    );
  }

  void _showLogoutDialog() {
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
              final authProvider =
                  Provider.of<AuthProvider>(context, listen: false);
              await authProvider.logout();
            },
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
  }
}
