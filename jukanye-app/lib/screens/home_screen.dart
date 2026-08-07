import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../data/app_images.dart';
import '../models/post.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';
import 'news_detail_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({
    super.key,
    required this.onOpenDonate,
    required this.onOpenTickets,
    required this.onOpenRoute,
    this.goToTab,
  });

  final VoidCallback onOpenDonate;
  final VoidCallback onOpenTickets;
  final ValueChanged<String> onOpenRoute;
  final void Function(int tab)? goToTab;

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<Post> _news = const [];
  bool _newsLoading = true;
  String? _newsError;

  @override
  void initState() {
    super.initState();
    _loadNews();
  }

  Future<void> _loadNews() async {
    setState(() {
      _newsLoading = true;
      _newsError = null;
    });
    try {
      final posts = await JukanyeApi.instance.fetchPosts(perPage: 5);
      if (!mounted) return;
      setState(() {
        _news = posts;
        _newsLoading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _newsError = e.message;
        _newsLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _newsError = 'Unable to load news';
        _newsLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);

    return Scaffold(
      appBar: AppPageBar(
        title: 'Home',
        goToTab: widget.goToTab,
      ),
      body: PageSkeletonGate(
        skeleton: ScreenSkeletons.feed(context),
        child: RefreshIndicator(
          color: AppColors.gold,
          onRefresh: _loadNews,
          child: ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 28),
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(18),
                child: SizedBox(
                  height: 210,
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      const AppNetworkImage(url: AppImages.kilimanjaroWide),
                      DecoratedBox(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [
                              Colors.black.withValues(alpha: 0.2),
                              Colors.black.withValues(alpha: 0.75),
                            ],
                          ),
                        ),
                      ),
                      Padding(
                        padding: const EdgeInsets.fromLTRB(18, 20, 18, 20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            Text(
                              'Welcome to',
                              style: GoogleFonts.dmSans(
                                color: Colors.white,
                                fontSize: 14,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'JUKANYE FESTIVAL',
                              style: GoogleFonts.cinzel(
                                color: AppColors.gold,
                                fontSize: 26,
                                fontWeight: FontWeight.w700,
                                height: 1.1,
                                letterSpacing: 0.5,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'A Pan-African Celebration of Freedom, Unity & Culture',
                              style: GoogleFonts.dmSans(
                                color: Colors.white.withValues(alpha: 0.92),
                                fontSize: 13,
                                height: 1.35,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: colors.card.withValues(alpha: colors.isDark ? 0.92 : 1),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: AppColors.gold.withValues(alpha: 0.35),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      "Together We Are Building Africa's Greatest Festival",
                      style: GoogleFonts.dmSans(
                        color: AppColors.goldLight,
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                        height: 1.35,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'Total Raised',
                      style: GoogleFonts.dmSans(
                        color: colors.textMuted,
                        fontSize: 12,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      formatTzs(1205750000),
                      style: GoogleFonts.dmSans(
                        color: colors.textPrimary,
                        fontSize: 26,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 16),
                    AppButton(
                      label: 'Contribute Now',
                      variant: AppButtonVariant.green,
                      height: 48,
                      onPressed: widget.onOpenDonate,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(
                    child: Text(
                      'Latest News',
                      style: GoogleFonts.dmSans(
                        color: colors.textPrimary,
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                  TextButton(
                    onPressed: () => widget.onOpenRoute('news'),
                    child: Text(
                      'View all',
                      style: GoogleFonts.dmSans(
                        color: AppColors.greenLight,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              if (_newsLoading)
                ...List.generate(
                  3,
                  (_) => const Padding(
                    padding: EdgeInsets.only(bottom: 10),
                    child: SkeletonBox(height: 88),
                  ),
                )
              else if (_newsError != null)
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  child: Column(
                    children: [
                      Text(
                        _newsError!,
                        style: GoogleFonts.dmSans(
                          color: colors.textMuted,
                          fontSize: 13,
                        ),
                      ),
                      TextButton(
                        onPressed: _loadNews,
                        child: Text(
                          'Retry',
                          style: GoogleFonts.dmSans(
                            color: AppColors.gold,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                    ],
                  ),
                )
              else if (_news.isEmpty)
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  child: Text(
                    'No news yet — check back soon.',
                    style: GoogleFonts.dmSans(
                      color: colors.textMuted,
                      fontSize: 13,
                    ),
                  ),
                )
              else
                ..._news.map(
                  (item) {
                    final dateLabel = item.publishedAt == null
                        ? ''
                        : DateFormat.MMMd()
                            .format(item.publishedAt!.toLocal());
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: AppCard(
                        onTap: () {
                          Navigator.of(context).push(
                            MaterialPageRoute<void>(
                              builder: (_) =>
                                  NewsDetailScreen(slug: item.slug),
                            ),
                          );
                        },
                        child: Row(
                          children: [
                            if (item.coverImage != null)
                              AppNetworkImage(
                                url: item.coverImage!,
                                width: 72,
                                height: 72,
                                borderRadius: BorderRadius.circular(12),
                              )
                            else
                              Container(
                                width: 72,
                                height: 72,
                                decoration: BoxDecoration(
                                  color: colors.surfaceElevated,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Icon(
                                  Icons.newspaper_outlined,
                                  color: colors.textMuted,
                                ),
                              ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item.title(context),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: GoogleFonts.dmSans(
                                      color: colors.textPrimary,
                                      fontWeight: FontWeight.w700,
                                      fontSize: 14,
                                      height: 1.3,
                                    ),
                                  ),
                                  if (dateLabel.isNotEmpty) ...[
                                    const SizedBox(height: 6),
                                    Text(
                                      dateLabel,
                                      style: GoogleFonts.dmSans(
                                        color: colors.textMuted,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
            ],
          ),
        ),
      ),
    );
  }
}
