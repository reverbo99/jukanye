import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

import '../theme/app_colors.dart';

class StreetMapPoint {
  const StreetMapPoint({
    required this.lat,
    required this.lng,
    this.title,
    this.subtitle,
  });

  final double lat;
  final double lng;
  final String? title;
  final String? subtitle;

  LatLng get latLng => LatLng(lat, lng);
}

class StreetMap extends StatelessWidget {
  const StreetMap({
    super.key,
    required this.points,
    this.height = 240,
    this.interactive = true,
    this.onTap,
  });

  static const LatLng arusha = LatLng(-3.3869, 36.6830);

  final List<StreetMapPoint> points;
  final double height;
  final bool interactive;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final markers = points
        .map(
          (point) => Marker(
            point: point.latLng,
            width: 40,
            height: 40,
            alignment: Alignment.topCenter,
            child: const Icon(Icons.location_on, color: AppColors.gold, size: 36),
          ),
        )
        .toList();
    final center = points.isNotEmpty ? points.first.latLng : arusha;
    final initialZoom = points.length == 1 ? 16.0 : 14.0;

    Widget map = FlutterMap(
      options: MapOptions(
        initialCenter: center,
        initialZoom: initialZoom,
        initialCameraFit: points.length > 1
            ? CameraFit.coordinates(
                coordinates: points.map((p) => p.latLng).toList(),
                padding: const EdgeInsets.all(36),
                maxZoom: 16,
              )
            : null,
        interactionOptions: InteractionOptions(
          flags: interactive ? InteractiveFlag.all : InteractiveFlag.none,
        ),
      ),
      children: [
        TileLayer(
          urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
          userAgentPackageName: 'com.example.jukanye',
        ),
        if (markers.isNotEmpty) MarkerLayer(markers: markers),
        const RichAttributionWidget(
          attributions: [
            TextSourceAttribution('OpenStreetMap contributors'),
          ],
        ),
      ],
    );

    if (onTap != null) {
      map = GestureDetector(
        onTap: onTap,
        child: AbsorbPointer(absorbing: !interactive, child: map),
      );
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: ColoredBox(
        color: colors.surfaceElevated,
        child: SizedBox(height: height, width: double.infinity, child: map),
      ),
    );
  }
}
