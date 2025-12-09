// lib/widgets/merchandise_item_widget.dart
import 'package:flutter/material.dart';
import '../models/merchandise.dart';
import '../constants/app_constants.dart';

class MerchandiseItemWidget extends StatelessWidget {
  final Merchandise merchandise;
  final VoidCallback onTap;

  const MerchandiseItemWidget({
    Key? key,
    required this.merchandise,
    required this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
      ),
      child: InkWell(
        onTap: merchandise.tersedia ? onTap : null,
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
            color: merchandise.tersedia ? Colors.white : Colors.grey.shade100,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Gambar merchandise
              Expanded(
                flex: 3,
                child: Container(
                  decoration: const BoxDecoration(
                    borderRadius: BorderRadius.only(
                      topLeft: Radius.circular(AppConstants.radiusMedium),
                      topRight: Radius.circular(AppConstants.radiusMedium),
                    ),
                  ),
                  child: Stack(
                    children: [
                      ClipRRect(
                        borderRadius: const BorderRadius.only(
                          topLeft: Radius.circular(AppConstants.radiusMedium),
                          topRight: Radius.circular(AppConstants.radiusMedium),
                        ),
                        child: Image.asset(
                          merchandise.gambar,
                          width: double.infinity,
                          height: double.infinity,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) {
                            return Container(
                              color: Colors.grey.shade200,
                              child: const Icon(
                                Icons.card_giftcard,
                                size: 48,
                                color: Colors.grey,
                              ),
                            );
                          },
                        ),
                      ),

                      // Stock indicator
                      Positioned(
                        top: 8,
                        right: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: merchandise.tersedia
                                ? AppConstants.primaryColor.withOpacity(0.9)
                                : AppConstants.errorColor.withOpacity(0.9),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            'Stok: ${merchandise.stok}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),

                      // Overlay jika tidak tersedia
                      if (!merchandise.tersedia)
                        Container(
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.5),
                            borderRadius: const BorderRadius.only(
                              topLeft: Radius.circular(
                                AppConstants.radiusMedium,
                              ),
                              topRight: Radius.circular(
                                AppConstants.radiusMedium,
                              ),
                            ),
                          ),
                          child: const Center(
                            child: Text(
                              'HABIS',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),

              // Informasi merchandise
              Expanded(
                flex: 2,
                child: Padding(
                  padding: const EdgeInsets.all(AppConstants.paddingMedium),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Nama merchandise
                      Text(
                        merchandise.nama,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: AppConstants.textPrimaryColor,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),

                      const Spacer(),

                      // Poin dan status
                      Row(
                        children: [
                          Icon(
                            Icons.monetization_on,
                            size: 16,
                            color: merchandise.tersedia
                                ? AppConstants.primaryColor
                                : Colors.grey,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            '${merchandise.jumlahPoin} poin',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: merchandise.tersedia
                                  ? AppConstants.primaryColor
                                  : Colors.grey,
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 4),

                      // Status tersedia
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: merchandise.tersedia
                              ? AppConstants.primaryColor.withOpacity(0.1)
                              : AppConstants.errorColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          merchandise.tersedia ? 'Tersedia' : 'Habis',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: merchandise.tersedia
                                ? AppConstants.primaryColor
                                : AppConstants.errorColor,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
