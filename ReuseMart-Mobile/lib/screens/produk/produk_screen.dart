import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/produk.dart';
import '../../services/api_service.dart';
import 'produkDetail_screen.dart';

class ProdukScreen extends StatefulWidget {
  @override
  _ProdukScreenState createState() => _ProdukScreenState();
}

class _ProdukScreenState extends State<ProdukScreen> {
  List<ProdukModel> produkList = [];
  List<KategoriModel> kategoriList = [];
  PaginationModel? pagination;

  bool isLoading = true;
  bool isLoadingMore = false;
  int? selectedKategoriId;
  String searchQuery = '';
  String? errorMessage;

  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();
  final ApiService _apiService = ApiService.instance;

  @override
  void initState() {
    super.initState();
    _loadInitialData();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadInitialData() async {
    print('Loading initial data...'); // Debug log
    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    try {
      await Future.wait([_loadKategori(), _loadProduk(isRefresh: true)]);
    } catch (e) {
      print('Error loading initial data: $e'); // Debug log
      setState(() {
        errorMessage = 'Gagal memuat data: $e';
      });
    } finally {
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> _loadKategori() async {
    try {
      print('Loading kategori...'); // Debug log
      final kategori = await _apiService.getKategori();
      setState(() {
        kategoriList = kategori;
      });
      print('Kategori loaded: ${kategori.length} items'); // Debug log
    } catch (e) {
      print('Error loading kategori: $e'); // Debug log
      _showError('Gagal memuat kategori: $e');
      // Tidak throw error agar produk masih bisa dimuat
    }
  }

  Future<void> _loadProduk({bool isRefresh = false, int page = 1}) async {
    try {
      print(
        'Loading produk... Page: $page, isRefresh: $isRefresh',
      ); // Debug log

      if (isRefresh) {
        setState(() {
          isLoading = true;
          produkList.clear();
          errorMessage = null;
        });
      } else {
        setState(() => isLoadingMore = true);
      }

      final response = await _apiService.getProduk(
        search: searchQuery.isNotEmpty ? searchQuery : null,
        kategori: selectedKategoriId,
        page: page,
        limit: 10,
      );

      print('Produk response: $response'); // Debug log

      if (response['success'] == true && response['data'] != null) {
        final data = response['data'];

        // Validasi struktur data
        if (data['produk'] != null && data['pagination'] != null) {
          final newProduk = (data['produk'] as List)
              .map((item) => ProdukModel.fromJson(item))
              .toList();

          setState(() {
            if (isRefresh) {
              produkList = newProduk;
            } else {
              produkList.addAll(newProduk);
            }
            pagination = PaginationModel.fromJson(data['pagination']);
            isLoading = false;
            isLoadingMore = false;
            errorMessage = null;
          });

          print('Produk loaded: ${newProduk.length} items'); // Debug log
        } else {
          throw Exception(
            'Data struktur tidak lengkap: produk atau pagination tidak ditemukan',
          );
        }
      } else {
        throw Exception(response['message'] ?? 'Response tidak valid');
      }
    } catch (e) {
      print('Error loading produk: $e'); // Debug log
      setState(() {
        isLoading = false;
        isLoadingMore = false;
        if (isRefresh) {
          errorMessage = 'Gagal memuat produk: $e';
        }
      });
      _showError('Gagal memuat produk: $e');
    }
  }

  void _onScroll() {
    if (_scrollController.position.pixels ==
            _scrollController.position.maxScrollExtent &&
        !isLoadingMore &&
        pagination?.hasNext == true) {
      _loadProduk(page: pagination!.currentPage + 1);
    }
  }

  void _showError(String message) {
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(message),
          backgroundColor: Colors.red,
          action: SnackBarAction(
            label: 'Coba Lagi',
            textColor: Colors.white,
            onPressed: () => _loadInitialData(),
          ),
        ),
      );
    }
  }

  void _searchProduk() {
    setState(() {
      searchQuery = _searchController.text.trim();
    });
    _loadProduk(isRefresh: true);
  }

  void _clearSearch() {
    _searchController.clear();
    setState(() {
      searchQuery = '';
    });
    _loadProduk(isRefresh: true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Produk'),
        backgroundColor: Colors.green,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: Icon(Icons.refresh),
            onPressed: _loadInitialData,
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: Column(
        children: [
          // Search and Filter Section
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: Column(
              children: [
                // Search Bar
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Cari produk...',
                    prefixIcon: Icon(Icons.search),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: Icon(Icons.clear),
                            onPressed: _clearSearch,
                          )
                        : null,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8.0),
                    ),
                    filled: true,
                    fillColor: Colors.grey[100],
                  ),
                  onSubmitted: (_) => _searchProduk(),
                ),
                SizedBox(height: 8),

                // Filter Kategori
                if (kategoriList.isNotEmpty)
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        FilterChip(
                          label: Text('Semua'),
                          selected: selectedKategoriId == null,
                          onSelected: (_) {
                            setState(() => selectedKategoriId = null);
                            _loadProduk(isRefresh: true);
                          },
                          backgroundColor: Colors.grey[200],
                          selectedColor: Colors.green[100],
                        ),
                        ...kategoriList
                            .map(
                              (kategori) => Padding(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 4.0,
                                ),
                                child: FilterChip(
                                  label: Text(kategori.nama),
                                  selected:
                                      selectedKategoriId == kategori.idKategori,
                                  onSelected: (_) {
                                    setState(
                                      () => selectedKategoriId =
                                          kategori.idKategori,
                                    );
                                    _loadProduk(isRefresh: true);
                                  },
                                  backgroundColor: Colors.grey[200],
                                  selectedColor: Colors.green[100],
                                ),
                              ),
                            )
                            .toList(),
                      ],
                    ),
                  ),
              ],
            ),
          ),

          // Content Area
          Expanded(child: _buildContent()),
        ],
      ),
    );
  }

  Widget _buildContent() {
    if (isLoading) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(color: Colors.green),
            SizedBox(height: 16),
            Text('Memuat produk...'),
          ],
        ),
      );
    }

    if (errorMessage != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 64, color: Colors.red),
            SizedBox(height: 16),
            Text(
              'Terjadi Kesalahan',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 8),
            Text(
              errorMessage!,
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey[600]),
            ),
            SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _loadInitialData,
              icon: Icon(Icons.refresh),
              label: Text('Coba Lagi'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                foregroundColor: Colors.white,
              ),
            ),
          ],
        ),
      );
    }

    if (produkList.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey),
            SizedBox(height: 16),
            Text(
              'Tidak ada produk ditemukan',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
            ),
            if (searchQuery.isNotEmpty || selectedKategoriId != null) ...[
              SizedBox(height: 8),
              Text(
                'Coba ubah kata kunci pencarian atau filter',
                style: TextStyle(color: Colors.grey[600]),
              ),
              SizedBox(height: 16),
              ElevatedButton(
                onPressed: () {
                  _clearSearch();
                  setState(() => selectedKategoriId = null);
                },
                child: Text('Reset Filter'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                ),
              ),
            ],
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => _loadProduk(isRefresh: true),
      color: Colors.green,
      child: ListView.builder(
        controller: _scrollController,
        physics: AlwaysScrollableScrollPhysics(),
        itemCount: produkList.length + (isLoadingMore ? 1 : 0),
        itemBuilder: (context, index) {
          if (index >= produkList.length) {
            return Container(
              padding: EdgeInsets.all(16),
              child: Center(
                child: CircularProgressIndicator(color: Colors.green),
              ),
            );
          }
          return _buildProductCard(produkList[index]);
        },
      ),
    );
  }

  Widget _buildProductCard(ProdukModel produk) {
    return Card(
      margin: EdgeInsets.symmetric(horizontal: 8.0, vertical: 4.0),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      child: InkWell(
        borderRadius: BorderRadius.circular(8),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) =>
                  ProdukDetailScreen(idProduk: produk.idProduk),
            ),
          );
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Product Image
            Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.vertical(
                    top: Radius.circular(8.0),
                  ),
                  child: AspectRatio(
                    aspectRatio: 16 / 9,
                    child: Image.asset(
                      produk.thumbnail,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) {
                        return Container(
                          color: Colors.grey[200],
                          child: Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.image_not_supported,
                                  size: 40,
                                  color: Colors.grey[400],
                                ),
                                SizedBox(height: 4),
                                Text(
                                  'Gambar tidak tersedia',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey[600],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                ),
                if (produk.garansi.isBergaransi)
                  Positioned(
                    top: 8,
                    left: 8,
                    child: Container(
                      padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.green,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        'Bergaransi',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
              ],
            ),

            // Product Info
            Padding(
              padding: EdgeInsets.all(12.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    produk.deskripsi,
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  SizedBox(height: 4),
                  Text(
                    produk.kategori,
                    style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                  ),
                  SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          'Rp ${_formatCurrency(produk.hargaJual)}',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Colors.green[700],
                          ),
                        ),
                      ),
                      if (produk.rating > 0)
                        Row(
                          children: [
                            Icon(Icons.star, size: 14, color: Colors.amber),
                            SizedBox(width: 2),
                            Text(
                              produk.rating.toStringAsFixed(1),
                              style: TextStyle(fontSize: 12),
                            ),
                          ],
                        ),
                    ],
                  ),
                ],
              ),
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
}

class ProdukSearchDelegate extends SearchDelegate {
  final List<ProdukModel> produkList;

  ProdukSearchDelegate(this.produkList);

  @override
  List<Widget> buildActions(BuildContext context) {
    return [
      IconButton(
        icon: Icon(Icons.clear),
        onPressed: () {
          query = '';
        },
      ),
    ];
  }

  @override
  Widget buildLeading(BuildContext context) {
    return IconButton(
      icon: Icon(Icons.arrow_back),
      onPressed: () {
        close(context, null);
      },
    );
  }

  @override
  Widget buildResults(BuildContext context) {
    final results = produkList
        .where(
          (produk) =>
              produk.deskripsi.toLowerCase().contains(query.toLowerCase()),
        )
        .toList();

    return _buildSearchResults(results);
  }

  @override
  Widget buildSuggestions(BuildContext context) {
    final suggestions = produkList
        .where(
          (produk) =>
              produk.deskripsi.toLowerCase().contains(query.toLowerCase()),
        )
        .toList();

    return _buildSearchResults(suggestions);
  }

  Widget _buildSearchResults(List<ProdukModel> results) {
    if (results.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.search_off, size: 64, color: Colors.grey),
            SizedBox(height: 16),
            Text('Tidak ada hasil ditemukan'),
          ],
        ),
      );
    }

    return ListView.builder(
      itemCount: results.length,
      itemBuilder: (context, index) {
        final produk = results[index];
        return ListTile(
          leading: ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: Image.network(
              produk.thumbnail,
              width: 50,
              height: 50,
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) {
                return Container(
                  width: 50,
                  height: 50,
                  color: Colors.grey[200],
                  child: Icon(Icons.image_not_supported, size: 20),
                );
              },
            ),
          ),
          title: Text(
            produk.deskripsi,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          subtitle: Text('Rp ${_formatCurrency(produk.hargaJual)}'),
          trailing: produk.garansi.isBergaransi
              ? Icon(Icons.verified, color: Colors.green, size: 16)
              : null,
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) =>
                    ProdukDetailScreen(idProduk: produk.idProduk),
              ),
            );
          },
        );
      },
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
}
