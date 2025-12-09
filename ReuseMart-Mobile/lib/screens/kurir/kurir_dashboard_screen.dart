import 'package:flutter/material.dart';
import '../../constants/app_constants.dart';
import '../../models/kurir.dart';
import '../../services/kurir_service.dart';
import 'package:intl/intl.dart';

class KurirDashboardScreen extends StatefulWidget {
  const KurirDashboardScreen({super.key});

  @override
  State<KurirDashboardScreen> createState() => _KurirDashboardScreenState();
}

class _KurirDashboardScreenState extends State<KurirDashboardScreen> {
  KurirStats? _kurirStats;
  List<TugasPengiriman> _tugasHariIni = [];
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadDashboardData();
  }

  Future<void> _loadDashboardData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final futures = await Future.wait([
        KurirService.getKurirStats(),
        KurirService.getTugasHariIni(),
      ]);

      if (mounted) {
        setState(() {
          _kurirStats = futures[0] as KurirStats;
          _tugasHariIni = futures[1] as List<TugasPengiriman>;
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
    if (_isLoading) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: AppConstants.paddingMedium),
            Text('Memuat dashboard...'),
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
              'Gagal memuat dashboard',
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
              onPressed: _loadDashboardData,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadDashboardData,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(AppConstants.paddingMedium),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildWelcomeCard(),
            const SizedBox(height: AppConstants.paddingLarge),
            _buildStatsSection(),
            const SizedBox(height: AppConstants.paddingLarge),
            _buildTugasHariIniSection(),
          ],
        ),
      ),
    );
  }

  Widget _buildWelcomeCard() {
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
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(AppConstants.paddingMedium),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.local_shipping,
                  size: 32,
                  color: Colors.white,
                ),
              ),
              const SizedBox(width: AppConstants.paddingMedium),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Dashboard Kurir',
                      style: AppConstants.headingStyle.copyWith(
                        color: Colors.white,
                        fontSize: 20,
                      ),
                    ),
                    const SizedBox(height: AppConstants.paddingSmall),
                    Text(
                      DateFormat('EEEE, dd MMMM yyyy', 'id_ID')
                          .format(DateTime.now()),
                      style: AppConstants.bodyStyle.copyWith(
                        color: Colors.white70,
                      ),
                    ),
                  ],
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
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: AppConstants.paddingMedium,
          mainAxisSpacing: AppConstants.paddingMedium,
          childAspectRatio: 1.3,
          children: [
            _buildStatCard(
              'Hari Ini',
              _kurirStats!.pengirimanHariIni.toString(),
              Icons.today_outlined,
              AppConstants.primaryColor,
            ),
            _buildStatCard(
              'Dalam Proses',
              _kurirStats!.pengirimanDalamProses.toString(),
              Icons.access_time_outlined,
              Colors.orange,
            ),
            _buildStatCard(
              'Bulan Ini',
              _kurirStats!.pengirimanBulanIni.toString(),
              Icons.calendar_month_outlined,
              AppConstants.secondaryColor,
            ),
            _buildStatCard(
              'Total Selesai',
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
              fontSize: 24,
              fontWeight: FontWeight.bold,
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

  Widget _buildTugasHariIniSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Tugas Hari Ini', style: AppConstants.titleStyle),
            if (_tugasHariIni.isNotEmpty)
              Text(
                '${_tugasHariIni.length} tugas',
                style: AppConstants.captionStyle.copyWith(
                  color: AppConstants.primaryColor,
                  fontWeight: FontWeight.w500,
                ),
              ),
          ],
        ),
        const SizedBox(height: AppConstants.paddingMedium),
        if (_tugasHariIni.isEmpty)
          _buildEmptyTugasCard()
        else
          ..._tugasHariIni.take(3).map((tugas) => _buildTugasCard(tugas)),
        if (_tugasHariIni.length > 3) ...[
          const SizedBox(height: AppConstants.paddingMedium),
          Center(
            child: TextButton(
              onPressed: () {
                // Navigate to history tab (index 2)
                // This would be handled by parent widget
              },
              child: Text(
                'Lihat Semua Tugas (${_tugasHariIni.length})',
                style: AppConstants.bodyStyle.copyWith(
                  color: AppConstants.primaryColor,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildEmptyTugasCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppConstants.paddingLarge),
      decoration: BoxDecoration(
        color: AppConstants.surfaceColor,
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
        boxShadow: AppConstants.defaultShadow,
      ),
      child: Column(
        children: [
          Icon(
            Icons.assignment_turned_in_outlined,
            size: 48,
            color: AppConstants.textSecondaryColor,
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          Text(
            'Tidak Ada Tugas Hari Ini',
            style: AppConstants.bodyStyle.copyWith(fontWeight: FontWeight.w500),
          ),
          const SizedBox(height: AppConstants.paddingSmall),
          Text(
            'Belum ada tugas pengiriman untuk hari ini',
            style: AppConstants.captionStyle.copyWith(
              color: AppConstants.textSecondaryColor,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildTugasCard(TugasPengiriman tugas) {
    return Container(
      margin: const EdgeInsets.only(bottom: AppConstants.paddingMedium),
      decoration: BoxDecoration(
        color: AppConstants.surfaceColor,
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
        boxShadow: AppConstants.defaultShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            decoration: BoxDecoration(
              color: _getStatusColor(tugas.status).withOpacity(0.1),
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(AppConstants.radiusMedium),
                topRight: Radius.circular(AppConstants.radiusMedium),
              ),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Nota: ${tugas.nomorNota}',
                        style: AppConstants.titleStyle.copyWith(fontSize: 16),
                      ),
                      const SizedBox(height: AppConstants.paddingSmall / 2),
                      Text(
                        tugas.namaPembeli,
                        style: AppConstants.bodyStyle.copyWith(
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: AppConstants.paddingMedium,
                    vertical: AppConstants.paddingSmall,
                  ),
                  decoration: BoxDecoration(
                    color: _getStatusColor(tugas.status),
                    borderRadius:
                        BorderRadius.circular(AppConstants.radiusSmall),
                  ),
                  child: Text(
                    tugas.statusDisplayName,
                    style: AppConstants.captionStyle.copyWith(
                      color: Colors.white,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Content
          Padding(
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Alamat
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.location_on_outlined,
                      color: AppConstants.primaryColor,
                      size: 20,
                    ),
                    const SizedBox(width: AppConstants.paddingSmall),
                    Expanded(
                      child: Text(
                        tugas.alamatPengiriman,
                        style: AppConstants.bodyStyle,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: AppConstants.paddingMedium),

                // Items summary
                Row(
                  children: [
                    Icon(
                      Icons.inventory_outlined,
                      color: AppConstants.textSecondaryColor,
                      size: 16,
                    ),
                    const SizedBox(width: AppConstants.paddingSmall),
                    Text(
                      '${tugas.items.length} item',
                      style: AppConstants.captionStyle,
                    ),
                    const Spacer(),
                    Text(
                      'Rp ${NumberFormat.currency(locale: 'id', symbol: '', decimalDigits: 0).format(tugas.totalHarga)}',
                      style: AppConstants.bodyStyle.copyWith(
                        fontWeight: FontWeight.w500,
                        color: AppConstants.primaryColor,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'disiapkan':
        return Colors.orange;
      case 'kirim':
        return Colors.blue;
      case 'terjual':
        return Colors.green;
      default:
        return AppConstants.textSecondaryColor;
    }
  }
}
