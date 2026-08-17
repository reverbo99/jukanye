import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';

import '../data/app_images.dart';
import '../models/site_media_item.dart';
import '../theme/app_colors.dart';
import 'app_network_image.dart';

/// Hero / banner carousel driven by CMS `site-media` items.
class MediaCarousel extends StatefulWidget {
  const MediaCarousel({
    super.key,
    required this.items,
    this.height = 210,
    this.borderRadius = 18,
    this.fallbackUrl = AppImages.kilimanjaroWide,
    this.overlay,
    this.autoAdvance = true,
  });

  final List<SiteMediaItem> items;
  final double height;
  final double borderRadius;
  final String fallbackUrl;
  final Widget? overlay;
  final bool autoAdvance;

  @override
  State<MediaCarousel> createState() => _MediaCarouselState();
}

class _MediaCarouselState extends State<MediaCarousel> {
  late final PageController _controller;
  int _index = 0;

  @override
  void initState() {
    super.initState();
    _controller = PageController();
    if (widget.autoAdvance && widget.items.length > 1) {
      _scheduleAutoAdvance();
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _scheduleAutoAdvance() {
    Future<void>.delayed(const Duration(seconds: 6), () {
      if (!mounted || widget.items.length < 2) return;
      final next = (_index + 1) % widget.items.length;
      _controller.animateToPage(
        next,
        duration: const Duration(milliseconds: 450),
        curve: Curves.easeOutCubic,
      );
      _scheduleAutoAdvance();
    });
  }

  Future<void> _openItem(SiteMediaItem item) async {
    final target = item.isYoutube
        ? item.youtubeUrl
        : (item.link != null && item.link!.trim().isNotEmpty
            ? item.link
            : null);
    if (target == null || target.trim().isEmpty) return;
    final uri = Uri.tryParse(target.trim());
    if (uri == null) return;
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    final slides = widget.items.where((item) => item.displayUrl(context) != null).toList();
    final hasSlides = slides.isNotEmpty;

    return ClipRRect(
      borderRadius: BorderRadius.circular(widget.borderRadius),
      child: SizedBox(
        height: widget.height,
        child: Stack(
          fit: StackFit.expand,
          children: [
            if (hasSlides)
              PageView.builder(
                controller: _controller,
                itemCount: slides.length,
                onPageChanged: (i) => setState(() => _index = i),
                itemBuilder: (context, index) {
                  final item = slides[index];
                  final url = item.displayUrl(context)!;
                  final tappable = item.isYoutube ||
                      (item.link != null && item.link!.trim().isNotEmpty);

                  Widget content = Stack(
                    fit: StackFit.expand,
                    children: [
                      AppNetworkImage(url: url),
                      if (item.isYoutube)
                        DecoratedBox(
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.28),
                          ),
                          child: Center(
                            child: Container(
                              width: 56,
                              height: 56,
                              decoration: BoxDecoration(
                                color: AppColors.gold.withValues(alpha: 0.92),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(
                                Icons.play_arrow_rounded,
                                color: Colors.black,
                                size: 34,
                              ),
                            ),
                          ),
                        ),
                      if (item.title(context).trim().isNotEmpty ||
                          item.caption(context).trim().isNotEmpty)
                        Align(
                          alignment: Alignment.bottomLeft,
                          child: Container(
                            width: double.infinity,
                            padding: const EdgeInsets.fromLTRB(16, 24, 16, 16),
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                                colors: [
                                  Colors.transparent,
                                  Colors.black.withValues(alpha: 0.78),
                                ],
                              ),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                if (item.title(context).trim().isNotEmpty)
                                  Text(
                                    item.title(context),
                                    style: GoogleFonts.dmSans(
                                      color: Colors.white,
                                      fontWeight: FontWeight.w700,
                                      fontSize: 14,
                                    ),
                                  ),
                                if (item.caption(context).trim().isNotEmpty)
                                  Text(
                                    item.caption(context),
                                    style: GoogleFonts.dmSans(
                                      color: Colors.white70,
                                      fontSize: 12,
                                    ),
                                  ),
                              ],
                            ),
                          ),
                        ),
                    ],
                  );

                  if (tappable) {
                    content = Material(
                      color: Colors.transparent,
                      child: InkWell(
                        onTap: () => _openItem(item),
                        child: content,
                      ),
                    );
                  }

                  return content;
                },
              )
            else
              AppNetworkImage(url: widget.fallbackUrl),
            if (widget.overlay != null) widget.overlay!,
            if (slides.length > 1)
              Positioned(
                bottom: 10,
                left: 0,
                right: 0,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(slides.length, (i) {
                    final active = i == _index;
                    return AnimatedContainer(
                      duration: const Duration(milliseconds: 220),
                      margin: const EdgeInsets.symmetric(horizontal: 3),
                      width: active ? 18 : 7,
                      height: 7,
                      decoration: BoxDecoration(
                        color: active
                            ? AppColors.gold
                            : Colors.white.withValues(alpha: 0.45),
                        borderRadius: BorderRadius.circular(8),
                      ),
                    );
                  }),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

/// Horizontal row of YouTube cards from CMS featured_videos slot.
class FeaturedVideosRow extends StatelessWidget {
  const FeaturedVideosRow({
    super.key,
    required this.items,
    required this.title,
  });

  final List<SiteMediaItem> items;
  final String title;

  Future<void> _open(String? url) async {
    if (url == null || url.trim().isEmpty) return;
    final uri = Uri.tryParse(url.trim());
    if (uri == null) return;
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    final videos = items.where((e) => e.isYoutube).toList();
    if (videos.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: GoogleFonts.cinzel(
            color: AppColors.gold,
            fontSize: 20,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 168,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: videos.length,
            separatorBuilder: (context, index) => const SizedBox(width: 12),
            itemBuilder: (context, index) {
              final item = videos[index];
              final thumb = item.youtubeThumbnailUrl;
              if (thumb == null || thumb.isEmpty) {
                return const SizedBox.shrink();
              }
              return SizedBox(
                width: 240,
                child: Material(
                  color: AppColors.of(context).card,
                  borderRadius: BorderRadius.circular(14),
                  clipBehavior: Clip.antiAlias,
                  child: InkWell(
                    onTap: () => _open(item.youtubeUrl),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Stack(
                          alignment: Alignment.center,
                          children: [
                            AppNetworkImage(url: thumb, height: 120),
                            Container(
                              width: 44,
                              height: 44,
                              decoration: BoxDecoration(
                                color: AppColors.gold.withValues(alpha: 0.92),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(
                                Icons.play_arrow_rounded,
                                color: Colors.black,
                              ),
                            ),
                          ],
                        ),
                        Padding(
                          padding: const EdgeInsets.fromLTRB(12, 8, 12, 10),
                          child: Text(
                            item.title(context).trim().isNotEmpty
                                ? item.title(context)
                                : 'Video',
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.dmSans(
                              fontWeight: FontWeight.w600,
                              fontSize: 13,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
