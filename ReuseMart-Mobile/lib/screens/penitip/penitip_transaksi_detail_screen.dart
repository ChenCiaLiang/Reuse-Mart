import 'dart:convert';
import 'package:flutter/material.dart';
import '../../../constants/app_constants.dart';
import '../../../services/penitip_service.dart';
import '../../../models/history_transaksi_penitip.dart';

class PenitipTransaksiDetailScreen extends StatefulWidget {
  final int? idTransaksiPenjualan; // ✅ UBAH: Buat optional
  final HistoryTransaksiPenitip?
  transaction; // ✅ TAMBAH: Parameter untuk barang belum terjual

  const PenitipTransaksiDetailScreen({
    Key? key,
    this.idTransaksiPenjualan, // ✅ UBAH: Optional
    this.transaction, // ✅ TAMBAH: Optional transaction data
  }) : super(key: key);

  @override
  State<PenitipTransaksiDetailScreen> createState() =>
      _PenitipTransaksiDetailScreenState();
}

class _PenitipTransaksiDetailScreenState
    extends State<PenitipTransaksiDetailScreen> {
  DetailTransaksiPenitip? detailData;
  bool isLoading = true;
  String? error;

  // ✅ TAMBAH: Helper untuk cek apakah ini barang yang sudah terjual
  bool get isSoldItem => widget.idTransaksiPenjualan != null;
  bool get isUnsoldItem =>
      widget.transaction != null && !widget.transaction!.isTerjual;

  @override
  void initState() {
    super.initState();
    if (isSoldItem) {
      _loadDetailTransaksi();
    } else {
      // ✅ TAMBAH: Untuk barang belum terjual, tidak perlu load dari API
      _setupUnsoldItemData();
    }
  }

  // ✅ TAMBAH: Setup data untuk barang yang belum terjual
  void _setupUnsoldItemData() {
    setState(() {
      isLoading = false;
      // Kita akan menggunakan data transaction yang sudah ada
    });
  }

  Future<void> _loadDetailTransaksi() async {
    if (!mounted) return;

    try {
      setState(() {
        isLoading = true;
        error = null;
      });

      final result = await PenitipService.getDetailTransaksi(
        widget.idTransaksiPenjualan!,
      );

      if (!mounted) return;

      setState(() {
        detailData = result;
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
        title: Text(
          // ✅ UBAH: Title yang dinamis
          isSoldItem
              ? 'Detail Transaksi #${widget.idTransaksiPenjualan}'
              : 'Detail Penitipan #${widget.transaction!.idTransaksiPenitipan}',
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: isSoldItem
                ? _loadDetailTransaksi
                : () {}, // ✅ UBAH: Conditional refresh
          ),
        ],
      ),
      body: _buildBody(),
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
              onPressed: _loadDetailTransaksi,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    // ✅ UBAH: Handle kedua kondisi
    if (isSoldItem && detailData == null) {
      return const Center(child: Text('Data tidak ditemukan'));
    }

    if (isUnsoldItem && widget.transaction == null) {
      return const Center(child: Text('Data tidak ditemukan'));
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildStatusCard(),
          const SizedBox(height: 16),
          _buildProductCard(), // ✅ UBAH: Ganti nama method
          const SizedBox(height: 16),
          _buildPenitipanInfoCard(),
          const SizedBox(height: 16),
          // ✅ UBAH: Conditional widgets
          if (isSoldItem) ...[
            _buildTransaksiInfoCard(),
            const SizedBox(height: 16),
            _buildPembeliInfoCard(),
            const SizedBox(height: 16),
            _buildKomisiCard(),
          ] else ...[
            _buildStatusInfoCard(), // ✅ TAMBAH: Card untuk status barang belum terjual
          ],
        ],
      ),
    );
  }

  Widget _buildStatusCard() {
    if (isUnsoldItem) {
      // ✅ TAMBAH: Status card untuk barang belum terjual
      final transaction = widget.transaction!;
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: _getStatusColorFromType(transaction.statusColorType),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(
                  _getStatusIcon(transaction.status),
                  color: Colors.white,
                  size: 24,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      transaction.statusDisplay,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      'Dititipkan: ${transaction.tanggalPesan}',
                      style: TextStyle(color: Colors.grey[600]),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      );
    }

    // Original status card untuk barang terjual
    final transaksi = detailData!.transaksi;
    final status = transaksi.status;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _getStatusColor(status),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(
                _getStatusIcon(status),
                color: Colors.white,
                size: 24,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    _getStatusLabel(status),
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  Text(
                    'Dipesan: ${transaksi.tanggalPesan}',
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                  if (transaksi.tanggalLunas != null)
                    Text(
                      'Lunas: ${transaksi.tanggalLunas}',
                      style: const TextStyle(
                        color: Colors.green,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ✅ TAMBAH: Product card yang bisa handle kedua kondisi
  Widget _buildProductCard() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.inventory_2_outlined,
                  color: Colors.orange[600],
                  size: 20,
                ),
                const SizedBox(width: 8),
                Text(
                  isUnsoldItem ? 'Detail Produk' : 'Produk Anda yang Terjual',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            if (isUnsoldItem)
              _buildUnsoldProductItem()
            else
              ...detailData!.produk.map((produk) => _buildProdukItem(produk)),
          ],
        ),
      ),
    );
  }

  // ✅ TAMBAH: Product item untuk barang belum terjual
  Widget _buildUnsoldProductItem() {
    final transaction = widget.transaction!;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        border: Border.all(color: Colors.grey[300]!),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
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
                : const Icon(Icons.image_not_supported, color: Colors.grey),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  transaction.namaProduk,
                  style: const TextStyle(fontWeight: FontWeight.w500),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                if (transaction.kategori != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    'Kategori: ${transaction.kategori}',
                    style: TextStyle(color: Colors.grey[600], fontSize: 12),
                  ),
                ],
                const SizedBox(height: 4),
                Text(
                  'Harga Jual: Rp ${PenitipService.formatCurrency(transaction.hargaJual)}',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: Colors.green,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPenitipanInfoCard() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.inventory_outlined,
                  color: Colors.blue[600],
                  size: 20,
                ),
                const SizedBox(width: 8),
                const Text(
                  'Informasi Penitipan',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // ✅ PERBAIKI: Deklarasi variabel di luar spread operator
            if (isUnsoldItem) ...[
              // Info untuk barang belum terjual
              _buildInfoRow(
                'ID Penitipan',
                '#${widget.transaction!.idTransaksiPenitipan}',
              ),
              _buildInfoRow(
                'Tanggal Penitipan',
                widget.transaction!.tanggalPesan,
              ),
              if (widget.transaction!.statusPenitipan != null)
                _buildStatusPenitipanRow(widget.transaction!.statusPenitipan!),
            ] else ...[
              // Original info untuk barang terjual
              if (detailData!.penitipanSummary != null) ...[
                _buildInfoRow(
                  'Total Produk',
                  '${detailData!.penitipanSummary!['total_produk'] ?? 0} item',
                ),
                if (detailData!
                        .penitipanSummary!['tanggal_penitipan_pertama'] !=
                    null)
                  _buildInfoRow(
                    'Tanggal Penitipan',
                    detailData!.penitipanSummary!['tanggal_penitipan_pertama']
                        .toString(),
                  ),
              ],

              if (detailData!.produk.isNotEmpty) ...[
                if (detailData!.produk.first.idTransaksiPenitipan != null)
                  _buildInfoRow(
                    'ID Penitipan',
                    '#${detailData!.produk.first.idTransaksiPenitipan}',
                  ),
                if (detailData!.produk.first.statusPenitipan != null)
                  _buildStatusPenitipanRow(
                    detailData!.produk.first.statusPenitipan!,
                  ),
              ],
            ],
          ],
        ),
      ),
    );
  }

  // ✅ TAMBAH: Helper untuk status penitipan row
  Widget _buildStatusPenitipanRow(String statusPenitipan) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              'Status Penitipan',
              style: TextStyle(color: Colors.grey[600]),
            ),
          ),
          const Text(': '),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: _getPenitipanStatusColor(statusPenitipan),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              _getPenitipanStatusLabel(statusPenitipan),
              style: const TextStyle(
                color: Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ✅ TAMBAH: Status info card untuk barang belum terjual
  Widget _buildStatusInfoCard() {
    final transaction = widget.transaction!;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.info_outline,
                  color: _getInfoColorFromStatus(transaction.status),
                  size: 20,
                ),
                const SizedBox(width: 8),
                const Text(
                  'Status Produk',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: _getInfoColorFromStatus(
                  transaction.status,
                ).withOpacity(0.1),
                border: Border.all(
                  color: _getInfoColorFromStatus(
                    transaction.status,
                  ).withOpacity(0.3),
                ),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        _getInfoIconFromStatus(transaction.status),
                        color: _getInfoColorFromStatus(transaction.status),
                        size: 20,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        _getInfoTextFromStatus(transaction.status),
                        style: TextStyle(
                          color: _getInfoColorFromStatus(transaction.status),
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _getDetailInfoFromStatus(transaction.status),
                    style: TextStyle(
                      color: _getInfoColorFromStatus(transaction.status),
                      fontSize: 14,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // Sisanya tetap sama seperti kode original...

  Widget _buildTransaksiInfoCard() {
    final transaksi = detailData!.transaksi;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.receipt_long_outlined,
                  color: Colors.green[600],
                  size: 20,
                ),
                const SizedBox(width: 8),
                const Text(
                  'Informasi Transaksi',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 12),
            _buildInfoRow('ID Transaksi', '#${transaksi.idTransaksiPenjualan}'),
            if (transaksi.metodePengiriman != null)
              _buildInfoRow(
                'Metode Pengiriman',
                _getMetodePengirimanLabel(transaksi.metodePengiriman!),
              ),
            ..._buildAlamatInfo(transaksi.alamatPengiriman),
          ],
        ),
      ),
    );
  }

  Widget _buildPembeliInfoCard() {
    final pembeli = detailData!.pembeli;

    if (pembeli == null) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(Icons.person_outline, color: Colors.grey[600], size: 20),
                  const SizedBox(width: 8),
                  const Text(
                    'Informasi Pembeli',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                'Barang belum terjual',
                style: TextStyle(
                  color: Colors.grey[600],
                  fontStyle: FontStyle.italic,
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(Icons.person_outline, color: Colors.purple[600], size: 20),
                const SizedBox(width: 8),
                const Text(
                  'Informasi Pembeli',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 12),
            _buildInfoRow('Nama', pembeli.nama),
            _buildInfoRow('Email', pembeli.email),
          ],
        ),
      ),
    );
  }

  Widget _buildProdukItem(ProdukInfo produk) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        border: Border.all(color: Colors.grey[300]!),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              color: Colors.grey[200],
              borderRadius: BorderRadius.circular(8),
            ),
            child: produk.gambar != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      '${AppConstants.baseUrl}/storage/${produk.gambar}',
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) {
                        return const Icon(
                          Icons.image_not_supported,
                          color: Colors.grey,
                        );
                      },
                    ),
                  )
                : const Icon(Icons.image_not_supported, color: Colors.grey),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  produk.nama,
                  style: const TextStyle(fontWeight: FontWeight.w500),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                if (produk.idTransaksiPenitipan != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    'Penitipan #${produk.idTransaksiPenitipan}',
                    style: TextStyle(
                      color: Colors.blue[600],
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
                if (produk.kategori != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    'Kategori: ${produk.kategori}',
                    style: TextStyle(color: Colors.grey[600], fontSize: 12),
                  ),
                ],
                if (produk.tanggalPenitipan != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    'Dititipkan: ${produk.tanggalPenitipan}',
                    style: TextStyle(color: Colors.grey[600], fontSize: 11),
                  ),
                ],
                const SizedBox(height: 4),
                Text(
                  'Harga Jual: Rp ${PenitipService.formatCurrency(produk.hargaJual)}',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: Colors.green,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKomisiCard() {
    final komisiList = detailData!.komisi;
    final totalKomisiPenitip = detailData!.totalKomisiPenitip;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.account_balance_wallet_outlined,
                  color: Colors.green[600],
                  size: 20,
                ),
                const SizedBox(width: 8),
                const Text(
                  'Rincian Komisi',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 12),

            ...komisiList.asMap().entries.map((entry) {
              final index = entry.key;
              final komisi = entry.value;

              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.green[50],
                  border: Border.all(color: Colors.green[200]!),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(
                          'Item ${index + 1}',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.green,
                          ),
                        ),
                        if (komisi.idProduk != null) ...[
                          const SizedBox(width: 8),
                          Text(
                            '(Produk #${komisi.idProduk})',
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 8),
                    _buildKomisiRow('Komisi Anda', komisi.komisiPenitip),
                    _buildKomisiRow('Komisi Hunter', komisi.komisiHunter),
                    _buildKomisiRow('Komisi ReUseMart', komisi.komisiReuse),
                  ],
                ),
              );
            }).toList(),

            const Divider(thickness: 2),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.green[100],
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Total Komisi Anda:',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Colors.green,
                    ),
                  ),
                  Text(
                    'Rp ${PenitipService.formatCurrency(totalKomisiPenitip)}',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.green,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(label, style: TextStyle(color: Colors.grey[600])),
          ),
          const Text(': '),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKomisiRow(String label, double amount) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: Colors.grey[700], fontSize: 14)),
          Text(
            'Rp ${PenitipService.formatCurrency(amount)}',
            style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 14),
          ),
        ],
      ),
    );
  }

  // ✅ TAMBAH: Helper methods untuk styling
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

  Color _getInfoColorFromStatus(String status) {
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

  Color _getPenitipanStatusColor(String status) {
    switch (status) {
      case 'Aktif':
        return Colors.blue;
      case 'Selesai':
        return Colors.green;
      case 'Diambil':
        return Colors.orange;
      case 'Hangus':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String _getPenitipanStatusLabel(String status) {
    switch (status) {
      case 'Aktif':
        return 'Aktif';
      case 'Selesai':
        return 'Selesai';
      case 'Diambil':
        return 'Diambil';
      case 'Hangus':
        return 'Hangus';
      default:
        return status;
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
        return 'Barang Tersedia';
      case 'Didonasikan':
        return 'Barang Didonasikan';
      case 'Terjual':
        return 'Sedang Diproses';
      default:
        return 'Status: ${status.toLowerCase()}';
    }
  }

  String _getDetailInfoFromStatus(String status) {
    switch (status) {
      case 'Tersedia':
        return 'Produk ini masih tersedia di toko dan menunggu pembeli yang berminat.';
      case 'Didonasikan':
        return 'Produk ini telah didonasikan kepada organisasi sosial karena tidak terjual dalam batas waktu yang ditentukan.';
      case 'Terjual':
        return 'Produk ini sedang dalam proses penjualan dan menunggu konfirmasi dari pembeli.';
      default:
        return 'Status produk: ${status.toLowerCase()}.';
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'terjual':
      case 'selesai':
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

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'terjual':
      case 'selesai':
      case 'diambil':
        return Icons.check_circle;
      case 'kirim':
        return Icons.local_shipping;
      case 'disiapkan':
        return Icons.inventory;
      case 'menunggu_pembayaran':
        return Icons.payment;
      case 'menunggu_verifikasi':
        return Icons.pending;
      case 'batal':
      case 'hangus':
        return Icons.cancel;
      case 'Tersedia':
        return Icons.store_outlined;
      case 'Didonasikan':
        return Icons.volunteer_activism_outlined;
      default:
        return Icons.info;
    }
  }

  String _getStatusLabel(String status) {
    switch (status) {
      case 'terjual':
        return 'Terjual';
      case 'selesai':
        return 'Selesai';
      case 'diambil':
        return 'Diambil Pembeli';
      case 'kirim':
        return 'Sedang Dikirim';
      case 'disiapkan':
        return 'Sedang Disiapkan';
      case 'menunggu_pembayaran':
        return 'Menunggu Pembayaran';
      case 'menunggu_verifikasi':
        return 'Menunggu Verifikasi';
      case 'batal':
        return 'Dibatalkan';
      case 'hangus':
        return 'Hangus';
      default:
        return status;
    }
  }

  String _getMetodePengirimanLabel(String metode) {
    switch (metode) {
      case 'kurir':
        return 'Dikirim oleh Kurir';
      case 'ambil_sendiri':
        return 'Diambil Sendiri';
      default:
        return metode;
    }
  }

  List<Widget> _buildAlamatInfo(dynamic alamatPengiriman) {
    if (alamatPengiriman == null) return [];

    List<Widget> widgets = [];

    if (alamatPengiriman is String) {
      try {
        final alamatMap = jsonDecode(alamatPengiriman);
        if (alamatMap['jenis'] != null) {
          widgets.add(_buildInfoRow('Jenis Alamat', alamatMap['jenis']));
        }
        if (alamatMap['alamatLengkap'] != null) {
          widgets.add(_buildInfoRow('Alamat', alamatMap['alamatLengkap']));
        }
      } catch (e) {
        widgets.add(_buildInfoRow('Alamat', alamatPengiriman));
      }
    } else if (alamatPengiriman is Map) {
      if (alamatPengiriman['jenis'] != null) {
        widgets.add(_buildInfoRow('Jenis Alamat', alamatPengiriman['jenis']));
      }
      if (alamatPengiriman['alamatLengkap'] != null) {
        widgets.add(_buildInfoRow('Alamat', alamatPengiriman['alamatLengkap']));
      }
    }

    return widgets;
  }
}
