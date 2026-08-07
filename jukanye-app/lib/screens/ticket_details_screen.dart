import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../data/app_images.dart';
import '../models/ticket_tier.dart';
import '../navigation/app_page_route.dart';
import '../screens/donation_amount_screen.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';

class TicketDetailsScreen extends StatefulWidget {
  const TicketDetailsScreen({super.key, required this.ticket});

  final TicketTier ticket;

  @override
  State<TicketDetailsScreen> createState() => _TicketDetailsScreenState();
}

class _TicketDetailsScreenState extends State<TicketDetailsScreen> {
  int quantity = 1;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final total = widget.ticket.price * quantity;

    return Scaffold(
      appBar: AppPageBar(title: widget.ticket.name(context)),
      body: PageSkeletonGate(
        skeleton: ScreenSkeletons.detail(),
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 8, 20, 28),
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: const AppNetworkImage(
                url: AppImages.concertLights,
                height: 180,
                width: double.infinity,
              ),
            ),
            const SizedBox(height: 18),
            Text(
              widget.ticket.name(context),
              style: GoogleFonts.cinzel(
                color: AppColors.gold,
                fontSize: 24,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              formatTzs(widget.ticket.price),
              style: GoogleFonts.dmSans(
                color: colors.textPrimary,
                fontSize: 20,
                fontWeight: FontWeight.w700,
              ),
            ),
            if (widget.ticket.description(context).trim().isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                widget.ticket.description(context),
                style: TextStyle(color: colors.textSecondary, height: 1.5),
              ),
            ],
            const SizedBox(height: 20),
            Text(
              'What\'s Included',
              style: GoogleFonts.cinzel(
                color: colors.textPrimary,
                fontWeight: FontWeight.w700,
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 10),
            if (widget.ticket.includes.isEmpty)
              Text(
                'Details coming soon.',
                style: TextStyle(color: colors.textMuted),
              )
            else
              ...widget.ticket.includes.map(
                (item) => Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.check_circle,
                        color: AppColors.greenLight,
                        size: 18,
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          item,
                          style: TextStyle(color: colors.textSecondary),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            const SizedBox(height: 20),
            AppCard(
              child: Row(
                children: [
                  Text(
                    'Quantity',
                    style: TextStyle(
                      color: colors.textPrimary,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const Spacer(),
                  _QtyButton(
                    icon: Icons.remove,
                    onTap: () {
                      if (quantity > 1) setState(() => quantity--);
                    },
                  ),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                    child: Text(
                      '$quantity',
                      style: const TextStyle(
                        color: AppColors.gold,
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                  _QtyButton(
                    icon: Icons.add,
                    onTap: () => setState(() => quantity++),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),
            AppButton(
              label: 'Proceed to Checkout · ${formatTzs(total)}',
              variant: AppButtonVariant.green,
              onPressed: () {
                AppNav.push(
                  context,
                  DonationAmountScreen(
                    isTicketPurchase: true,
                    presetAmount: total,
                    title: 'Checkout',
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _QtyButton extends StatelessWidget {
  const _QtyButton({required this.icon, required this.onTap});

  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        width: 34,
        height: 34,
        decoration: BoxDecoration(
          color: colors.surfaceElevated,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: colors.border),
        ),
        child: Icon(icon, size: 18, color: colors.textPrimary),
      ),
    );
  }
}
