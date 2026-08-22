import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import '../models/product.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/common.dart';

class ProductDetailsScreen extends StatelessWidget {
  const ProductDetailsScreen({super.key, required this.product});

  final Product product;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final tagline = product.tagline(context).trim();
    final description = product.description(context).trim();
    final dateLabel = product.createdAt == null
        ? ''
        : DateFormat.yMMMMd().format(product.createdAt!.toLocal());

    return Scaffold(
      backgroundColor: colors.background,
      appBar: AppPageBar(title: product.name(context)),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 28),
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: product.image != null
                ? AppNetworkImage(
                    url: product.image!,
                    height: 220,
                    width: double.infinity,
                  )
                : Container(
                    height: 220,
                    color: colors.surfaceElevated,
                    alignment: Alignment.center,
                    child: Icon(
                      Icons.shopping_bag_outlined,
                      color: colors.textMuted,
                      size: 48,
                    ),
                  ),
          ),
          const SizedBox(height: 18),
          Text(
            product.name(context),
            style: GoogleFonts.cinzel(
              color: AppColors.gold,
              fontSize: 24,
              fontWeight: FontWeight.w700,
            ),
          ),
          if (dateLabel.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              dateLabel,
              style: GoogleFonts.dmSans(
                color: colors.textMuted,
                fontSize: 13,
              ),
            ),
          ],
          const SizedBox(height: 8),
          Text(
            formatTzs(product.price),
            style: GoogleFonts.dmSans(
              color: colors.textPrimary,
              fontSize: 20,
              fontWeight: FontWeight.w700,
            ),
          ),
          if (tagline.isNotEmpty) ...[
            const SizedBox(height: 12),
            Text(
              tagline,
              style: GoogleFonts.dmSans(
                color: colors.textSecondary,
                fontSize: 15,
                height: 1.4,
              ),
            ),
          ],
          if (description.isNotEmpty) ...[
            const SizedBox(height: 16),
            Text(
              'About this item',
              style: GoogleFonts.cinzel(
                color: colors.textPrimary,
                fontWeight: FontWeight.w700,
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              description,
              style: GoogleFonts.dmSans(
                color: colors.textSecondary,
                height: 1.5,
              ),
            ),
          ],
          const SizedBox(height: 24),
          AppButton(
            label: 'Buy Now',
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text(
                    'Online merch checkout is coming soon. Contact us to order.',
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}
