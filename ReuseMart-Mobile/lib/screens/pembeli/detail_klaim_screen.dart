// lib/screens/detail_klaim_screen.dart
import 'package:flutter/material.dart';
import '../../models/merchandise.dart';
import '../../services/merchandise_service.dart';
import '../../constants/app_constants.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/error_widget.dart' as custom;
import '../../widgets/status_badge_widget.dart';

class DetailKlaimScreen extends StatefulWidget {
  final int idPenukaran;

  const DetailKlaimScreen({Key? key, required this.idPenukaran})
    : super(key: key);

  @override
  State<DetailKlaimScreen> createState() => _DetailKlaimScreenState();
}

class _DetailKlaimScreenState extends State<DetailKlaimScreen> {
  DetailKlaim? detailKlaim;
  bool isLoading = true;
  String? errorMessage;

  @override
  void initState() {
    super.initState();
    _loadDetailKlaim();
  }

  Future<void> _loadDetailKlaim() async {
    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    final response = await MerchandiseService.getDetailKlaim(
      widget.idPenukaran,
    );

    setState(() {
      isLoading = false;
      if (response.success && response.data != null) {
        detailKlaim = response.data!;
      } else {
        errorMessage = response.message;
      }
    });
  }

  String _formatDate(String dateString) {
    try {
      final date = DateTime.parse(dateString);
      return '${date.day}/${date.month}/${date.year} ${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
    } catch (e) {
      return dateString;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Klaim'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            onPressed: _loadDetailKlaim,
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadDetailKlaim,
        child: _buildContent(),
      ),
    );
  }

  Widget _buildContent() {
    if (isLoading) {
      return const LoadingWidget(message: 'Memuat detail klaim...');
    }

    if (errorMessage != null) {
      return custom.ErrorWidget(
        message: errorMessage!,
        onRetry: _loadDetailKlaim,
      );
    }

    if (detailKlaim == null) {
      return const custom.ErrorWidget(message: 'Data klaim tidak ditemukan');
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppConstants.paddingMedium),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Card merchandise
          Card(
            elevation: 4,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
            ),
            child: Padding(
              padding: const EdgeInsets.all(AppConstants.paddingMedium),
              child: Row(
                children: [
                  // Gambar merchandise
                  ClipRRect(
                    borderRadius: BorderRadius.circular(
                      AppConstants.radiusMedium,
                    ),
                    child: Image.asset(
                      detailKlaim!.merchandise.gambar,
                      width: 80,
                      height: 80,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) {
                        return Container(
                          width: 80,
                          height: 80,
                          color: Colors.grey.shade200,
                          child: const Icon(
                            Icons.card_giftcard,
                            size: 40,
                            color: Colors.grey,
                          ),
                        );
                      },
                    ),
                  ),

                  const SizedBox(width: 16),

                  // Informasi merchandise
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          detailKlaim!.merchandise.nama,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: AppConstants.textPrimaryColor,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            const Icon(
                              Icons.monetization_on,
                              size: 18,
                              color: AppConstants.primaryColor,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              '${detailKlaim!.merchandise.jumlahPoin} poin',
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: AppConstants.primaryColor,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        StatusBadgeWidget(
                          status: detailKlaim!.statusPenukaran,
                          label: detailKlaim!.statusLabel,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 20),

          // Informasi klaim
          const Text(
            'Informasi Klaim',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: AppConstants.textPrimaryColor,
            ),
          ),
          const SizedBox(height: 12),

          Card(
            elevation: 2,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
            ),
            child: Padding(
              padding: const EdgeInsets.all(AppConstants.paddingMedium),
              child: Column(
                children: [
                  _buildInfoRow(
                    'ID Penukaran',
                    '#${detailKlaim!.idPenukaran}',
                    Icons.receipt,
                  ),
                  const Divider(),
                  _buildInfoRow(
                    'Tanggal Pengajuan',
                    _formatDate(detailKlaim!.tanggalPengajuan),
                    Icons.schedule,
                  ),
                  if (detailKlaim!.tanggalPenerimaan != null) ...[
                    const Divider(),
                    _buildInfoRow(
                      'Tanggal Penerimaan',
                      _formatDate(detailKlaim!.tanggalPenerimaan!),
                      Icons.check_circle,
                      valueColor: Colors.green,
                    ),
                  ],
                ],
              ),
            ),
          ),

          const SizedBox(height: 20),

          // Catatan/Instruksi
          if (detailKlaim!.catatan.isNotEmpty) ...[
            const Text(
              'Catatan',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: AppConstants.textPrimaryColor,
              ),
            ),
            const SizedBox(height: 12),

            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
              ),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(AppConstants.paddingMedium),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(
                    AppConstants.radiusMedium,
                  ),
                  color: _getCatatanBackgroundColor(),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          _getCatatanIcon(),
                          color: _getCatatanIconColor(),
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          _getCatatanTitle(),
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: _getCatatanIconColor(),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      detailKlaim!.catatan,
                      style: TextStyle(
                        fontSize: 14,
                        color: _getCatatanTextColor(),
                        height: 1.4,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildInfoRow(
    String label,
    String value,
    IconData icon, {
    Color? valueColor,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(icon, size: 20, color: AppConstants.textSecondaryColor),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppConstants.textSecondaryColor,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: valueColor ?? AppConstants.textPrimaryColor,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Color _getCatatanBackgroundColor() {
    switch (detailKlaim!.statusPenukaran) {
      case 'belum diambil':
        return Colors.blue.shade50;
      case 'sudah diambil':
        return Colors.green.shade50;
      case 'dibatalkan':
        return Colors.red.shade50;
      default:
        return Colors.grey.shade50;
    }
  }

  Color _getCatatanIconColor() {
    switch (detailKlaim!.statusPenukaran) {
      case 'belum diambil':
        return Colors.blue.shade700;
      case 'sudah diambil':
        return Colors.green.shade700;
      case 'dibatalkan':
        return Colors.red.shade700;
      default:
        return Colors.grey.shade700;
    }
  }

  Color _getCatatanTextColor() {
    switch (detailKlaim!.statusPenukaran) {
      case 'belum diambil':
        return Colors.blue.shade800;
      case 'sudah diambil':
        return Colors.green.shade800;
      case 'dibatalkan':
        return Colors.red.shade800;
      default:
        return Colors.grey.shade800;
    }
  }

  IconData _getCatatanIcon() {
    switch (detailKlaim!.statusPenukaran) {
      case 'belum diambil':
        return Icons.info;
      case 'sudah diambil':
        return Icons.check_circle;
      case 'dibatalkan':
        return Icons.cancel;
      default:
        return Icons.help;
    }
  }

  String _getCatatanTitle() {
    switch (detailKlaim!.statusPenukaran) {
      case 'belum diambil':
        return 'Instruksi Pengambilan';
      case 'sudah diambil':
        return 'Terima Kasih';
      case 'dibatalkan':
        return 'Informasi Pembatalan';
      default:
        return 'Informasi';
    }
  }
}
