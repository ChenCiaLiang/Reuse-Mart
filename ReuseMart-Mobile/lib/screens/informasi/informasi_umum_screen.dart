import 'package:flutter/material.dart';
import '../../../constants/app_constants.dart';

class InformasiUmumScreen extends StatelessWidget {
  const InformasiUmumScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppConstants.backgroundColor,
      appBar: AppBar(
        title: const Text('Tentang ReUseMart'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            _buildHeroSection(context),
            _buildAboutSection(),
            _buildCaraKerjaSection(),
            _buildKategoriSection(),
            _buildKeuntunganSection(),
            _buildKontakSection(),
            const SizedBox(height: AppConstants.paddingLarge),
          ],
        ),
      ),
    );
  }

  Widget _buildHeroSection(BuildContext context) {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppConstants.primaryColor,
            AppConstants.secondaryColor,
          ],
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(AppConstants.paddingLarge),
        child: Column(
          children: [
            const Icon(
              Icons.recycling,
              size: 64,
              color: Colors.white,
            ),
            const SizedBox(height: AppConstants.paddingMedium),
            Text(
              'ReUseMart',
              style: AppConstants.headingStyle.copyWith(
                color: Colors.white,
                fontSize: 28,
              ),
            ),
            const SizedBox(height: AppConstants.paddingSmall),
            Text(
              'Platform untuk menjual dan membeli barang bekas berkualitas',
              style: AppConstants.bodyStyle.copyWith(
                color: Colors.white70,
                fontSize: 16,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppConstants.paddingMedium),
          ],
        ),
      ),
    );
  }

  Widget _buildAboutSection() {
    return Container(
      padding: const EdgeInsets.all(AppConstants.paddingLarge),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Tentang ReUseMart',
            style: AppConstants.titleStyle.copyWith(fontSize: 20),
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          Text(
            'ReUseMart adalah perusahaan yang bergerak di bidang penjualan barang bekas berkualitas yang berbasis di Yogyakarta. Didirikan oleh Pak Raka Pratama, seorang pengusaha muda yang memiliki kepedulian tinggi terhadap isu lingkungan, pengelolaan limbah, dan konsep ekonomi sirkular.',
            style: AppConstants.bodyStyle.copyWith(
              height: 1.5,
            ),
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          Container(
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            decoration: BoxDecoration(
              color: AppConstants.primaryColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
              border:
                  Border.all(color: AppConstants.primaryColor.withOpacity(0.3)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.visibility_outlined,
                      color: AppConstants.primaryColor,
                      size: 20,
                    ),
                    const SizedBox(width: AppConstants.paddingSmall),
                    Text(
                      'Visi Kami',
                      style: AppConstants.bodyStyle.copyWith(
                        fontWeight: FontWeight.w600,
                        color: AppConstants.primaryColor,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppConstants.paddingSmall),
                Text(
                  'Mengurangi penumpukan sampah dan memberikan kesempatan kedua bagi barang-barang bekas yang masih layak pakai.',
                  style: AppConstants.bodyStyle.copyWith(
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: AppConstants.paddingMedium),
                Row(
                  children: [
                    Icon(
                      Icons.person_outline,
                      color: AppConstants.primaryColor,
                      size: 20,
                    ),
                    const SizedBox(width: AppConstants.paddingSmall),
                    Text(
                      'Founder: Pak Raka Pratama',
                      style: AppConstants.bodyStyle.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppConstants.paddingSmall),
                Row(
                  children: [
                    Icon(
                      Icons.location_on_outlined,
                      color: AppConstants.primaryColor,
                      size: 20,
                    ),
                    const SizedBox(width: AppConstants.paddingSmall),
                    Text(
                      'Lokasi: Yogyakarta',
                      style: AppConstants.bodyStyle.copyWith(
                        fontWeight: FontWeight.w600,
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

  Widget _buildCaraKerjaSection() {
    final caraKerja = [
      {
        'step': 1,
        'judul': 'Penitipan Barang',
        'deskripsi':
            'Titipkan barang bekas berkualitas Anda di gudang kami. Tim kami akan melakukan pemeriksaan kualitas dan memasarkannya untuk Anda.',
        'icon': Icons.inventory_2_outlined,
      },
      {
        'step': 2,
        'judul': 'Pemasaran',
        'deskripsi':
            'Kami akan memasarkan barang Anda melalui platform kami. Anda tidak perlu repot memasukkan data, memfoto, atau melayani pertanyaan pembeli.',
        'icon': Icons.store_outlined,
      },
      {
        'step': 3,
        'judul': 'Penjualan & Pembayaran',
        'deskripsi':
            'Ketika barang terjual, kami menangani proses pengiriman dan pembayaran. Anda menerima dana setelah dipotong komisi 20% untuk layanan kami.',
        'icon': Icons.payment_outlined,
      },
    ];

    return Container(
      color: AppConstants.backgroundColor,
      padding: const EdgeInsets.all(AppConstants.paddingLarge),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Cara Kerja Kami',
            style: AppConstants.titleStyle.copyWith(fontSize: 20),
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          ...caraKerja
              .map((step) => Container(
                    margin: const EdgeInsets.only(
                        bottom: AppConstants.paddingMedium),
                    padding: const EdgeInsets.all(AppConstants.paddingMedium),
                    decoration: BoxDecoration(
                      color: AppConstants.surfaceColor,
                      borderRadius:
                          BorderRadius.circular(AppConstants.radiusMedium),
                      boxShadow: AppConstants.defaultShadow,
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: AppConstants.primaryColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Center(
                            child: Text(
                              '${step['step']}',
                              style: AppConstants.bodyStyle.copyWith(
                                fontWeight: FontWeight.bold,
                                color: AppConstants.primaryColor,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: AppConstants.paddingMedium),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                step['judul'] as String,
                                style: AppConstants.bodyStyle.copyWith(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 16,
                                ),
                              ),
                              const SizedBox(height: AppConstants.paddingSmall),
                              Text(
                                step['deskripsi'] as String,
                                style: AppConstants.bodyStyle.copyWith(
                                  color: AppConstants.textSecondaryColor,
                                  height: 1.4,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ))
              .toList(),

          // Kebijakan Penitipan
          Container(
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            decoration: BoxDecoration(
              color: Colors.blue.shade50,
              borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
              border: Border.all(color: Colors.blue.shade200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.policy_outlined,
                      color: Colors.blue.shade700,
                      size: 20,
                    ),
                    const SizedBox(width: AppConstants.paddingSmall),
                    Text(
                      'Kebijakan Penitipan',
                      style: AppConstants.bodyStyle.copyWith(
                        fontWeight: FontWeight.w600,
                        color: Colors.blue.shade700,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppConstants.paddingSmall),
                const Text(
                  '• Masa penitipan: 30 hari (dapat diperpanjang 1x)\n'
                  '• Komisi normal: 20%, perpanjangan: 30%\n'
                  '• Bonus 10% komisi jika laku < 7 hari\n'
                  '• Barang tidak diambil akan didonasikan',
                  style: TextStyle(
                    fontSize: 13,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKategoriSection() {
    final kategori = [
      {
        'nama': 'Elektronik & Gadget',
        'icon': Icons.devices_outlined,
        'color': Colors.blue
      },
      {
        'nama': 'Pakaian & Aksesori',
        'icon': Icons.checkroom_outlined,
        'color': Colors.purple
      },
      {
        'nama': 'Perabotan Rumah Tangga',
        'icon': Icons.chair_outlined,
        'color': Colors.orange
      },
      {
        'nama': 'Buku & Alat Tulis',
        'icon': Icons.book_outlined,
        'color': Colors.red
      },
      {
        'nama': 'Hobi & Mainan',
        'icon': Icons.toys_outlined,
        'color': Colors.green
      },
      {
        'nama': 'Perlengkapan Bayi & Anak',
        'icon': Icons.child_care_outlined,
        'color': Colors.pink
      },
      {
        'nama': 'Otomotif & Aksesori',
        'icon': Icons.directions_car_outlined,
        'color': Colors.indigo
      },
      {
        'nama': 'Taman & Outdoor',
        'icon': Icons.park_outlined,
        'color': Colors.teal
      },
      {
        'nama': 'Peralatan Kantor & Industri',
        'icon': Icons.business_outlined,
        'color': Colors.grey
      },
      {
        'nama': 'Kosmetik & Perawatan Diri',
        'icon': Icons.face_outlined,
        'color': Colors.deepOrange
      },
    ];

    return Container(
      padding: const EdgeInsets.all(AppConstants.paddingLarge),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Kategori Barang',
            style: AppConstants.titleStyle.copyWith(fontSize: 20),
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          Text(
            'ReUseMart menerima berbagai jenis barang bekas berkualitas dalam kategori berikut:',
            style: AppConstants.bodyStyle.copyWith(
              color: AppConstants.textSecondaryColor,
            ),
          ),
          const SizedBox(height: AppConstants.paddingMedium),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: AppConstants.paddingSmall,
              mainAxisSpacing: AppConstants.paddingSmall,
              childAspectRatio: 2.5,
            ),
            itemCount: kategori.length,
            itemBuilder: (context, index) {
              final item = kategori[index];
              return Container(
                padding: const EdgeInsets.all(AppConstants.paddingSmall),
                decoration: BoxDecoration(
                  color: AppConstants.surfaceColor,
                  borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: (item['color'] as Color).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Icon(
                        item['icon'] as IconData,
                        size: 16,
                        color: item['color'] as Color,
                      ),
                    ),
                    const SizedBox(width: AppConstants.paddingSmall),
                    Expanded(
                      child: Text(
                        item['nama'] as String,
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildKeuntunganSection() {
    return Container(
      color: AppConstants.backgroundColor,
      padding: const EdgeInsets.all(AppConstants.paddingLarge),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Keuntungan ReUseMart',
            style: AppConstants.titleStyle.copyWith(fontSize: 20),
          ),
          const SizedBox(height: AppConstants.paddingMedium),

          // Untuk Penitip
          Container(
            margin: const EdgeInsets.only(bottom: AppConstants.paddingMedium),
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            decoration: BoxDecoration(
              color: AppConstants.surfaceColor,
              borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
              boxShadow: AppConstants.defaultShadow,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.person_add_outlined,
                      color: AppConstants.primaryColor,
                      size: 20,
                    ),
                    const SizedBox(width: AppConstants.paddingSmall),
                    Text(
                      'Bagi Penitip',
                      style: AppConstants.bodyStyle.copyWith(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppConstants.paddingSmall),
                const Text(
                  '• Tidak perlu repot mengurus penjualan\n'
                  '• Bonus 10% komisi jika laku cepat\n'
                  '• Poin reward untuk donasi barang\n'
                  '• Status Top Seller dan bonus',
                  style: TextStyle(
                    fontSize: 14,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),

          // Untuk Pembeli
          Container(
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            decoration: BoxDecoration(
              color: AppConstants.surfaceColor,
              borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
              boxShadow: AppConstants.defaultShadow,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.shopping_cart_outlined,
                      color: AppConstants.secondaryColor,
                      size: 20,
                    ),
                    const SizedBox(width: AppConstants.paddingSmall),
                    Text(
                      'Bagi Pembeli',
                      style: AppConstants.bodyStyle.copyWith(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppConstants.paddingSmall),
                const Text(
                  '• Barang berkualitas harga terjangkau\n'
                  '• Poin loyalitas setiap pembelian\n'
                  '• Ongkir gratis min. Rp1.500.000\n'
                  '• Tukar poin dengan merchandise',
                  style: TextStyle(
                    fontSize: 14,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKontakSection() {
    return Container(
      padding: const EdgeInsets.all(AppConstants.paddingLarge),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Hubungi Kami',
            style: AppConstants.titleStyle.copyWith(fontSize: 20),
          ),
          const SizedBox(height: AppConstants.paddingMedium),

          Container(
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            decoration: BoxDecoration(
              color: AppConstants.surfaceColor,
              borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
              boxShadow: AppConstants.defaultShadow,
            ),
            child: Column(
              children: [
                _buildKontakItem(
                  Icons.location_on_outlined,
                  'Alamat',
                  'Jl. Green Eco Park No. 456, Yogyakarta',
                ),
                const Divider(),
                _buildKontakItem(
                  Icons.phone_outlined,
                  'Telepon',
                  '+62 274 123456',
                ),
                const Divider(),
                _buildKontakItem(
                  Icons.email_outlined,
                  'Email',
                  'info@reusemart.com',
                ),
                const Divider(),
                _buildKontakItem(
                  Icons.access_time_outlined,
                  'Jam Operasional',
                  '08:00 - 20:00 WIB (Setiap Hari)',
                ),
              ],
            ),
          ),

          const SizedBox(height: AppConstants.paddingMedium),

          // Social Media
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _buildSocialButton(Icons.facebook, Colors.blue),
              const SizedBox(width: AppConstants.paddingMedium),
              _buildSocialButton(Icons.camera_alt, Colors.pink),
              const SizedBox(width: AppConstants.paddingMedium),
              _buildSocialButton(Icons.alternate_email, Colors.lightBlue),
              const SizedBox(width: AppConstants.paddingMedium),
              _buildSocialButton(Icons.play_arrow, Colors.red),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildKontakItem(IconData icon, String title, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: AppConstants.paddingSmall),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            icon,
            color: AppConstants.primaryColor,
            size: 20,
          ),
          const SizedBox(width: AppConstants.paddingMedium),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: AppConstants.captionStyle.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  value,
                  style: AppConstants.bodyStyle,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSocialButton(IconData icon, Color color) {
    return Container(
      width: 40,
      height: 40,
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Icon(
        icon,
        color: color,
        size: 20,
      ),
    );
  }
}
