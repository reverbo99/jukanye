import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../models/post.dart';
import '../theme/app_colors.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/async_body.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';
import 'news_detail_screen.dart';

class NewsScreen extends StatefulWidget {
  const NewsScreen({super.key});

  @override
  State<NewsScreen> createState() => _NewsScreenState();
}

class _NewsScreenState extends State<NewsScreen> {
  List<Post> _posts = const [];
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
      final posts = await JukanyeApi.instance.fetchPosts(perPage: 30);
      if (!mounted) return;
      setState(() {
        _posts = posts;
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
      appBar: const AppPageBar(title: 'News'),
      body: RefreshIndicator(
        color: AppColors.gold,
        onRefresh: _load,
        child: AsyncBody<Post>(
          loading: _loading,
          error: _error,
          items: _posts,
          skeleton: ScreenSkeletons.listCards(),
          onRetry: _load,
          emptyMessage: 'No news yet',
          emptyIcon: Icons.newspaper_outlined,
          builder: (context, posts) {
            return ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
              itemCount: posts.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final post = posts[index];
                final dateLabel = post.publishedAt == null
                    ? ''
                    : DateFormat.yMMMd().format(post.publishedAt!.toLocal());
                return AppCard(
                  onTap: () {
                    Navigator.of(context).push(
                      MaterialPageRoute<void>(
                        builder: (_) => NewsDetailScreen(slug: post.slug),
                      ),
                    );
                  },
                  child: Row(
                    children: [
                      if (post.coverImage != null)
                        AppNetworkImage(
                          url: post.coverImage!,
                          width: 84,
                          height: 84,
                          borderRadius: BorderRadius.circular(12),
                        )
                      else
                        Container(
                          width: 84,
                          height: 84,
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
                              post.title(context),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.dmSans(
                                color: colors.textPrimary,
                                fontWeight: FontWeight.w700,
                                fontSize: 15,
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
                );
              },
            );
          },
        ),
      ),
    );
  }
}
