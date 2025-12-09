import 'package:flutter/material.dart';
import '../../../constants/app_constants.dart';
import '../../../services/hunter_service.dart';
import '../../../models/history_komisi.dart';

class HistoryKomisiScreen extends StatefulWidget {
  const HistoryKomisiScreen({Key? key}) : super(key: key);

  @override
  State<HistoryKomisiScreen> createState() => _HistoryKomisiScreenState();
}

class _HistoryKomisiScreenState extends State<HistoryKomisiScreen> {
  List<HistoryKomisi> historyKomisi = [];
  KomisiSummary? summary;
  bool isLoading = true;
  String? error;

  @override
  void initState() {
    super.initState();
    _loadHistoryKomisi();
  }

  Future<void> _loadHistoryKomisi() async {
    if (!mounted) return;

    try {
      setState(() {
        isLoading = true;
        error = null;
      });

      final result = await HunterService.getHistoryKomisi();

      if (!mounted) return;

      setState(() {
        historyKomisi = result['historyKomisi'];
        summary = result['summary'];
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
    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: AppBar(
        title: const Text('History Komisi'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadHistoryKomisi,
          ),
        ],
      ),
      body: Column(
        children: [
          if (summary != null) _buildSummarySection(),
          Expanded(child: _buildKomisiList()),
        ],
      ),
    );
  }

  Widget _buildSummarySection() {
    return Container(
      padding: const EdgeInsets.all(16),
      color: Colors.green[50],
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _buildSummaryItem(
            'Total Komisi',
            'Rp ${_formatCurrency(summary!.totalKomisi)}',
            Colors.green,
          ),
          _buildSummaryItem(
            'Total Transaksi',
            summary!.totalTransaksi.toString(),
            Colors.blue,
          ),
          if (summary!.rataRataKomisi > 0)
            _buildSummaryItem(
              'Rata-rata',
              'Rp ${_formatCurrency(summary!.rataRataKomisi)}',
              Colors.orange,
            ),
        ],
      ),
    );
  }

  Widget _buildSummaryItem(String label, String value, Color color) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(label, style: const TextStyle(fontSize: 12)),
      ],
    );
  }

  Widget _buildKomisiList() {
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
              onPressed: _loadHistoryKomisi,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (historyKomisi.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.money_off_outlined, size: 64, color: Colors.grey[400]),
            const SizedBox(height: 16),
            Text(
              'Belum ada komisi',
              style: TextStyle(fontSize: 16, color: Colors.grey[600]),
            ),
            const SizedBox(height: 8),
            Text(
              'Komisi akan muncul setelah barang hasil hunting terjual',
              style: TextStyle(fontSize: 14, color: Colors.grey[500]),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: historyKomisi.length,
      itemBuilder: (context, index) {
        final komisi = historyKomisi[index];
        return _buildKomisiCard(komisi);
      },
    );
  }

  Widget _buildKomisiCard(HistoryKomisi komisi) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Komisi #${komisi.idKomisi}',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.green,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    'Rp ${_formatCurrency(komisi.komisiHunter)}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            if (komisi.tanggal != null)
              Text(
                'Tanggal: ${_formatDate(komisi.tanggal!)}',
                style: TextStyle(color: Colors.grey[600]),
              ),
            const SizedBox(height: 8),
            Row(
              children: [
                Container(
                  width: 50,
                  height: 50,
                  decoration: BoxDecoration(
                    color: Colors.grey[200],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: komisi.produk.gambarUtama != null
                      ? ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: Image.network(
                            komisi.produk.gambarUtama!,
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) {
                              return const Icon(
                                Icons.image_not_supported,
                                color: Colors.grey,
                              );
                            },
                          ),
                        )
                      : const Icon(
                          Icons.image_not_supported,
                          color: Colors.grey,
                        ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        komisi.produk.nama,
                        style: const TextStyle(fontWeight: FontWeight.w500),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      if (komisi.produk.kategori != null)
                        Text(
                          komisi.produk.kategori!,
                          style: TextStyle(
                            color: Colors.grey[600],
                            fontSize: 12,
                          ),
                        ),
                      Text(
                        'Harga: Rp ${_formatCurrency(komisi.produk.hargaJual)}',
                        style: TextStyle(color: Colors.grey[600], fontSize: 12),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              'Penitip: ${komisi.penitip.nama}',
              style: TextStyle(color: Colors.grey[600], fontSize: 12),
            ),
            if (komisi.transaksi.tanggalLaku != null)
              Text(
                'Terjual: ${_formatDate(komisi.transaksi.tanggalLaku!)}',
                style: TextStyle(color: Colors.grey[600], fontSize: 12),
              ),
          ],
        ),
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
      return '${date.day}/${date.month}/${date.year} ${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
    } catch (e) {
      return dateStr;
    }
  }
}
