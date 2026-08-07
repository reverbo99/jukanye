import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';
import '../widgets/app_page_bar.dart';

class MyTicketsScreen extends StatefulWidget {
  const MyTicketsScreen({super.key});

  @override
  State<MyTicketsScreen> createState() => _MyTicketsScreenState();
}

class _MyTicketsScreenState extends State<MyTicketsScreen> {
  String tab = 'Upcoming';

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return Scaffold(
      appBar: const AppPageBar(title: 'My Tickets'),
      body: PageSkeletonGate(
        skeleton: ScreenSkeletons.detail(),
        child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
            child: Row(
              children: [
                for (final t in ['Upcoming', 'Past', 'Transferred'])
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 4),
                      child: ChoiceChip(
                        label: Center(
                          child: Text(
                            t,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: tab == t ? Colors.black : colors.textSecondary,
                            ),
                          ),
                        ),
                        selected: tab == t,
                        selectedColor: AppColors.gold,
                        backgroundColor: colors.card,
                        side: BorderSide.none,
                        onSelected: (_) => setState(() => tab = t),
                      ),
                    ),
                  ),
              ],
            ),
          ),
          Expanded(
            child: tab == 'Upcoming'
                ? ListView(
                    padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
                    children: [
                      AppCard(
                        child: Column(
                          children: [
                            Text(
                              'Full Festival Pass',
                              style: GoogleFonts.cinzel(
                                color: AppColors.gold,
                                fontSize: 18,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'John Carlos',
                              style: TextStyle(
                                color: colors.textPrimary,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Ticket ID · JKY-2026-88421',
                              style: TextStyle(color: colors.textMuted, fontSize: 12),
                            ),
                            const SizedBox(height: 18),
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: QrImageView(
                                data: 'JKY-2026-88421-JOHN-CARLOS',
                                size: 180,
                                backgroundColor: Colors.white,
                              ),
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'Show this QR code at the entrance gate',
                              style: TextStyle(color: colors.textMuted, fontSize: 12),
                            ),
                            const SizedBox(height: 16),
                            AppButton(
                              label: 'Transfer Ticket',
                              onPressed: () {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text('Transfer flow coming soon'),
                                    backgroundColor: colors.surfaceElevated,
                                  ),
                                );
                              },
                            ),
                          ],
                        ),
                      ),
                    ],
                  )
                : Center(
                    child: Text(
                      'No $tab tickets yet',
                      style: TextStyle(color: colors.textMuted),
                    ),
                  ),
          ),
        ],
      ),
      ),
    );
  }
}
