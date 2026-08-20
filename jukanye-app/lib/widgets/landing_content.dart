import 'dart:async';

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../data/app_images.dart';
import '../models/festival_settings.dart';
import '../models/site_media_item.dart';
import '../theme/app_colors.dart';
import 'media_carousel.dart';

/// Shared Splash/Home landing content matched to the official mockup.
class LandingContent extends StatefulWidget {
  const LandingContent({
    super.key,
    required this.onActivities,
    required this.onBuyTickets,
    required this.onSupport,
    required this.onVote,
    this.showLogo = true,
    this.bottomPadding,
  });

  final VoidCallback onActivities;
  final VoidCallback onBuyTickets;
  final VoidCallback onSupport;
  final VoidCallback onVote;
  final bool showLogo;
  final double? bottomPadding;

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

    final inset = MediaQuery.paddingOf(context).bottom;
    const navOuterBottom = 12.0;
    const navBarHeight = 72.0;
    return navOuterBottom + navBarHeight + inset + 16;
  }

  @override
  Widget build(BuildContext context) {
    final bottomPadding = _resolveBottomPadding(context);
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
                const _PostaLogo(),
                const SizedBox(height: 16),
                _LandingCtaGrid(
                  onActivities: widget.onActivities,
                  onBuyTickets: widget.onBuyTickets,
                  onSupport: widget.onSupport,
                  onVote: widget.onVote,
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _PostaLogo extends StatelessWidget {
  const _PostaLogo();

  static const assetPath = 'assets/images/posta_logo.png';

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      assetPath,
      height: 44,
      fit: BoxFit.contain,
      filterQuality: FilterQuality.high,
    );
  }
}

class _LandingCtaGrid extends StatelessWidget {
  const _LandingCtaGrid({
    required this.onActivities,
    required this.onBuyTickets,
    required this.onSupport,
    required this.onVote,
  });

  final VoidCallback onActivities;
  final VoidCallback onBuyTickets;
  final VoidCallback onSupport;
  final VoidCallback onVote;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        _CtaRow(
          leftLabel: 'Activities',
          rightLabel: 'Buy Ticket',
          variant: _CtaVariant.gold,
          onLeft: onActivities,
          onRight: onBuyTickets,
        ),
        const SizedBox(height: 10),
        _CtaRow(
          leftLabel: 'Support',
          rightLabel: 'Vote',
          variant: _CtaVariant.green,
          onLeft: onSupport,
          onRight: onVote,
        ),
      ],
    );
  }
}

enum _CtaVariant { gold, green }

class _CtaRow extends StatelessWidget {
  const _CtaRow({
    required this.leftLabel,
    required this.rightLabel,
    required this.variant,
    required this.onLeft,
    required this.onRight,
  });

  final String leftLabel;
  final String rightLabel;
  final _CtaVariant variant;
  final VoidCallback onLeft;
  final VoidCallback onRight;

  @override
  Widget build(BuildContext context) {
    final dividerColor = variant == _CtaVariant.gold
        ? AppColors.goldLight.withValues(alpha: 0.85)
        : AppColors.greenLight.withValues(alpha: 0.9);

    return Row(
      children: [
        Expanded(
          child: _CtaTile(
            label: leftLabel,
            variant: variant,
            onPressed: onLeft,
          ),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 6),
          child: Container(
            width: 1.5,
            height: 36,
            color: dividerColor,
          ),
        ),
        Expanded(
          child: _CtaTile(
            label: rightLabel,
            variant: variant,
            onPressed: onRight,
          ),
        ),
      ],
    );
  }
}

class _CtaTile extends StatelessWidget {
  const _CtaTile({
    required this.label,
    required this.variant,
    required this.onPressed,
  });

  final String label;
  final _CtaVariant variant;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    final bg = variant == _CtaVariant.gold ? AppColors.gold : AppColors.green;
    final fg = variant == _CtaVariant.gold ? Colors.black : Colors.white;

    return Material(
      color: bg,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onPressed,
        borderRadius: BorderRadius.circular(14),
        child: SizedBox(
          height: 52,
          child: Center(
            child: Text(
              label.toUpperCase(),
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(
                color: fg,
                fontWeight: FontWeight.w800,
                fontSize: 13,
                letterSpacing: 0.8,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class JukanyeLogoBadge extends StatelessWidget {
  const JukanyeLogoBadge({super.key, this.size = 58});

  static const assetPath = 'assets/icon/app_icon.png';

  final double size;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: Colors.black,
        border: Border.all(color: AppColors.gold, width: 1.6),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.35),
            blurRadius: 10,
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Image.asset(
        assetPath,
        fit: BoxFit.cover,
        filterQuality: FilterQuality.high,
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
