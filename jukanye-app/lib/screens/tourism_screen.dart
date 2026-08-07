import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../models/tour.dart';
import '../theme/app_colors.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/async_body.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';

class TourismScreen extends StatefulWidget {
  const TourismScreen({super.key});

  @override
  State<TourismScreen> createState() => _TourismScreenState();
}

class _TourismScreenState extends State<TourismScreen> {
  List<Tour> _tours = const [];
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
      final tours = await JukanyeApi.instance.fetchTours();
      if (!mounted) return;
      setState(() {
        _tours = tours;
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
      appBar: const AppPageBar(title: 'Tourism'),
      body: RefreshIndicator(
        color: AppColors.gold,
        onRefresh: _load,
        child: AsyncBody<Tour>(
          loading: _loading,
          error: _error,
          items: _tours,
          skeleton: ScreenSkeletons.listCards(count: 4),
          onRetry: _load,
          emptyMessage: 'No tours yet',
          emptyIcon: Icons.travel_explore,
          builder: (context, tours) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
              children: [
                Text(
                  'Discover Tanzania',
                  style: GoogleFonts.cinzel(
                    color: AppColors.gold,
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Travel packages curated for festival guests.',
                  style: TextStyle(color: colors.textMuted),
                ),
                const SizedBox(height: 16),
                ...tours.map(
                  (tour) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: AppCard(
                      padding: EdgeInsets.zero,
                      onTap: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              '${tour.name(context)} booking coming soon',
                            ),
                            backgroundColor: colors.surfaceElevated,
                          ),
                        );
                      },
                      child: Row(
                        children: [
                          if (tour.image != null)
                            AppNetworkImage(
                              url: tour.image!,
                              width: 110,
                              height: 110,
                              borderRadius: const BorderRadius.horizontal(
                                left: Radius.circular(16),
                              ),
                            )
                          else
                            Container(
                              width: 110,
                              height: 110,
                              decoration: BoxDecoration(
                                color: colors.surfaceElevated,
                                borderRadius: const BorderRadius.horizontal(
                                  left: Radius.circular(16),
                                ),
                              ),
                              child: Icon(
                                Icons.travel_explore,
                                color: colors.textMuted,
                              ),
                            ),
                          Expanded(
                            child: Padding(
                              padding:
                                  const EdgeInsets.fromLTRB(14, 12, 14, 12),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    tour.name(context),
                                    style: GoogleFonts.dmSans(
                                      color: colors.textPrimary,
                                      fontWeight: FontWeight.w700,
                                      fontSize: 15,
                                    ),
                                  ),
                                  if (tour
                                      .duration(context)
                                      .trim()
                                      .isNotEmpty) ...[
                                    const SizedBox(height: 4),
                                    Text(
                                      tour.duration(context),
                                      style: TextStyle(
                                        color: colors.textMuted,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ],
                                  const SizedBox(height: 10),
                                  Text(
                                    'From ${formatTzs(tour.fromPrice)}',
                                    style: const TextStyle(
                                      color: AppColors.gold,
                                      fontWeight: FontWeight.w700,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }
}
