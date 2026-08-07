import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../models/post.dart';
import '../theme/app_colors.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/skeleton.dart';

class NewsDetailScreen extends StatefulWidget {
  const NewsDetailScreen({super.key, required this.slug});

  final String slug;

  @override
  State<NewsDetailScreen> createState() => _NewsDetailScreenState();
}

class _NewsDetailScreenState extends State<NewsDetailScreen> {
  Post? _post;
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
      final post = await JukanyeApi.instance.fetchPost(widget.slug);
      if (!mounted) return;
      setState(() {
        _post = post;
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
    final post = _post;

    return Scaffold(
      appBar: const AppPageBar(title: 'Article'),
      body: _loading
          ? ScreenSkeletons.detail()
          : _error != null || post == null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(28),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          _error ?? 'Article not found',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.dmSans(color: colors.textMuted),
                        ),
                        TextButton(
                          onPressed: _load,
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
                  ),
                )
              : ListView(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 28),
                  children: [
                    if (post.coverImage != null) ...[
                      ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: AspectRatio(
                          aspectRatio: 16 / 10,
                          child: AppNetworkImage(url: post.coverImage!),
                        ),
                      ),
                      const SizedBox(height: 18),
                    ],
                    Text(
                      post.title(context),
                      style: GoogleFonts.dmSans(
                        color: colors.textPrimary,
                        fontSize: 22,
                        fontWeight: FontWeight.w800,
                        height: 1.25,
                      ),
                    ),
                    if (post.publishedAt != null) ...[
                      const SizedBox(height: 8),
                      Text(
                        DateFormat.yMMMMd().format(post.publishedAt!.toLocal()),
                        style: GoogleFonts.dmSans(
                          color: colors.textMuted,
                          fontSize: 13,
                        ),
                      ),
                    ],
                    const SizedBox(height: 16),
                    Text(
                      post.body(context).isNotEmpty
                          ? post.body(context)
                          : post.excerpt(context),
                      style: GoogleFonts.dmSans(
                        color: colors.textSecondary,
                        fontSize: 15,
                        height: 1.55,
                      ),
                    ),
                  ],
                ),
    );
  }
}
