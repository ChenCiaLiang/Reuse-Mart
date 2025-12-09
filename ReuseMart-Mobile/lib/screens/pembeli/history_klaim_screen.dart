// lib/screens/history_klaim_screen.dart
import 'package:flutter/material.dart';
import '../../models/merchandise.dart';
import '../../services/merchandise_service.dart';
import '../../constants/app_constants.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/error_widget.dart' as custom;
import '../../widgets/status_badge_widget.dart';
import 'detail_klaim_screen.dart';
import 'katalogMerchandise_screen.dart';

class HistoryKlaimScreen extends StatefulWidget {
  const HistoryKlaimScreen({Key? key}) : super(key: key);

  @override
  State<HistoryKlaimScreen> createState() => _HistoryKlaimScreenState();
}

class _HistoryKlaimScreenState extends State<HistoryKlaimScreen> {
  List<HistoryKlaim> historyList = [];
  bool isLoading = true;
  String? errorMessage;

  @override
  void initState() {
    super.initState();
    _loadHistoryKlaim();
  }

  Future<void> _loadHistoryKlaim() async {
    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    final response = await MerchandiseService.getHistoryKlaim();

    setState(() {
      isLoading = false;
      if (response.success && response.data != null) {
        historyList = response.data!;
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
        title: const Text('History Klaim'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            onPressed: _loadHistoryKlaim,
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadHistoryKlaim,
        child: _buildContent(),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (context) => const KatalogMerchandiseScreen(),
            ),
          );
        },
        backgroundColor: AppConstants.primaryColor,
        child: const Icon(Icons.card_giftcard, color: Colors.white),
        tooltip: 'Katalog Merchandise',
      ),
    );
  }

  Widget _buildContent() {
    if (isLoading) {
      return const LoadingWidget(message: 'Memuat history klaim...');
    }

    if (errorMessage != null) {
      return custom.ErrorWidget(
        message: errorMessage!,
        onRetry: _loadHistoryKlaim,
      );
    }

    if (historyList.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.history, size: 64, color: Colors.grey),
            const SizedBox(height: 16),
            const Text(
              'Belum ada history klaim',
              style: TextStyle(
                fontSize: 16,
                color: Colors.grey,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Klaim merchandise pertama Anda di katalog',
              style: TextStyle(fontSize: 14, color: Colors.grey),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () {
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(
                    builder: (context) => const KatalogMerchandiseScreen(),
                  ),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppConstants.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 12,
                ),
              ),
              icon: const Icon(Icons.card_giftcard),
              label: const Text('Lihat Katalog'),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(AppConstants.paddingMedium),
      itemCount: historyList.length,
      itemBuilder: (context, index) {
        final history = historyList[index];
        return _buildHistoryItem(history);
      },
    );
  }

  Widget _buildHistoryItem(HistoryKlaim history) {
    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(bottom: AppConstants.paddingMedium),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
      ),
      child: InkWell(
        onTap: () {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (context) =>
                  DetailKlaimScreen(idPenukaran: history.idPenukaran),
            ),
          );
        },
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
        child: Padding(
          padding: const EdgeInsets.all(AppConstants.paddingMedium),
          child: Row(
            children: [
              // Gambar merchandise
              ClipRRect(
                borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
                child: Image.network(
                  history.gambar,
                  width: 60,
                  height: 60,
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) {
                    return Container(
                      width: 60,
                      height: 60,
                      color: Colors.grey.shade200,
                      child: const Icon(
                        Icons.card_giftcard,
                        color: Colors.grey,
                      ),
                    );
                  },
                ),
              ),

              const SizedBox(width: 12),

              // Informasi klaim
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Nama merchandise dan status
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Text(
                            history.namaMerchandise,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: AppConstants.textPrimaryColor,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        const SizedBox(width: 8),
                        StatusBadgeWidget(
                          status: history.statusPenukaran,
                          label: history.statusLabel,
                        ),
                      ],
                    ),

                    const SizedBox(height: 8),

                    // Poin yang digunakan
                    Row(
                      children: [
                        const Icon(
                          Icons.monetization_on,
                          size: 16,
                          color: AppConstants.primaryColor,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          '${history.jumlahPoin} poin',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: AppConstants.primaryColor,
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 4),

                    // Tanggal pengajuan
                    Row(
                      children: [
                        const Icon(
                          Icons.schedule,
                          size: 16,
                          color: AppConstants.textSecondaryColor,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          'Diklaim: ${_formatDate(history.tanggalPengajuan)}',
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppConstants.textSecondaryColor,
                          ),
                        ),
                      ],
                    ),

                    // Tanggal penerimaan (jika sudah diambil)
                    if (history.tanggalPenerimaan != null) ...[
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(
                            Icons.check_circle,
                            size: 16,
                            color: Colors.green,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            'Diambil: ${_formatDate(history.tanggalPenerimaan!)}',
                            style: const TextStyle(
                              fontSize: 12,
                              color: Colors.green,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),

              // Arrow icon
              const Icon(
                Icons.arrow_forward_ios,
                size: 16,
                color: AppConstants.textSecondaryColor,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
