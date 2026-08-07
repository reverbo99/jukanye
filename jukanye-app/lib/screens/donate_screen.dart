import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../data/app_images.dart';
import '../models/festival_settings.dart';
import '../navigation/app_page_route.dart';
import '../screens/donation_amount_screen.dart';
import '../theme/app_colors.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/async_body.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';

class DonateScreen extends StatefulWidget {
  const DonateScreen({super.key, this.onOpenRoute, this.goToTab});

  final ValueChanged<String>? onOpenRoute;
  final void Function(int tab)? goToTab;

  @override
  State<DonateScreen> createState() => _DonateScreenState();
}

class _DonateScreenState extends State<DonateScreen> {
  FestivalSettings? _settings;
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
      final settings = await JukanyeApi.instance.fetchSettings();
      if (!mounted) return;
      setState(() {
        _settings = settings;
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
    final body = _settings?.donateBody(context).trim() ?? '';
    final embedUrl = _settings?.donateEmbedUrl?.trim() ?? '';
    final options = [
      (
        'One-Time Donation',
        'Give once and support the festival immediately',
        Icons.favorite_outline,
      ),
      (
        'Monthly Support',
        'Become a sustaining partner every month',
        Icons.calendar_month_outlined,
      ),
      (
        'Sponsor a Youth',
        'Fund a young artist or innovator\'s participation',
        Icons.school_outlined,
      ),
      (
        'Sponsor a Group',
        'Support community troupes and cultural groups',
        Icons.groups_outlined,
      ),
      (
        'Support Exhibitions',
        'Help power galleries and heritage exhibits',
        Icons.museum_outlined,
      ),
    ];

    return Scaffold(
      appBar: AppPageBar(title: 'Donate', goToTab: widget.goToTab),
      body: RefreshIndicator(
        color: AppColors.gold,
        onRefresh: _load,
        child: AsyncBody<Object>(
          loading: _loading,
          error: _error,
          items: _settings == null ? const [] : const [Object()],
          skeleton: ScreenSkeletons.listCards(count: 4),
          onRetry: _load,
          emptyMessage: 'Donation info unavailable',
          emptyIcon: Icons.volunteer_activism_outlined,
          builder: (context, _) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              children: [
                const HeroHeaderImage(
                  imageUrl: AppImages.donateHands,
                  height: 180,
                  child: Align(
                    alignment: Alignment.bottomLeft,
                    child: Padding(
                      padding: EdgeInsets.all(20),
                      child: Text(
                        'Support Jukanye',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 26,
                          fontWeight: FontWeight.w700,
                          fontFamily: 'serif',
                        ),
                      ),
                    ),
                  ),
                ),
                if (body.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 18, 20, 8),
                    child: Text(
                      body,
                      style: GoogleFonts.dmSans(
                        color: colors.textSecondary,
                        height: 1.5,
                        fontSize: 14,
                      ),
                    ),
                  )
                else
                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 18, 20, 8),
                    child: Text(
                      'Choose how you want to contribute',
                      style: GoogleFonts.dmSans(color: colors.textMuted),
                    ),
                  ),
                if (embedUrl.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
                    child: AppCard(
                      onTap: () async {
                        await Clipboard.setData(ClipboardData(text: embedUrl));
                        if (!context.mounted) return;
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Donation link copied'),
                          ),
                        );
                      },
                      child: Row(
                        children: [
                          const Icon(Icons.link, color: AppColors.gold),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'External donation page',
                                  style: GoogleFonts.dmSans(
                                    color: colors.textPrimary,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  embedUrl,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    color: colors.textMuted,
                                    fontSize: 12,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Icon(Icons.copy, color: colors.textMuted, size: 18),
                        ],
                      ),
                    ),
                  ),
                ...options.map(
                  (opt) => Padding(
                    padding: const EdgeInsets.fromLTRB(20, 0, 20, 10),
                    child: AppCard(
                      onTap: () {
                        AppNav.push(
                          context,
                          DonationAmountScreen(title: opt.$1),
                        );
                      },
                      child: Row(
                        children: [
                          Container(
                            width: 46,
                            height: 46,
                            decoration: BoxDecoration(
                              color: colors.surfaceElevated,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Icon(opt.$3, color: AppColors.gold),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  opt.$1,
                                  style: GoogleFonts.dmSans(
                                    color: colors.textPrimary,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  opt.$2,
                                  style: TextStyle(
                                    color: colors.textMuted,
                                    fontSize: 12,
                                    height: 1.35,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Icon(Icons.chevron_right, color: colors.textMuted),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
              ],
            );
          },
        ),
      ),
    );
  }
}
