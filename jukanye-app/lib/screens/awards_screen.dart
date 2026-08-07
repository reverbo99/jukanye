import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../models/award_category.dart';
import '../models/nominee.dart';
import '../theme/app_colors.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/async_body.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';

class AwardsScreen extends StatefulWidget {
  const AwardsScreen({super.key});

  @override
  State<AwardsScreen> createState() => _AwardsScreenState();
}

class _AwardsScreenState extends State<AwardsScreen> {
  List<AwardCategory> _categories = const [];
  List<Nominee> _nominees = const [];
  String? _selectedSlug;
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
      final categories = await JukanyeApi.instance.fetchAwardCategories();
      final nominees = await JukanyeApi.instance.fetchNominees(
        categorySlug: _selectedSlug,
      );
      if (!mounted) return;
      setState(() {
        _categories = categories;
        _nominees = nominees;
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

  Future<void> _selectCategory(String? slug) async {
    setState(() {
      _selectedSlug = slug;
      _loading = true;
      _error = null;
    });
    try {
      final nominees =
          await JukanyeApi.instance.fetchNominees(categorySlug: slug);
      if (!mounted) return;
      setState(() {
        _nominees = nominees;
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
      appBar: const AppPageBar(title: 'Awards'),
      body: Column(
        children: [
          if (_categories.isNotEmpty)
            SizedBox(
              height: 52,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.fromLTRB(16, 10, 16, 6),
                itemCount: _categories.length + 1,
                separatorBuilder: (_, _) => const SizedBox(width: 8),
                itemBuilder: (context, index) {
                  final isAll = index == 0;
                  final selected = isAll
                      ? _selectedSlug == null
                      : _selectedSlug == _categories[index - 1].slug;
                  final label = isAll
                      ? 'All'
                      : _categories[index - 1].name(context);
                  return Material(
                    color: colors.card,
                    borderRadius: BorderRadius.circular(10),
                    child: InkWell(
                      borderRadius: BorderRadius.circular(10),
                      onTap: () => _selectCategory(
                        isAll ? null : _categories[index - 1].slug,
                      ),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: selected
                                ? AppColors.gold
                                : Colors.transparent,
                            width: 1.2,
                          ),
                        ),
                        child: Text(
                          label,
                          style: GoogleFonts.dmSans(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: selected
                                ? AppColors.gold
                                : colors.textPrimary,
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          Expanded(
            child: RefreshIndicator(
              color: AppColors.gold,
              onRefresh: _load,
              child: AsyncBody<Nominee>(
                loading: _loading,
                error: _error,
                items: _nominees,
                skeleton: ScreenSkeletons.listCards(),
                onRetry: _load,
                emptyMessage: 'No nominees yet',
                emptyIcon: Icons.emoji_events_outlined,
                builder: (context, nominees) {
                  return ListView.separated(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                    itemCount: nominees.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final nominee = nominees[index];
                      final bio = nominee.bio(context);
                      return AppCard(
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (nominee.photo != null)
                              AppNetworkImage(
                                url: nominee.photo!,
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
                                  Icons.person_outline,
                                  color: colors.textMuted,
                                ),
                              ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    nominee.name,
                                    style: GoogleFonts.dmSans(
                                      color: colors.textPrimary,
                                      fontWeight: FontWeight.w700,
                                      fontSize: 15,
                                    ),
                                  ),
                                  if (nominee.country != null &&
                                      nominee.country!.isNotEmpty) ...[
                                    const SizedBox(height: 4),
                                    Text(
                                      nominee.country!,
                                      style: GoogleFonts.dmSans(
                                        color: AppColors.gold,
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ],
                                  if (nominee.category != null) ...[
                                    const SizedBox(height: 4),
                                    Text(
                                      nominee.category!.name(context),
                                      style: GoogleFonts.dmSans(
                                        color: colors.textMuted,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ],
                                  if (bio.isNotEmpty) ...[
                                    const SizedBox(height: 8),
                                    Text(
                                      bio,
                                      maxLines: 3,
                                      overflow: TextOverflow.ellipsis,
                                      style: GoogleFonts.dmSans(
                                        color: colors.textSecondary,
                                        fontSize: 13,
                                        height: 1.35,
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
          ),
        ],
      ),
    );
  }
}
