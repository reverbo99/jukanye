import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../navigation/app_page_route.dart';
import '../navigation/main_shell.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/common.dart';

class ThankYouScreen extends StatelessWidget {
  const ThankYouScreen({
    super.key,
    required this.amount,
    required this.reference,
    required this.purpose,
  });

  final int amount;
  final String reference;
  final String purpose;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(24, 40, 24, 28),
          child: Column(
            children: [
              const Spacer(),
              Container(
                width: 110,
                height: 110,
                decoration: BoxDecoration(
                  color: AppColors.green.withValues(alpha: 0.18),
                  shape: BoxShape.circle,
                  border: Border.all(color: AppColors.greenLight, width: 3),
                ),
                child: const Icon(Icons.check_rounded, color: AppColors.greenLight, size: 64),
              ),
              const SizedBox(height: 28),
              Text(
                'Thank You!',
                style: GoogleFonts.cinzel(
                  color: AppColors.gold,
                  fontSize: 32,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Payment Successful!',
                style: GoogleFonts.dmSans(
                  color: colors.textPrimary,
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 24),
              AppCard(
                child: Column(
                  children: [
                    _row(context, 'Purpose', purpose),
                    const Divider(height: 22),
                    _row(context, 'Amount', formatTzs(amount)),
                    const Divider(height: 22),
                    _row(context, 'Reference', reference),
                  ],
                ),
              ),
              const Spacer(flex: 2),
              AppButton(
                label: 'Back to Home',
                variant: AppButtonVariant.green,
                onPressed: () {
                  AppNav.pushAndRemoveUntil(context, const MainShell());
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _row(BuildContext context, String label, String value) {
    final colors = AppColors.of(context);
    return Row(
      children: [
        Text(label, style: TextStyle(color: colors.textMuted)),
        const Spacer(),
        Text(
          value,
          style: TextStyle(
            color: colors.textPrimary,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }
}
