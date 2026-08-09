import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../main.dart';
import '../navigation/app_page_route.dart';
import '../screens/profile_screen.dart';
import '../theme/app_colors.dart';
import '../utils/locale_text.dart';
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
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _tickets = const [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    if (!authSession.isLoggedIn) {
      setState(() {
        _loading = false;
        _error = null;
        _tickets = const [];
      });
      return;
    }

    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final tickets = await JukanyeApi.instance.fetchMyTickets();
      if (!mounted) return;
      setState(() {
        _tickets = tickets;
        _loading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.message;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = 'Unable to load tickets';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);

    return Scaffold(
      appBar: const AppPageBar(title: 'My Tickets'),
      body: !authSession.isLoggedIn
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(28),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.lock_outline, color: colors.textMuted, size: 42),
                    const SizedBox(height: 14),
                    Text(
                      'Sign in to view your tickets',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.dmSans(
                        color: colors.textPrimary,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 16),
                    AppButton(
                      label: 'Go to Profile',
                      onPressed: () {
                        AppNav.push(context, const ProfileScreen());
                      },
                    ),
                  ],
                ),
              ),
            )
          : RefreshIndicator(
              color: AppColors.gold,
              onRefresh: _load,
              child: _loading
                  ? ListView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      children: [
                        SizedBox(
                          height: MediaQuery.sizeOf(context).height * 0.6,
                          child: ScreenSkeletons.detail(),
                        ),
                      ],
                    )
                  : _error != null
                      ? ListView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: const EdgeInsets.all(28),
                          children: [
                            Text(
                              _error!,
                              textAlign: TextAlign.center,
                              style: TextStyle(color: colors.textMuted),
                            ),
                            const SizedBox(height: 12),
                            AppButton(
                              label: 'Retry',
                              onPressed: _load,
                            ),
                          ],
                        )
                      : _tickets.isEmpty
                          ? ListView(
                              physics: const AlwaysScrollableScrollPhysics(),
                              padding: const EdgeInsets.all(28),
                              children: [
                                const SizedBox(height: 80),
                                Icon(
                                  Icons.confirmation_number_outlined,
                                  color: colors.textMuted,
                                  size: 42,
                                ),
                                const SizedBox(height: 14),
                                Text(
                                  'No tickets yet',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(color: colors.textMuted),
                                ),
                              ],
                            )
                          : ListView.separated(
                              physics: const AlwaysScrollableScrollPhysics(),
                              padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
                              itemCount: _tickets.length,
                              separatorBuilder: (_, _) =>
                                  const SizedBox(height: 12),
                              itemBuilder: (context, index) {
                                final ticket = _tickets[index];
                                final tierName = localizedText(
                                  en: ticket['tier_name_en'] as String?,
                                  sw: ticket['tier_name_sw'] as String?,
                                  context: context,
                                );
                                final holder =
                                    (ticket['customer_name'] as String?)
                                            ?.trim()
                                            .isNotEmpty ==
                                        true
                                    ? (ticket['customer_name'] as String).trim()
                                    : authSession.displayName;
                                final reference =
                                    ticket['reference'] as String? ?? '';
                                final qr = (ticket['qr_payload'] as String?)
                                            ?.trim()
                                            .isNotEmpty ==
                                        true
                                    ? (ticket['qr_payload'] as String).trim()
                                    : reference;

                                return AppCard(
                                  child: Column(
                                    children: [
                                      Text(
                                        tierName.isNotEmpty
                                            ? tierName
                                            : 'Festival Ticket',
                                        style: GoogleFonts.cinzel(
                                          color: AppColors.gold,
                                          fontSize: 18,
                                          fontWeight: FontWeight.w700,
                                        ),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        holder,
                                        style: TextStyle(
                                          color: colors.textPrimary,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                      if (reference.isNotEmpty) ...[
                                        const SizedBox(height: 4),
                                        Text(
                                          'Ticket ID · $reference',
                                          style: TextStyle(
                                            color: colors.textMuted,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ],
                                      const SizedBox(height: 18),
                                      Container(
                                        padding: const EdgeInsets.all(12),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius:
                                              BorderRadius.circular(12),
                                        ),
                                        child: QrImageView(
                                          data: qr.isNotEmpty
                                              ? qr
                                              : 'JUKANYE-TICKET',
                                          size: 180,
                                          backgroundColor: Colors.white,
                                        ),
                                      ),
                                      const SizedBox(height: 16),
                                      Text(
                                        'Show this QR code at the entrance gate',
                                        style: TextStyle(
                                          color: colors.textMuted,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              },
                            ),
            ),
    );
  }
}
