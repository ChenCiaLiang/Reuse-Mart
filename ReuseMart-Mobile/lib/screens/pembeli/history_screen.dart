import 'package:flutter/material.dart';
import '../../../constants/app_constants.dart';
import '../../../services/pembeli_service.dart';
import '../../../models/transaksi_history.dart';
import 'transaksi_detail_screen.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({Key? key}) : super(key: key);

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  List<TransaksiHistory> transactions = [];
  HistorySummary? summary;
  bool isLoading = true;
  String? error;

  // Filter tanggal lunas
  String? selectedTanggalLunasMulai;
  String? selectedTanggalLunasSelesai;

  @override
  void initState() {
    super.initState();
    _loadHistory();
  }

  // ✅ PERBAIKAN: Tambah mounted check
  Future<void> _loadHistory() async {
    if (!mounted) return;

    try {
      setState(() {
        isLoading = true;
        error = null;
      });

      final result = await PembeliService.getHistoryTransaksi(
        tanggalLunasMulai: selectedTanggalLunasMulai,
        tanggalLunasSelesai: selectedTanggalLunasSelesai,
        limit: 50,
      );

      // ✅ Check mounted sebelum setState
      if (!mounted) return;

      setState(() {
        transactions = result['transactions'];
        summary = result['summary'];
        isLoading = false;
      });
    } catch (e) {
      // ✅ Check mounted sebelum setState
      if (!mounted) return;

      setState(() {
        error = e.toString();
        isLoading = false;
      });
    }
  }

  @override
  void dispose() {
    // ✅ Clean up jika ada
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: AppBar(
        title: const Text('History Transaksi'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        automaticallyImplyLeading: false,
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadHistory),
        ],
      ),
      body: Column(
        children: [
          _buildDateFilterSection(),
          if (summary != null) _buildSummarySection(),
          Expanded(child: _buildTransactionList()),
        ],
      ),
    );
  }

  Widget _buildDateFilterSection() {
    return Container(
      padding: const EdgeInsets.all(16),
      color: Colors.grey[50],
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Filter Berdasarkan Tanggal Lunas',
            style: TextStyle(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildDateField(
                  'Tanggal Mulai',
                  selectedTanggalLunasMulai,
                  (date) {
                    if (!mounted) return;
                    setState(() {
                      selectedTanggalLunasMulai = date;
                    });
                    _loadHistory();
                  },
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildDateField(
                  'Tanggal Selesai',
                  selectedTanggalLunasSelesai,
                  (date) {
                    if (!mounted) return;
                    setState(() {
                      selectedTanggalLunasSelesai = date;
                    });
                    _loadHistory();
                  },
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              TextButton(
                onPressed: () {
                  if (!mounted) return;
                  setState(() {
                    selectedTanggalLunasMulai = null;
                    selectedTanggalLunasSelesai = null;
                  });
                  _loadHistory();
                },
                child: const Text('Reset Filter'),
              ),
              const Spacer(),
              TextButton(
                onPressed: _showQuickFilterDialog,
                child: const Text('Filter Cepat'),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildDateField(
    String label,
    String? value,
    Function(String?) onChanged,
  ) {
    return InkWell(
      onTap: () => _selectDate(label, value, onChanged),
      child: InputDecorator(
        decoration: InputDecoration(
          labelText: label,
          border: const OutlineInputBorder(),
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 12,
            vertical: 8,
          ),
        ),
        child: Text(
          value ?? 'Pilih tanggal',
          style: TextStyle(
            color: value != null ? Colors.black : Colors.grey[600],
          ),
        ),
      ),
    );
  }

  Future<void> _selectDate(
    String label,
    String? currentValue,
    Function(String?) onChanged,
  ) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: currentValue != null
          ? DateTime.tryParse(currentValue) ?? DateTime.now()
          : DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
    );

    if (picked != null) {
      final formattedDate =
          '${picked.year}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}';
      onChanged(formattedDate);
    }
  }

  void _showQuickFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filter Cepat'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              title: const Text('Bulan Ini'),
              onTap: () {
                final now = DateTime.now();
                final startOfMonth = DateTime(now.year, now.month, 1);
                final endOfMonth = DateTime(now.year, now.month + 1, 0);

                if (!mounted) return;
                setState(() {
                  selectedTanggalLunasMulai =
                      '${startOfMonth.year}-${startOfMonth.month.toString().padLeft(2, '0')}-${startOfMonth.day.toString().padLeft(2, '0')}';
                  selectedTanggalLunasSelesai =
                      '${endOfMonth.year}-${endOfMonth.month.toString().padLeft(2, '0')}-${endOfMonth.day.toString().padLeft(2, '0')}';
                });

                Navigator.of(context).pop();
                _loadHistory();
              },
            ),
            ListTile(
              title: const Text('3 Bulan Terakhir'),
              onTap: () {
                final now = DateTime.now();
                final threeMonthsAgo = DateTime(
                  now.year,
                  now.month - 3,
                  now.day,
                );

                if (!mounted) return;
                setState(() {
                  selectedTanggalLunasMulai =
                      '${threeMonthsAgo.year}-${threeMonthsAgo.month.toString().padLeft(2, '0')}-${threeMonthsAgo.day.toString().padLeft(2, '0')}';
                  selectedTanggalLunasSelesai =
                      '${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}';
                });

                Navigator.of(context).pop();
                _loadHistory();
              },
            ),
            ListTile(
              title: const Text('Tahun Ini'),
              onTap: () {
                final now = DateTime.now();

                if (!mounted) return;
                setState(() {
                  selectedTanggalLunasMulai = '${now.year}-01-01';
                  selectedTanggalLunasSelesai = '${now.year}-12-31';
                });

                Navigator.of(context).pop();
                _loadHistory();
              },
            ),
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

  Widget _buildSummarySection() {
    return Container(
      padding: const EdgeInsets.all(16),
      color: Colors.blue[50],
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _buildSummaryItem(
            'Total Transaksi',
            summary!.totalTransaksi,
            Colors.blue,
          ),
          if (summary!.filterTanggalLunas['tanggal_lunas_mulai'] != null ||
              summary!.filterTanggalLunas['tanggal_lunas_selesai'] != null)
            _buildSummaryItem('Terfilter', transactions.length, Colors.green),
        ],
      ),
    );
  }

  Widget _buildSummaryItem(String label, int count, Color color) {
    return Column(
      children: [
        Text(
          count.toString(),
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(label, style: const TextStyle(fontSize: 12)),
      ],
    );
  }

  Widget _buildTransactionList() {
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
              onPressed: _loadHistory,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (transactions.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.shopping_bag_outlined,
              size: 64,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              'Tidak ada transaksi',
              style: TextStyle(fontSize: 16, color: Colors.grey[600]),
            ),
            if (selectedTanggalLunasMulai != null ||
                selectedTanggalLunasSelesai != null) ...[
              const SizedBox(height: 8),
              Text(
                'untuk periode yang dipilih',
                style: TextStyle(fontSize: 14, color: Colors.grey[500]),
              ),
            ],
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: transactions.length,
      itemBuilder: (context, index) {
        final transaction = transactions[index];
        return _buildTransactionCard(transaction);
      },
    );
  }

  Widget _buildTransactionCard(TransaksiHistory transaction) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => TransaksiDetailScreen(
                transaction:
                    transaction, // ✅ Sesuai dengan requirement TransaksiDetailScreen
              ),
            ),
          );
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Transaksi #${transaction.idTransaksiPenjualan}',
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
                      color: _getStatusColor(transaction.status.code),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      transaction.status.label,
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
              if (transaction.tanggalLunas != null)
                Text(
                  'Lunas: ${transaction.tanggalLunas}',
                  style: const TextStyle(
                    color: Colors.green,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              Text(
                'Dipesan: ${transaction.tanggalPesan ?? '-'}',
                style: TextStyle(color: Colors.grey[600]),
              ),
              Text(
                'Metode: ${transaction.metodePengiriman.label}',
                style: TextStyle(color: Colors.grey[600]),
              ),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('${transaction.jumlahItem} item'),
                  Text(
                    'Rp ${_formatCurrency(transaction.totalBayar)}',
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                    ),
                  ),
                ],
              ),
              if (transaction.poinDidapat > 0) ...[
                const SizedBox(height: 4),
                Text(
                  '+${transaction.poinDidapat} poin',
                  style: const TextStyle(
                    color: Colors.green,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'terjual':
      case 'diambil':
        return Colors.green;
      case 'kirim':
      case 'disiapkan':
        return Colors.blue;
      case 'menunggu_pembayaran':
      case 'menunggu_verifikasi':
        return Colors.orange;
      case 'batal':
      case 'hangus':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String _formatCurrency(double amount) {
    return amount
        .toStringAsFixed(0)
        .replaceAllMapped(
          RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
          (Match m) => '${m[1]}.',
        );
  }
}
