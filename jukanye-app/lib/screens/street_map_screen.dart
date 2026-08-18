import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';

import '../theme/app_colors.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/street_map.dart';

class StreetMapScreen extends StatelessWidget {
  const StreetMapScreen({
    super.key,
    required this.title,
    required this.points,
    this.initialPoint,
  });

  final String title;
  final List<StreetMapPoint> points;
  final StreetMapPoint? initialPoint;

  Future<void> _openOsm(StreetMapPoint point) async {
    final uri = Uri.parse(
      'https://www.openstreetmap.org/?mlat=${point.lat}&mlon=${point.lng}#map=16/${point.lat}/${point.lng}',
    );
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final focus = initialPoint ?? (points.isNotEmpty ? points.first : null);
    final center = focus?.latLng ?? StreetMap.arusha;

    return Scaffold(
      appBar: AppPageBar(title: title),
      body: FlutterMap(
        options: MapOptions(
          initialCenter: center,
          initialZoom: points.length <= 1 ? 16 : 14,
          initialCameraFit: points.length > 1
              ? CameraFit.coordinates(
                  coordinates: points.map((p) => p.latLng).toList(),
                  padding: const EdgeInsets.all(48),
                  maxZoom: 16,
                )
              : null,
        ),
        children: [
          TileLayer(
            urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            userAgentPackageName: 'com.example.jukanye',
          ),
          MarkerLayer(
            markers: points
                .map(
                  (point) => Marker(
                    point: point.latLng,
                    width: 44,
                    height: 44,
                    alignment: Alignment.topCenter,
                    child: GestureDetector(
                      onTap: () => _openOsm(point),
                      child: const Icon(
                        Icons.location_on,
                        color: AppColors.gold,
                        size: 40,
                      ),
                    ),
                  ),
                )
                .toList(),
          ),
          RichAttributionWidget(
            attributions: [
              TextSourceAttribution(
                'OpenStreetMap contributors',
                onTap: () => launchUrl(
                  Uri.parse('https://www.openstreetmap.org/copyright'),
                ),
              ),
            ],
          ),
        ],
      ),
      bottomNavigationBar: focus == null
          ? null
          : SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if ((focus.title ?? '').isNotEmpty)
                      Text(
                        focus.title!,
                        style: GoogleFonts.dmSans(
                          color: colors.textPrimary,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    if ((focus.subtitle ?? '').isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        focus.subtitle!,
                        style: TextStyle(color: colors.textMuted, fontSize: 13),
                      ),
                    ],
                    const SizedBox(height: 10),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: () => _openOsm(focus),
                        icon: const Icon(Icons.open_in_new),
                        label: const Text('Open in OpenStreetMap'),
                        style: FilledButton.styleFrom(
                          backgroundColor: AppColors.gold,
                          foregroundColor: const Color(0xFF1A1608),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
