import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../navigation/app_page_route.dart';
import '../screens/thank_you_screen.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';
import '../widgets/app_page_bar.dart';

class PaymentScreen extends StatefulWidget {
  const PaymentScreen({
    super.key,
    required this.amount,
    required this.method,
    required this.purpose,
  });

  final int amount;
  final String method;
  final String purpose;

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  final phoneController = TextEditingController(text: '2557');
  bool loading = false;

  @override
  void dispose() {
    phoneController.dispose();
    super.dispose();
  }

  Future<void> _pay() async {
    if (phoneController.text.trim().length < 10) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Enter a valid phone number')),
      );
      return;
    }
    setState(() => loading = true);
    await Future<void>.delayed(const Duration(milliseconds: 1100));
    if (!mounted) return;
    // Root navigator: leave shell (and its bottom nav) so Thank You is
    // full-screen; avoids nesting another MainShell on "Back to Home".
    AppNav.pushAndRemoveUntil(
      context,
      ThankYouScreen(
        amount: widget.amount,
        reference: 'JKY${DateTime.now().millisecondsSinceEpoch % 1000000}',
        purpose: widget.purpose,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return Scaffold(
      appBar: const AppPageBar(title: 'Payment'),
      body: AnimatedSwitcher(
        duration: const Duration(milliseconds: 280),
        child: loading
            ? KeyedSubtree(
                key: const ValueKey('busy'),
                child: ScreenSkeletons.paymentBusy(),
              )
            : KeyedSubtree(
                key: const ValueKey('form'),
                child: ListView(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
                  children: [
                    AppCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            widget.purpose == 'Donation'
                                ? 'You are donating'
                                : 'You are paying',
                            style: GoogleFonts.dmSans(color: colors.textMuted),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            formatTzs(widget.amount),
                            style: GoogleFonts.cinzel(
                              color: AppColors.gold,
                              fontSize: 28,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'via ${widget.method}',
                            style: TextStyle(color: colors.textSecondary),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 22),
                    Text(
                      'Phone Number',
                      style: GoogleFonts.cinzel(
                        color: colors.textPrimary,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 10),
                    TextField(
                      controller: phoneController,
                      keyboardType: TextInputType.phone,
                      style: TextStyle(color: colors.textPrimary),
                      decoration: const InputDecoration(
                        hintText: '2557XXXXXXXX',
                        prefixIcon: Icon(Icons.phone, color: AppColors.gold),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'You will receive a push prompt on your phone to confirm payment.',
                      style: TextStyle(
                        color: colors.textMuted,
                        fontSize: 13,
                        height: 1.4,
                      ),
                    ),
                    const SizedBox(height: 28),
                    AppButton(
                      label: 'Pay Now',
                      variant: AppButtonVariant.green,
                      onPressed: _pay,
                    ),
                    const SizedBox(height: 12),
                    AppButton(
                      label: 'Cancel',
                      variant: AppButtonVariant.outline,
                      onPressed: () => Navigator.of(context).pop(),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}
