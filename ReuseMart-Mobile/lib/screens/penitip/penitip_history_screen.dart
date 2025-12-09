import 'package:flutter/material.dart';
import '../../../constants/app_constants.dart';
import '../../../services/penitip_service.dart';
import '../../../models/history_transaksi_penitip.dart';
import 'penitip_transaksi_detail_screen.dart';

class PenitipHistoryScreen extends StatefulWidget {
  const PenitipHistoryScreen({Key? key}) : super(key: key);

  @override
  State<PenitipHistoryScreen> createState() => _PenitipHistoryScreenState();
}

class _PenitipHistoryScreenState extends State<PenitipHistoryScreen> {
  List<HistoryTransaksiPenitip> transactions = [];
  PaginationInfo? pagination;
  FilterInfo? filter;
  bool isLoading = true;
  bool isLoadingMore = false;
  String? error;

  // Filter tanggal
  String? selectedStartDate;
  String? selectedEndDate;

  // Pagination
  int currentPage = 1;
  final int perPage = 10;

  @override
  void initState() {
    super.initState();
    _loadHistory();
  }

  Future<void> _loadHistory({bool isLoadMore = false}) async {
    if (!mounted) return;

    try {
      if (isLoadMore) {
        setState(() {
          isLoadingMore = true;
        });
      } else {
        setState(() {
          isLoading = true;
          error = null;
          currentPage = 1;
        });
      }

      final result = await PenitipService.getHistoryTransaksi(
        startDate: selectedStartDate,
        endDate: selectedEndDate,
        page: isLoadMore ? currentPage + 1 : 1,
        perPage: perPage,
      );

      if (!mounted) return;

      setState(() {
        if (isLoadMore) {
          // ✅ Updated: Menggunakan List<HistoryTransaksiPenitip> langsung
          transactions.addAll(
            result['transactions'] as List<HistoryTransaksiPenitip>,
          );
          currentPage++;
          isLoadingMore = false;
        } else {
          // ✅ Updated: Menggunakan List<HistoryTransaksiPenitip> langsung
          transactions =
              result['transactions'] as List<HistoryTransaksiPenitip>;
          currentPage = 1;
          isLoading = false;
        }

        // ✅ Updated: Menggunakan model yang sudah di-parse
        pagination = result['pagination'] as PaginationInfo?;
        filter = result['filter'] as FilterInfo?;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        error = e.toString();
        isLoading = false;
        isLoadingMore = false;
      });
    }
  }

  Future<void> _refreshHistory() async {
    await _loadHistory(isLoadMore: false);
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: AppBar(
        title: const Text('History Penitipan'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterDialog,
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _refreshHistory,
          ),
        ],
      ),
      body: Column(
        children: [
          if (pagination != null) _buildSummarySection(),
          Expanded(child: _buildTransactionList()),
        ],
      ),
    );
  }

  // ✅ NEW: Filter dialog
  void _showFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filter Tanggal'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildDateField(
              'Tanggal Mulai',
              selectedStartDate,
              (value) => selectedStartDate = value,
            ),
            const SizedBox(height: 16),
            _buildDateField(
              'Tanggal Selesai',
              selectedEndDate,
              (value) => selectedEndDate = value,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              setState(() {
                selectedStartDate = null;
                selectedEndDate = null;
              });
              Navigator.pop(context);
              _loadHistory();
            },
            child: const Text('Reset'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _loadHistory();
            },
            child: const Text('Terapkan'),
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
          value != null ? _formatDisplayDate(value) : 'Pilih tanggal',
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

  Widget _buildSummarySection() {
    return Container(
      padding: const EdgeInsets.all(16),
      color: Colors.green[50],
      child: Column(
        children: [
          // ✅ Show filter period if available
          if (filter != null) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.green[100],
                borderRadius: BorderRadius.circular(16),
              ),
              child: Text(
                'Periode: ${filter!.displayPeriod}',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Colors.green,
                ),
              ),
            ),
            const SizedBox(height: 12),
          ],
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildSummaryItem('Total Data', pagination!.total, Colors.green),
              _buildSummaryItem(
                'Halaman ${pagination!.currentPage}',
                pagination!.lastPage,
                Colors.blue,
              ),
              // ✅ Show count of sold items
              _buildSummaryItem(
                'Terjual',
                transactions.where((t) => t.isTerjual).length,
                Colors.orange,
              ),
            ],
          ),
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
        Text(
          label,
          style: const TextStyle(fontSize: 12),
          textAlign: TextAlign.center,
        ),
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
              onPressed: _refreshHistory,
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
            Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey[400]),
            const SizedBox(height: 16),
            Text(
              'Belum ada barang yang dititipkan',
              style: TextStyle(fontSize: 16, color: Colors.grey[600]),
            ),
            if (selectedStartDate != null || selectedEndDate != null) ...[
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

    return NotificationListener<ScrollNotification>(
      onNotification: (ScrollNotification scrollInfo) {
        if (!isLoadingMore &&
            pagination != null &&
            pagination!.hasMore &&
            scrollInfo.metrics.pixels == scrollInfo.metrics.maxScrollExtent) {
          _loadHistory(isLoadMore: true);
        }
        return false;
      },
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: transactions.length + (isLoadingMore ? 1 : 0),
        itemBuilder: (context, index) {
          if (index == transactions.length) {
            return const Center(
              child: Padding(
                padding: EdgeInsets.all(16),
                child: CircularProgressIndicator(),
              ),
            );
          }

          final transaction = transactions[index];
          return _buildTransactionCard(transaction);
        },
      ),
    );
  }

  // Update method _buildTransactionCard (bagian onTap)
  Widget _buildTransactionCard(HistoryTransaksiPenitip transaction) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () {
          // ✅ UPDATED: Selalu navigasi ke detail screen, baik terjual maupun belum
          if (transaction.isTerjual &&
              transaction.idTransaksiPenjualan != null) {
            // Navigasi ke detail transaksi untuk barang yang sudah terjual
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => PenitipTransaksiDetailScreen(
                  idTransaksiPenjualan: transaction.idTransaksiPenjualan!,
                ),
              ),
            );
          } else {
            // Navigasi ke detail penitipan untuk barang yang belum terjual
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => PenitipTransaksiDetailScreen(
                  transaction: transaction, // ✅ Gunakan parameter transaction
                ),
              ),
            );
          }
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header dengan status dan ID penitipan
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          transaction.isTerjual
                              ? 'Transaksi #${transaction.idTransaksiPenjualan}'
                              : 'Penitipan #${transaction.idTransaksiPenitipan}',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        // Show status penitipan info
                        if (transaction.statusPenitipan != null)
                          Text(
                            transaction.penitipanInfo,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[600],
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: _getStatusColorFromType(
                        transaction.statusColorType,
                      ),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      transaction.statusDisplay,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Produk info dengan gambar
              Row(
                children: [
                  // Gambar produk
                  Container(
                    width: 60,
                    height: 60,
                    decoration: BoxDecoration(
                      color: Colors.grey[200],
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: transaction.gambar != null
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(8),
                            child: Image.network(
                              '${AppConstants.baseUrl}/storage/${transaction.gambar}',
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

                  // Info produk
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          transaction.namaProduk,
                          style: const TextStyle(
                            fontWeight: FontWeight.w500,
                            fontSize: 15,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        // Show category if available
                        if (transaction.kategori != null) ...[
                          const SizedBox(height: 2),
                          Text(
                            transaction.kategori!,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                        const SizedBox(height: 4),
                        Text(
                          'Harga Jual: Rp ${PenitipService.formatCurrency(transaction.hargaJual)}',
                          style: const TextStyle(
                            color: Colors.green,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 12),

              // Tanggal info
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          transaction.isTerjual
                              ? 'Dipesan: ${transaction.tanggalPesan}'
                              : 'Dititipkan: ${transaction.tanggalPesan}',
                          style: TextStyle(
                            color: Colors.grey[600],
                            fontSize: 13,
                          ),
                        ),
                        if (transaction.tanggalLunas != null)
                          Text(
                            'Lunas: ${transaction.tanggalLunas}',
                            style: const TextStyle(
                              color: Colors.green,
                              fontWeight: FontWeight.w500,
                              fontSize: 13,
                            ),
                          ),
                      ],
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 12),

              // Komisi info - hanya tampil jika sudah terjual
              if (transaction.isTerjual) ...[
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.green[50],
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.green[200]!),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Komisi Anda:',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.green,
                            ),
                          ),
                          Text(
                            'Rp ${PenitipService.formatCurrency(transaction.komisiPenitip)}',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.green,
                              fontSize: 16,
                            ),
                          ),
                        ],
                      ),
                      if (transaction.komisiHunter > 0) ...[
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Komisi Hunter:',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey[600],
                              ),
                            ),
                            Text(
                              'Rp ${PenitipService.formatCurrency(transaction.komisiHunter)}',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey[600],
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ] else ...[
                // Info untuk barang yang belum terjual
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: _getInfoColorFromStatus(transaction.status)[50],
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: _getInfoColorFromStatus(transaction.status)[200]!,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        _getInfoIconFromStatus(transaction.status),
                        color: _getInfoColorFromStatus(transaction.status)[700],
                        size: 16,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _getInfoTextFromStatus(transaction.status),
                          style: TextStyle(
                            color: _getInfoColorFromStatus(
                              transaction.status,
                            )[700],
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                      // ✅ NEW: Tambahkan arrow indicator untuk menunjukkan bisa di-tap
                      Icon(
                        Icons.arrow_forward_ios,
                        size: 12,
                        color: _getInfoColorFromStatus(transaction.status)[700],
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  // ✅ REMOVE: Hapus method _showItemNotSoldDialog karena tidak diperlukan lagi
  // void _showItemNotSoldDialog(HistoryTransaksiPenitip transaction) {
  //   ... (hapus seluruh method ini)
  // }

  // ✅ NEW: Dialog untuk barang yang belum terjual
  void _showItemNotSoldDialog(HistoryTransaksiPenitip transaction) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Info Produk'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Penitipan ID: #${transaction.idTransaksiPenitipan}'),
            const SizedBox(height: 8),
            Text('Produk: ${transaction.namaProduk}'),
            if (transaction.kategori != null) ...[
              const SizedBox(height: 4),
              Text('Kategori: ${transaction.kategori}'),
            ],
            const SizedBox(height: 8),
            Text('Status: ${transaction.statusDisplay}'),
            const SizedBox(height: 8),
            Text(
              'Harga Jual: Rp ${PenitipService.formatCurrency(transaction.hargaJual)}',
            ),
            const SizedBox(height: 12),
            Text(
              _getDetailInfoFromStatus(transaction.status),
              style: TextStyle(
                color: _getInfoColorFromStatus(transaction.status)[700],
              ),
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

  // ✅ Helper methods for status styling
  Color _getStatusColorFromType(String type) {
    switch (type) {
      case 'success':
        return Colors.green;
      case 'info':
        return Colors.blue;
      case 'warning':
        return Colors.orange;
      case 'primary':
        return Colors.purple;
      case 'secondary':
      default:
        return Colors.grey;
    }
  }

  MaterialColor _getInfoColorFromStatus(String status) {
    switch (status) {
      case 'Tersedia':
        return Colors.orange;
      case 'Didonasikan':
        return Colors.blue;
      case 'Terjual':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }

  IconData _getInfoIconFromStatus(String status) {
    switch (status) {
      case 'Tersedia':
        return Icons.store_outlined;
      case 'Didonasikan':
        return Icons.volunteer_activism_outlined;
      case 'Terjual':
        return Icons.hourglass_empty_outlined;
      default:
        return Icons.info_outline;
    }
  }

  String _getInfoTextFromStatus(String status) {
    switch (status) {
      case 'Tersedia':
        return 'Barang tersedia di toko dan belum terjual';
      case 'Didonasikan':
        return 'Barang telah didonasikan';
      case 'Terjual':
        return 'Barang sedang dalam proses penjualan';
      default:
        return 'Status: ${status.toLowerCase()}';
    }
  }

  String _getDetailInfoFromStatus(String status) {
    switch (status) {
      case 'Tersedia':
        return 'Produk ini masih tersedia di toko dan menunggu pembeli.';
      case 'Didonasikan':
        return 'Produk ini telah didonasikan karena tidak terjual dalam batas waktu yang ditentukan.';
      case 'Terjual':
        return 'Produk ini sedang dalam proses penjualan dan menunggu pembayaran dari pembeli.';
      default:
        return 'Status produk: ${status.toLowerCase()}.';
    }
  }

  String _formatDisplayDate(String dateStr) {
    return PenitipService.formatDisplayDate(dateStr);
  }
}
