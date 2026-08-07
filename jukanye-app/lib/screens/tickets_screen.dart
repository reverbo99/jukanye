import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../data/app_images.dart';
import '../models/ticket_tier.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/async_body.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';

class TicketsScreen extends StatefulWidget {
  const TicketsScreen({
    super.key,
    required this.onBuy,
    required this.onMyTickets,
    this.goToTab,
  });

  final ValueChanged<TicketTier> onBuy;
  final VoidCallback onMyTickets;
  final void Function(int tab)? goToTab;

  @override
  State<TicketsScreen> createState() => _TicketsScreenState();
}

class _TicketsScreenState extends State<TicketsScreen> {
  List<TicketTier> _tiers = const [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final tiers = await JukanyeApi.instance.fetchTicketTiers();
      if (!mounted) return;
      setState(() {
        _tiers = tiers;
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
        _error = 'Something went wrong';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return Scaffold(
      appBar: AppPageBar(title: 'Tickets', goToTab: widget.goToTab),
      body: RefreshIndicator(
        color: AppColors.gold,
        onRefresh: _load,
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            const SliverToBoxAdapter(
              child: HeroHeaderImage(
                imageUrl: AppImages.festivalCrowd,
                height: 170,
                child: Align(
                  alignment: Alignment.bottomLeft,
                  child: Padding(
                    padding: EdgeInsets.all(20),
                    child: Text(
                      'Tickets',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 28,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        'Choose your pass',
                        style: GoogleFonts.cinzel(
                          color: colors.textPrimary,
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    TextButton(
                      onPressed: widget.onMyTickets,
                      child: const Text(
                        'My Tickets',
                        style: TextStyle(
                          color: AppColors.gold,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            SliverFillRemaining(
              hasScrollBody: true,
              child: AsyncBody<TicketTier>(
                loading: _loading,
                error: _error,
                items: _tiers,
                skeleton: ScreenSkeletons.listCards(count: 4),
                onRetry: _load,
                emptyMessage: 'No tickets yet',
                emptyIcon: Icons.confirmation_number_outlined,
                builder: (context, tiers) {
                  return ListView.builder(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(20, 4, 20, 28),
                    itemCount: tiers.length,
                    itemBuilder: (context, index) {
                      final ticket = tiers[index];
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: AppCard(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      ticket.name(context),
                                      style: GoogleFonts.dmSans(
                                        color: colors.textPrimary,
                                        fontWeight: FontWeight.w700,
                                        fontSize: 16,
                                      ),
                                    ),
                                  ),
                                  Text(
                                    formatTzs(ticket.price),
                                    style: GoogleFonts.cinzel(
                                      color: AppColors.gold,
                                      fontWeight: FontWeight.w700,
                                      fontSize: 14,
                                    ),
                                  ),
                                ],
                              ),
                              if (ticket
                                  .description(context)
                                  .trim()
                                  .isNotEmpty) ...[
                                const SizedBox(height: 8),
                                Text(
                                  ticket.description(context),
                                  style: TextStyle(
                                    color: colors.textMuted,
                                    fontSize: 13,
                                    height: 1.4,
                                  ),
                                ),
                              ],
                              const SizedBox(height: 14),
                              AppButton(
                                label: 'Buy Now',
                                height: 44,
                                onPressed: () => widget.onBuy(ticket),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}
