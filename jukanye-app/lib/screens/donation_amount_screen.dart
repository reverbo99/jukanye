import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../data/app_data.dart';
import '../navigation/app_page_route.dart';
import '../screens/payment_screen.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/common.dart';
import '../widgets/app_page_bar.dart';

class DonationAmountScreen extends StatefulWidget {
  const DonationAmountScreen({
    super.key,
    this.title = 'One-Time Donation',
    this.presetAmount,
    this.isTicketPurchase = false,
    this.ticketTierId,
  });

  final String title;
  final int? presetAmount;
  final bool isTicketPurchase;
  final int? ticketTierId;

  @override
  State<DonationAmountScreen> createState() => _DonationAmountScreenState();
}

class _DonationAmountScreenState extends State<DonationAmountScreen> {
  late int? selectedAmount;
  String method = 'M-Pesa';
  final customController = TextEditingController();

  @override
  void initState() {
    super.initState();
    selectedAmount = widget.presetAmount ?? 10000;
    if (widget.presetAmount != null) {
      customController.text = widget.presetAmount.toString();
    }
  }

  @override
  void dispose() {
    customController.dispose();
    super.dispose();
  }

  int? get amount {
    final custom = int.tryParse(customController.text.replaceAll(',', ''));
    if (custom != null && custom > 0) return custom;
    return selectedAmount;
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final methods = ['M-Pesa', 'Airtel Money', 'Tigo Pesa', 'Card'];

    return Scaffold(
      appBar: AppPageBar(title: widget.title),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
        children: [
          Text(
            widget.isTicketPurchase ? 'Confirm payment amount' : 'Select amount',
            style: GoogleFonts.cinzel(
              color: colors.textPrimary,
              fontWeight: FontWeight.w700,
              fontSize: 18,
            ),
          ),
          const SizedBox(height: 14),
          if (!widget.isTicketPurchase)
            Wrap(
              spacing: 10,
              runSpacing: 10,
              children: [
                for (final value in AppData.donationAmounts)
                  ChoiceChip(
                    label: Text(formatTzs(value)),
                    selected: selectedAmount == value && customController.text.isEmpty,
                    selectedColor: AppColors.gold,
                    labelStyle: TextStyle(
                      color: selectedAmount == value && customController.text.isEmpty
                          ? Colors.black
                          : colors.textSecondary,
                      fontWeight: FontWeight.w600,
                    ),
                    backgroundColor: colors.card,
                    side: BorderSide.none,
                    onSelected: (_) {
                      setState(() {
                        selectedAmount = value;
                        customController.clear();
                      });
                    },
                  ),
              ],
            ),
          const SizedBox(height: 16),
          TextField(
            controller: customController,
            keyboardType: TextInputType.number,
            style: TextStyle(color: colors.textPrimary),
            decoration: InputDecoration(
              labelText: widget.isTicketPurchase ? 'Amount' : 'Other Amount (TZS)',
              labelStyle: TextStyle(color: colors.textMuted),
              prefixText: 'TZS ',
            ),
            onChanged: (_) => setState(() => selectedAmount = null),
          ),
          const SizedBox(height: 24),
          Text(
            'Payment Method',
            style: GoogleFonts.cinzel(
              color: colors.textPrimary,
              fontWeight: FontWeight.w700,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 10),
          ...methods.map(
            (m) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: AppCard(
                onTap: () => setState(() => method = m),
                color: method == m ? colors.surfaceElevated : colors.card,
                child: Row(
                  children: [
                    Icon(
                      m == 'Card' ? Icons.credit_card : Icons.phone_android,
                      color: AppColors.gold,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        m,
                        style: TextStyle(
                          color: colors.textPrimary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    Icon(
                      method == m ? Icons.radio_button_checked : Icons.radio_button_off,
                      color: method == m ? AppColors.gold : colors.textMuted,
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(height: 20),
          AppButton(
            label: 'Continue',
            variant: AppButtonVariant.green,
            onPressed: () {
              final payAmount = amount;
              if (payAmount == null || payAmount <= 0) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Enter a valid amount')),
                );
                return;
              }
              AppNav.push(
                context,
                PaymentScreen(
                  amount: payAmount,
                  method: method,
                  purpose: widget.isTicketPurchase ? 'Ticket purchase' : 'Donation',
                  ticketTierId: widget.ticketTierId,
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}
