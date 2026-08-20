import 'dart:async';

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../data/app_images.dart';
import '../models/festival_settings.dart';
import '../models/site_media_item.dart';
import '../theme/app_colors.dart';
import 'app_button.dart';
import 'media_carousel.dart';

/// Shared Splash/Home landing content matched to the official mockup.
class LandingContent extends StatefulWidget {
  const LandingContent({
    super.key,
    required this.onBuyTickets,
    required this.onDonate,
    this.showLogo = true,
    this.bottomPadding,
    this.tuckDonateUnderNav = true,
  });

  final VoidCallback onBuyTickets;
  final VoidCallback onDonate;
  final bool showLogo;
  final double? bottomPadding;
  final bool tuckDonateUnderNav;

  @override
  State<LandingContent> createState() => _LandingContentState();
}

class _LandingContentState extends State<LandingContent> {
  late Duration _remaining;
  Timer? _timer;
  FestivalSettings? _settings;
  List<SiteMediaItem> _heroMedia = const [];

  @override
  void initState() {
    super.initState();
    _tick();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) => _tick());
    _loadSettings();
    _loadHeroMedia();
  }

  Future<void> _loadHeroMedia() async {
    try {
      final items = await JukanyeApi.instance.fetchSiteMedia(slot: 'hero_slider');
      if (!mounted) return;
      setState(() => _heroMedia = items);
    } catch (_) {}
  }

  Future<void> _loadSettings() async {
    try {
      final settings = await JukanyeApi.instance.fetchSettings();
      if (!mounted) return;
      setState(() => _settings = settings);
      _tick();
    } on ApiException {
      // Keep local countdown fallback when settings are unavailable.
    } catch (_) {}
  }

  DateTime get _countdownTarget =>
      _settings?.countdownTarget ?? DateTime(2027, 7, 19);

  void _tick() {
    final next = _countdownTarget.difference(DateTime.now());
    if (!mounted) return;
    setState(() => _remaining = next.isNegative ? Duration.zero : next);
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  double _resolveBottomPadding(BuildContext context) {
    if (widget.bottomPadding != null) return widget.bottomPadding!;
    if (!widget.tuckDonateUnderNav) return 24;

    final inset = MediaQuery.paddingOf(context).bottom;
    const navOuterBottom = 12.0;
    const navBarHeight = 64.0;
    const donateUnderNav = 22.0;
    final navTop = navOuterBottom + navBarHeight + inset;

    // Keep Buy Tickets clear; let Donate slide slightly under the nav pill.
    return navTop - donateUnderNav + 12;
  }

  @override
  Widget build(BuildContext context) {
    final bottomPadding = _resolveBottomPadding(context);
    const donateTuck = 20.0;
    final days = _remaining.inDays;
    final hours = _remaining.inHours % 24;
    final mins = _remaining.inMinutes % 60;
    final secs = _remaining.inSeconds % 60;
    final tagline = _settings?.tagline(context).trim().isNotEmpty == true
        ? _settings!.tagline(context)
        : "Honoring Africa's Liberation Heroes, Promoting Patriotism";
    final dateLabel = (_settings?.dateLabel?.trim().isNotEmpty ?? false)
        ? _settings!.dateLabel!
        : '19 JULY – 01 AUGUST 2027';
    final locationLabel =
        (_settings?.locationLabel?.trim().isNotEmpty ?? false)
            ? _settings!.locationLabel!
            : 'ARUSHA, TANZANIA';

    return Stack(
      fit: StackFit.expand,
      children: [
        MediaCarousel(
          items: _heroMedia,
          height: MediaQuery.sizeOf(context).height,
          borderRadius: 0,
          fallbackUrl: AppImages.kilimanjaro,
        ),
        DecoratedBox(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [
                Colors.black.withValues(alpha: 0.25),
                Colors.black.withValues(alpha: 0.55),
                Colors.black.withValues(alpha: 0.92),
                const Color(0xFF0B0B0B),
              ],
              stops: const [0, 0.35, 0.7, 1],
            ),
          ),
        ),
        Positioned(
          left: 0,
          right: 0,
          bottom: 0,
          height: 132 + MediaQuery.paddingOf(context).bottom,
          child: IgnorePointer(
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.transparent,
                    Colors.black.withValues(alpha: 0.35),
                    const Color(0xFF0B0B0B).withValues(alpha: 0.92),
                  ],
                  stops: const [0, 0.45, 1],
                ),
              ),
            ),
          ),
        ),
        SafeArea(
          bottom: false,
          child: Padding(
            padding: EdgeInsets.fromLTRB(22, 12, 22, bottomPadding),
            child: Column(
              children: [
                if (widget.showLogo)
                  const Align(
                    alignment: Alignment.centerLeft,
                    child: JukanyeLogoBadge(),
                  ),
                const Spacer(flex: 2),
                Text(
                  'JUKANYE',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.cinzel(
                    color: AppColors.gold,
                    fontSize: 40,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 3.5,
                    height: 1,
                  ),
                ),
                const SizedBox(height: 6),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 28,
                      height: 1.2,
                      color: AppColors.gold.withValues(alpha: 0.7),
                    ),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 10),
                      child: Text(
                        'FESTIVAL',
                        style: GoogleFonts.cinzel(
                          color: AppColors.gold,
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                          letterSpacing: 4,
                        ),
                      ),
                    ),
                    Container(
                      width: 28,
                      height: 1.2,
                      color: AppColors.gold.withValues(alpha: 0.7),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                Text(
                  tagline,
                  textAlign: TextAlign.center,
                  style: GoogleFonts.dmSans(
                    color: Colors.white,
                    fontSize: 13,
                    height: 1.35,
                    fontWeight: FontWeight.w400,
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  dateLabel,
                  textAlign: TextAlign.center,
                  style: GoogleFonts.dmSans(
                    color: AppColors.gold,
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.6,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  locationLabel,
                  textAlign: TextAlign.center,
                  style: GoogleFonts.dmSans(
                    color: AppColors.gold,
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.6,
                  ),
                ),
                const SizedBox(height: 22),
                Row(
                  children: [
                    _CountdownBox(value: days, label: 'Days'),
                    _CountdownBox(value: hours, label: 'Hours'),
                    _CountdownBox(value: mins, label: 'Mins'),
                    _CountdownBox(value: secs, label: 'Secs'),
                  ],
                ),
                const Spacer(flex: 2),
                AppButton(
                  label: 'Buy Tickets',
                  onPressed: widget.onBuyTickets,
                ),
                const SizedBox(height: 12),
                Transform.translate(
                  offset: widget.tuckDonateUnderNav
                      ? const Offset(0, donateTuck)
                      : Offset.zero,
                  child: AppButton(
                    label: 'Donate Now',
                    variant: AppButtonVariant.green,
                    onPressed: widget.onDonate,
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class JukanyeLogoBadge extends StatelessWidget {
  const JukanyeLogoBadge({super.key, this.size = 64});

  static const assetPath = 'assets/icon/app_icon_foreground.png';

  final double size;

  @override
  Widget build(BuildContext context) {
    final inset = size * 0.14;

    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: Colors.black,
        border: Border.all(color: AppColors.gold, width: 1.4),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.4),
            blurRadius: 10,
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Padding(
        padding: EdgeInsets.all(inset),
        child: Image.asset(
          assetPath,
          fit: BoxFit.contain,
          filterQuality: FilterQuality.high,
        ),
      ),
    );
  }
}

class _CountdownBox extends StatelessWidget {
  const _CountdownBox({required this.value, required this.label});

  final int value;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 4),
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: const Color(0xFF1A1A1A).withValues(alpha: 0.9),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          children: [
            Text(
              '$value',
              style: GoogleFonts.dmSans(
                color: AppColors.goldLight,
                fontSize: 22,
                fontWeight: FontWeight.w800,
                height: 1.1,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: GoogleFonts.dmSans(
                color: Colors.white.withValues(alpha: 0.85),
                fontSize: 11,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
