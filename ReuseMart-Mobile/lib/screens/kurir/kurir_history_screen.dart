import 'package:flutter/material.dart';
import '../../constants/app_constants.dart';
import '../../models/kurir.dart';
import '../../services/kurir_service.dart';
import 'package:intl/intl.dart';

class KurirHistoryScreen extends StatefulWidget {
  const KurirHistoryScreen({super.key});

  @override
  State<KurirHistoryScreen> createState() => _KurirHistoryScreenState();
}

class _KurirHistoryScreenState extends State<KurirHistoryScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  List<TugasPengiriman> _historyList = [];
  List<TugasPengiriman> _tugasHariIni = [];
  bool _isLoading = true;
  String? _errorMessage;

  // Loading state untuk individual tugas
  Set<int> _updatingTasks = {};

  // Filter states
  String? _selectedStatus;
  DateTime? _tanggalMulai;
  DateTime? _tanggalSelesai;

  final List<String> _statusOptions = [
    'Semua',
    'disiapkan',
    'kirim',
    'terjual',
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final futures = await Future.wait([
        KurirService.getHistoryTugasPengiriman(
          status: _selectedStatus != null && _selectedStatus != 'Semua'
              ? _selectedStatus
              : null,
          tanggalMulai: _tanggalMulai?.toIso8601String().split('T')[0],
          tanggalSelesai: _tanggalSelesai?.toIso8601String().split('T')[0],
        ),
        KurirService.getTugasHariIni(),
      ]);

      if (mounted) {
        setState(() {
          _historyList = futures[0] as List<TugasPengiriman>;
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
    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: AppBar(
        title: const Text('Tugas Pengiriman'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterDialog,
            tooltip: 'Filter',
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
            tooltip: 'Refresh',
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: [
            Tab(
              text: 'Hari Ini (${_tugasHariIni.length})',
              icon: const Icon(Icons.today),
            ),
            Tab(
              text: 'Riwayat (${_historyList.length})',
              icon: const Icon(Icons.history),
            ),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildTugasHariIniTab(),
          _buildHistoryTab(),
        ],
      ),
    );
  }

  Widget _buildTugasHariIniTab() {
    if (_isLoading) {
      return _buildLoadingWidget();
    }

    if (_errorMessage != null) {
      return _buildErrorWidget();
    }

    if (_tugasHariIni.isEmpty) {
      return _buildEmptyWidget(
        'Tidak Ada Tugas Hari Ini',
        'Belum ada tugas pengiriman untuk hari ini.',
        Icons.assignment_turned_in_outlined,
      );
    }

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView.builder(
        padding: const EdgeInsets.all(AppConstants.paddingMedium),
        itemCount: _tugasHariIni.length,
        itemBuilder: (context, index) {
          return _buildTugasCard(_tugasHariIni[index], showUpdateButton: true);
        },
      ),
    );
  }

  Widget _buildHistoryTab() {
    if (_isLoading) {
      return _buildLoadingWidget();
    }

    if (_errorMessage != null) {
      return _buildErrorWidget();
    }

    return Column(
      children: [
        // Filter summary
        if (_selectedStatus != null ||
            _tanggalMulai != null ||
            _tanggalSelesai != null)
          _buildFilterSummary(),

        // History list
        Expanded(
          child: _historyList.isEmpty
              ? _buildEmptyWidget(
                  'Tidak Ada Riwayat',
                  'Belum ada riwayat tugas pengiriman.',
                  Icons.history_outlined,
                )
              : RefreshIndicator(
                  onRefresh: _loadData,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppConstants.paddingMedium),
                    itemCount: _historyList.length,
                    itemBuilder: (context, index) {
                      return _buildTugasCard(_historyList[index]);
                    },
                  ),
                ),
        ),
      ],
    );
  }

  Widget _buildTugasCard(TugasPengiriman tugas,
      {bool showUpdateButton = false}) {
    final isUpdating = _updatingTasks.contains(tugas.idTransaksiPenjualan);

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
                // Alamat pengiriman
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
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: AppConstants.paddingMedium),

                // Tanggal info
                Row(
                  children: [
                    Expanded(
                      child: _buildInfoItem(
                        Icons.calendar_today,
                        'Dipesan',
                        DateFormat('dd/MM/yyyy HH:mm')
                            .format(tugas.tanggalPesan),
                      ),
                    ),
                    if (tugas.tanggalKirim != null)
                      Expanded(
                        child: _buildInfoItem(
                          Icons.local_shipping,
                          'Dikirim',
                          DateFormat('dd/MM/yyyy HH:mm')
                              .format(tugas.tanggalKirim!),
                        ),
                      ),
                  ],
                ),

                if (tugas.tanggalSelesai != null)
                  Padding(
                    padding:
                        const EdgeInsets.only(top: AppConstants.paddingSmall),
                    child: _buildInfoItem(
                      Icons.check_circle,
                      'Selesai',
                      DateFormat('dd/MM/yyyy HH:mm')
                          .format(tugas.tanggalSelesai!),
                    ),
                  ),

                const SizedBox(height: AppConstants.paddingMedium),

                // Items summary
                Container(
                  padding: const EdgeInsets.all(AppConstants.paddingMedium),
                  decoration: BoxDecoration(
                    color: AppConstants.backgroundColor,
                    borderRadius:
                        BorderRadius.circular(AppConstants.radiusSmall),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text(
                            'Items (${tugas.items.length})',
                            style: AppConstants.bodyStyle.copyWith(
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const Spacer(),
                          Text(
                            'Total: ${NumberFormat.currency(
                              locale: 'id_ID',
                              symbol: 'Rp ',
                              decimalDigits: 0,
                            ).format(tugas.totalHarga)}',
                            style: AppConstants.bodyStyle.copyWith(
                              fontWeight: FontWeight.w600,
                              color: AppConstants.primaryColor,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppConstants.paddingSmall),
                      ...tugas.items.take(3).map((item) => Padding(
                            padding: const EdgeInsets.only(
                                bottom: AppConstants.paddingSmall / 2),
                            child: Text(
                              '• ${item.namaProduk} (${item.quantity}x)',
                              style: AppConstants.captionStyle,
                            ),
                          )),
                      if (tugas.items.length > 3)
                        Text(
                          '... dan ${tugas.items.length - 3} item lainnya',
                          style: AppConstants.captionStyle.copyWith(
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                    ],
                  ),
                ),

                // FUNGSIONALITAS 119: Update button untuk tugas hari ini
                if (showUpdateButton && tugas.canUpdateToSelesai) ...[
                  const SizedBox(height: AppConstants.paddingMedium),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed:
                          isUpdating ? null : () => _updateStatusSelesai(tugas),
                      icon: isUpdating
                          ? const SizedBox(
                              width: 16,
                              height: 16,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor:
                                    AlwaysStoppedAnimation<Color>(Colors.white),
                              ),
                            )
                          : const Icon(Icons.check_circle),
                      label: Text(
                        isUpdating ? 'Memproses...' : 'Tandai Selesai',
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(
                          vertical: AppConstants.paddingMedium,
                        ),
                      ),
                    ),
                  ),
                ],

                // Status info untuk tugas yang sudah selesai
                if (tugas.status == 'terjual') ...[
                  const SizedBox(height: AppConstants.paddingMedium),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(AppConstants.paddingMedium),
                    decoration: BoxDecoration(
                      color: Colors.green.withOpacity(0.1),
                      borderRadius:
                          BorderRadius.circular(AppConstants.radiusSmall),
                      border: Border.all(color: Colors.green.withOpacity(0.3)),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.check_circle_outline,
                          color: Colors.green,
                          size: 20,
                        ),
                        const SizedBox(width: AppConstants.paddingSmall),
                        Text(
                          'Pengiriman telah selesai',
                          style: AppConstants.bodyStyle.copyWith(
                            color: Colors.green[700],
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoItem(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, color: AppConstants.textSecondaryColor, size: 16),
        const SizedBox(width: AppConstants.paddingSmall / 2),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: AppConstants.captionStyle.copyWith(fontSize: 10),
              ),
              Text(
                value,
                style: AppConstants.captionStyle,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ],
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

  Widget _buildFilterSummary() {
    return Container(
      margin: const EdgeInsets.all(AppConstants.paddingMedium),
      padding: const EdgeInsets.all(AppConstants.paddingMedium),
      decoration: BoxDecoration(
        color: AppConstants.primaryColor.withOpacity(0.1),
        borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
      ),
      child: Row(
        children: [
          Icon(Icons.filter_list, color: AppConstants.primaryColor, size: 20),
          const SizedBox(width: AppConstants.paddingSmall),
          Expanded(
            child: Text(
              _buildFilterText(),
              style: AppConstants.captionStyle.copyWith(
                color: AppConstants.primaryColor,
              ),
            ),
          ),
          IconButton(
            onPressed: _clearFilters,
            icon: Icon(Icons.clear, color: AppConstants.primaryColor, size: 20),
            tooltip: 'Hapus Filter',
          ),
        ],
      ),
    );
  }

  String _buildFilterText() {
    List<String> filters = [];

    if (_selectedStatus != null && _selectedStatus != 'Semua') {
      filters.add('Status: $_selectedStatus');
    }

    if (_tanggalMulai != null) {
      filters.add('Dari: ${DateFormat('dd/MM/yyyy').format(_tanggalMulai!)}');
    }

    if (_tanggalSelesai != null) {
      filters
          .add('Sampai: ${DateFormat('dd/MM/yyyy').format(_tanggalSelesai!)}');
    }

    return filters.join(' • ');
  }

  void _clearFilters() {
    setState(() {
      _selectedStatus = null;
      _tanggalMulai = null;
      _tanggalSelesai = null;
    });
    _loadData();
  }

  Widget _buildLoadingWidget() {
    return const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          CircularProgressIndicator(),
          SizedBox(height: AppConstants.paddingMedium),
          Text('Memuat tugas pengiriman...'),
        ],
      ),
    );
  }

  Widget _buildErrorWidget() {
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
            'Gagal memuat data',
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
            onPressed: _loadData,
            child: const Text('Coba Lagi'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyWidget(String title, String message, IconData icon) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            icon,
            size: 64,
            color: AppConstants.textSecondaryColor,
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          Text(
            title,
            style: AppConstants.titleStyle,
          ),
          const SizedBox(height: AppConstants.paddingSmall),
          Text(
            message,
            style: AppConstants.bodyStyle.copyWith(
              color: AppConstants.textSecondaryColor,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  void _showFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => _FilterDialog(
        selectedStatus: _selectedStatus,
        tanggalMulai: _tanggalMulai,
        tanggalSelesai: _tanggalSelesai,
        statusOptions: _statusOptions,
        onApply: (status, mulai, selesai) {
          setState(() {
            _selectedStatus = status;
            _tanggalMulai = mulai;
            _tanggalSelesai = selesai;
          });
          _loadData();
        },
      ),
    );
  }

  // FUNGSIONALITAS 119: Update status pengiriman menjadi selesai
  Future<void> _updateStatusSelesai(TugasPengiriman tugas) async {
    // Konfirmasi dialog
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Konfirmasi Pengiriman'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Apakah Anda yakin ingin menandai pengiriman ini sebagai selesai?',
            ),
            const SizedBox(height: AppConstants.paddingMedium),
            Container(
              padding: const EdgeInsets.all(AppConstants.paddingMedium),
              decoration: BoxDecoration(
                color: AppConstants.backgroundColor,
                borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Detail Pengiriman:',
                    style: AppConstants.bodyStyle.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: AppConstants.paddingSmall),
                  Text('• Nota: ${tugas.nomorNota}'),
                  Text('• Pembeli: ${tugas.namaPembeli}'),
                  Text('• Items: ${tugas.items.length} barang'),
                  Text('• Total: ${NumberFormat.currency(
                    locale: 'id_ID',
                    symbol: 'Rp ',
                    decimalDigits: 0,
                  ).format(tugas.totalHarga)}'),
                ],
              ),
            ),
            const SizedBox(height: AppConstants.paddingMedium),
            Container(
              padding: const EdgeInsets.all(AppConstants.paddingMedium),
              decoration: BoxDecoration(
                color: Colors.amber.withOpacity(0.1),
                borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                border: Border.all(color: Colors.amber.withOpacity(0.3)),
              ),
              child: Row(
                children: [
                  Icon(Icons.warning_amber_outlined, color: Colors.amber[700]),
                  const SizedBox(width: AppConstants.paddingSmall),
                  Expanded(
                    child: Text(
                      'Pastikan barang sudah diterima oleh pembeli sebelum menandai selesai.',
                      style: AppConstants.captionStyle.copyWith(
                        color: Colors.amber[700],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: Colors.white,
            ),
            child: const Text('Ya, Selesai'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    // Set loading state untuk tugas ini
    setState(() {
      _updatingTasks.add(tugas.idTransaksiPenjualan);
    });

    try {
      final success = await KurirService.updateStatusPengirimanSelesai(
        tugas.idTransaksiPenjualan,
      );

      if (success) {
        // Reload data
        await _loadData();

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  const Icon(Icons.check_circle, color: Colors.white),
                  const SizedBox(width: AppConstants.paddingSmall),
                  Text('Pengiriman ${tugas.nomorNota} berhasil diselesaikan'),
                ],
              ),
              backgroundColor: Colors.green,
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
              ),
            ),
          );
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  const Icon(Icons.error, color: Colors.white),
                  const SizedBox(width: AppConstants.paddingSmall),
                  const Text('Gagal mengupdate status pengiriman'),
                ],
              ),
              backgroundColor: AppConstants.errorColor,
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
              ),
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.error, color: Colors.white),
                const SizedBox(width: AppConstants.paddingSmall),
                Expanded(child: Text('Error: ${e.toString()}')),
              ],
            ),
            backgroundColor: AppConstants.errorColor,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
            ),
          ),
        );
      }
    } finally {
      // Remove loading state
      if (mounted) {
        setState(() {
          _updatingTasks.remove(tugas.idTransaksiPenjualan);
        });
      }
    }
  }
}

// Filter Dialog Widget (unchanged)
class _FilterDialog extends StatefulWidget {
  final String? selectedStatus;
  final DateTime? tanggalMulai;
  final DateTime? tanggalSelesai;
  final List<String> statusOptions;
  final Function(String?, DateTime?, DateTime?) onApply;

  const _FilterDialog({
    required this.selectedStatus,
    required this.tanggalMulai,
    required this.tanggalSelesai,
    required this.statusOptions,
    required this.onApply,
  });

  @override
  State<_FilterDialog> createState() => _FilterDialogState();
}

class _FilterDialogState extends State<_FilterDialog> {
  String? _selectedStatus;
  DateTime? _tanggalMulai;
  DateTime? _tanggalSelesai;

  @override
  void initState() {
    super.initState();
    _selectedStatus = widget.selectedStatus;
    _tanggalMulai = widget.tanggalMulai;
    _tanggalSelesai = widget.tanggalSelesai;
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Filter Riwayat'),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status filter
            const Text('Status:', style: AppConstants.titleStyle),
            const SizedBox(height: AppConstants.paddingSmall),
            DropdownButtonFormField<String>(
              value: _selectedStatus,
              decoration: AppConstants.inputDecoration('Pilih Status'),
              items: widget.statusOptions.map((status) {
                return DropdownMenuItem(
                  value: status == 'Semua' ? null : status,
                  child: Text(status),
                );
              }).toList(),
              onChanged: (value) {
                setState(() {
                  _selectedStatus = value;
                });
              },
            ),

            const SizedBox(height: AppConstants.paddingMedium),

            // Date range
            const Text('Rentang Tanggal:', style: AppConstants.titleStyle),
            const SizedBox(height: AppConstants.paddingSmall),

            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    decoration: AppConstants.inputDecoration('Tanggal Mulai'),
                    readOnly: true,
                    controller: TextEditingController(
                      text: _tanggalMulai != null
                          ? DateFormat('dd/MM/yyyy').format(_tanggalMulai!)
                          : '',
                    ),
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: _tanggalMulai ?? DateTime.now(),
                        firstDate: DateTime(2020),
                        lastDate: DateTime.now(),
                      );
                      if (date != null) {
                        setState(() {
                          _tanggalMulai = date;
                        });
                      }
                    },
                  ),
                ),
                const SizedBox(width: AppConstants.paddingSmall),
                Expanded(
                  child: TextFormField(
                    decoration: AppConstants.inputDecoration('Tanggal Selesai'),
                    readOnly: true,
                    controller: TextEditingController(
                      text: _tanggalSelesai != null
                          ? DateFormat('dd/MM/yyyy').format(_tanggalSelesai!)
                          : '',
                    ),
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: _tanggalSelesai ?? DateTime.now(),
                        firstDate: _tanggalMulai ?? DateTime(2020),
                        lastDate: DateTime.now(),
                      );
                      if (date != null) {
                        setState(() {
                          _tanggalSelesai = date;
                        });
                      }
                    },
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
      actions: [
        TextButton(
          onPressed: () {
            setState(() {
              _selectedStatus = null;
              _tanggalMulai = null;
              _tanggalSelesai = null;
            });
          },
          child: const Text('Reset'),
        ),
        TextButton(
          onPressed: () => Navigator.of(context).pop(),
          child: const Text('Batal'),
        ),
        ElevatedButton(
          onPressed: () {
            widget.onApply(_selectedStatus, _tanggalMulai, _tanggalSelesai);
            Navigator.of(context).pop();
          },
          child: const Text('Terapkan'),
        ),
      ],
    );
  }
}
