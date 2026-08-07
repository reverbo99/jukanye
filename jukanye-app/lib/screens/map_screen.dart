import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../data/app_images.dart';
import '../models/map_place.dart';
import '../theme/app_colors.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/async_body.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';

class MapScreen extends StatefulWidget {
  const MapScreen({super.key});

  @override
  State<MapScreen> createState() => _MapScreenState();
}

class _MapScreenState extends State<MapScreen> {
  List<MapPlace> _places = const [];
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
      final places = await JukanyeApi.instance.fetchMapPlaces();
      if (!mounted) return;
      setState(() {
        _places = places;
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
      appBar: const AppPageBar(title: 'Festival Map'),
      body: RefreshIndicator(
        color: AppColors.gold,
        onRefresh: _load,
        child: AsyncBody<MapPlace>(
          loading: _loading,
          error: _error,
          items: _places,
          skeleton: ScreenSkeletons.detail(),
          onRetry: _load,
          emptyMessage: 'No map places yet',
          emptyIcon: Icons.map_outlined,
          builder: (context, places) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
              children: [
                Text(
                  'Arusha Festival Grounds',
                  style: GoogleFonts.cinzel(
                    color: AppColors.gold,
                    fontWeight: FontWeight.w700,
                    fontSize: 18,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Explore stages, services, and amenities around the venue.',
                  style: TextStyle(color: colors.textMuted),
                ),
                const SizedBox(height: 16),
                ClipRRect(
                  borderRadius: BorderRadius.circular(18),
                  child: AspectRatio(
                    aspectRatio: 1.2,
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        const AppNetworkImage(url: AppImages.mapTerrain),
                        Container(
                          color: Colors.black.withValues(alpha: 0.28),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 18),
                ...places.map(
                  (place) => Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: AppCard(
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 42,
                            height: 42,
                            decoration: BoxDecoration(
                              color: colors.surfaceElevated,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(
                              Icons.place_outlined,
                              color: AppColors.gold,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  place.name(context),
                                  style: GoogleFonts.dmSans(
                                    color: colors.textPrimary,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                if (place
                                    .description(context)
                                    .trim()
                                    .isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    place.description(context),
                                    style: TextStyle(
                                      color: colors.textMuted,
                                      fontSize: 13,
                                      height: 1.35,
                                    ),
                                  ),
                                ],
                                if (place.lat != null && place.lng != null) ...[
                                  const SizedBox(height: 6),
                                  Text(
                                    '${place.lat!.toStringAsFixed(5)}, ${place.lng!.toStringAsFixed(5)}',
                                    style: GoogleFonts.dmSans(
                                      color: AppColors.gold,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ],
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
