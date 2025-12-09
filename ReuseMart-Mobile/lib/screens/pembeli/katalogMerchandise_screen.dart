// lib/screens/katalog_merchandise_screen.dart
import 'package:flutter/material.dart';
import '../../models/merchandise.dart';
import '../../services/merchandise_service.dart';
import '../../constants/app_constants.dart';
import 'history_klaim_screen.dart';
import '../auth/login_screen.dart';
import '../../widgets/merchandise_item_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/error_widget.dart' as custom;
import '../../widgets/poin_header_widget.dart';

class KatalogMerchandiseScreen extends StatefulWidget {
  const KatalogMerchandiseScreen({Key? key}) : super(key: key);

  @override
  State<KatalogMerchandiseScreen> createState() =>
      _KatalogMerchandiseScreenState();
}

class _KatalogMerchandiseScreenState extends State<KatalogMerchandiseScreen> {
  List<Merchandise> merchandiseList = [];
  PoinData? poinData;
  bool isLoading = true;
  bool isLoggedIn = true;
  String? errorMessage;

  @override
  void initState() {
    super.initState();
    _initializeData();
  }

  Future<void> _initializeData() async {
    await _loadKatalogMerchandise();
    if (isLoggedIn) {
      await _loadUserPoin();
    }
  }

  Future<void> _loadKatalogMerchandise() async {
    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    final response = await MerchandiseService.getKatalogMerchandise();

    setState(() {
      isLoading = false;
      if (response.success && response.data != null) {
        merchandiseList = response.data!;
      } else {
        errorMessage = response.message;
      }
    });
  }

  Future<void> _loadUserPoin() async {
    if (!isLoggedIn) return;

    final response = await MerchandiseService.getPoinPembeli();

    setState(() {
      if (response.success && response.data != null) {
        poinData = response.data!;
      }
    });
  }

  Future<void> _showKlaimDialog(Merchandise merchandise) async {
    if (!isLoggedIn) {
      _showLoginDialog();
      return;
    }

    // Cek poin cukup atau tidak
    final userPoin = poinData?.poin ?? 0;
    final poinDibutuhkan = merchandise.jumlahPoin;

    if (userPoin < poinDibutuhkan) {
      _showInsufficientPointsDialog(poinDibutuhkan, userPoin);
      return;
    }

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Konfirmasi Klaim'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Apakah Anda yakin ingin menukar poin dengan merchandise berikut?',
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.asset(
                    merchandise.gambar,
                    width: 60,
                    height: 60,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) {
                      return Container(
                        width: 60,
                        height: 60,
                        color: Colors.grey[300],
                        child: const Icon(
                          Icons.card_giftcard,
                          color: Colors.grey,
                        ),
                      );
                    },
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        merchandise.nama,
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(
                            Icons.monetization_on,
                            size: 16,
                            color: AppConstants.primaryColor,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            '${merchandise.jumlahPoin} poin',
                            style: const TextStyle(
                              color: AppConstants.primaryColor,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppConstants.backgroundColor,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Poin Anda saat ini:'),
                      Text(
                        '${userPoin} poin',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Poin yang digunakan:'),
                      Text(
                        '${poinDibutuhkan} poin',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          color: AppConstants.errorColor,
                        ),
                      ),
                    ],
                  ),
                  const Divider(),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Sisa poin:'),
                      Text(
                        '${userPoin - poinDibutuhkan} poin',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
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
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppConstants.primaryColor,
            ),
            child: const Text('Ya, Klaim'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await _klaimMerchandise(merchandise.idMerchandise);
    }
  }

  Future<void> _klaimMerchandise(int idMerchandise) async {
    // Show loading dialog
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const AlertDialog(
        content: Row(
          children: [
            CircularProgressIndicator(),
            SizedBox(width: 16),
            Text('Memproses klaim...'),
          ],
        ),
      ),
    );

    final response = await MerchandiseService.klaimMerchandise(idMerchandise);

    // Close loading dialog
    Navigator.of(context).pop();

    if (response.success && response.data != null) {
      // Klaim berhasil
      _showSuccessDialog(response.data!);
      // Refresh data
      await _loadKatalogMerchandise();
      await _loadUserPoin();
    } else {
      // Klaim gagal
      _showErrorDialog(response.message);
    }
  }

  void _showSuccessDialog(KlaimResponse klaimData) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            const Icon(Icons.check_circle, color: AppConstants.primaryColor),
            const SizedBox(width: 8),
            const Text('Klaim Berhasil!'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Merchandise ${klaimData.merchandise.nama} berhasil diklaim!',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppConstants.backgroundColor,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Poin digunakan:'),
                      Text('${klaimData.poinDigunakan} poin'),
                    ],
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Sisa poin:'),
                      Text(
                        '${klaimData.sisaPoin} poin',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          color: AppConstants.primaryColor,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.blue.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.info, color: Colors.blue),
                      SizedBox(width: 8),
                      Text(
                        'Instruksi Pengambilan:',
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(klaimData.instruksi),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('OK'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (context) => const HistoryKlaimScreen(),
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppConstants.primaryColor,
            ),
            child: const Text('Lihat History'),
          ),
        ],
      ),
    );
  }

  void _showErrorDialog(String message) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.error, color: AppConstants.errorColor),
            SizedBox(width: 8),
            Text('Klaim Gagal'),
          ],
        ),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  void _showLoginDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Login Diperlukan'),
        content: const Text(
          'Anda harus login terlebih dahulu untuk melakukan klaim merchandise.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              Navigator.of(context).push(
                MaterialPageRoute(builder: (context) => const LoginScreen()),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppConstants.primaryColor,
            ),
            child: const Text('Login'),
          ),
        ],
      ),
    );
  }

  void _showInsufficientPointsDialog(int poinDibutuhkan, int poinAnda) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.warning, color: AppConstants.secondaryColor),
            SizedBox(width: 8),
            Text('Poin Tidak Cukup'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Poin Anda tidak mencukupi untuk menukar merchandise ini.',
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppConstants.backgroundColor,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Poin dibutuhkan:'),
                      Text(
                        '$poinDibutuhkan poin',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Poin Anda:'),
                      Text(
                        '$poinAnda poin',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const Divider(),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Kekurangan:'),
                      Text(
                        '${poinDibutuhkan - poinAnda} poin',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          color: AppConstants.errorColor,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Katalog Merchandise'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          if (isLoggedIn)
            IconButton(
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => const HistoryKlaimScreen(),
                  ),
                );
              },
              icon: const Icon(Icons.history),
              tooltip: 'History Klaim',
            ),
          IconButton(
            onPressed: _loadKatalogMerchandise,
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _initializeData,
        child: Column(
          children: [
            // Header dengan informasi poin
            PoinHeaderWidget(
              isLoggedIn: isLoggedIn,
              poinData: poinData,
              onLoginPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute(builder: (context) => const LoginScreen()),
                );
              },
            ),

            // Content area
            Expanded(child: _buildContent()),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    if (isLoading) {
      return const LoadingWidget(message: 'Memuat katalog merchandise...');
    }

    if (errorMessage != null) {
      return custom.ErrorWidget(
        message: errorMessage!,
        onRetry: _loadKatalogMerchandise,
      );
    }

    if (merchandiseList.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.card_giftcard, size: 64, color: Colors.grey),
            SizedBox(height: 16),
            Text(
              'Tidak ada merchandise tersedia',
              style: TextStyle(fontSize: 16, color: Colors.grey),
            ),
          ],
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.all(AppConstants.paddingMedium),
      child: GridView.builder(
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          childAspectRatio: 0.75,
          crossAxisSpacing: AppConstants.paddingMedium,
          mainAxisSpacing: AppConstants.paddingMedium,
        ),
        itemCount: merchandiseList.length,
        itemBuilder: (context, index) {
          final merchandise = merchandiseList[index];
          return MerchandiseItemWidget(
            merchandise: merchandise,
            onTap: () => _showKlaimDialog(merchandise),
          );
        },
      ),
    );
  }
}
