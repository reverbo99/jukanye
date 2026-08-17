import 'package:flutter/widgets.dart';
import 'package:intl/intl.dart';

import '../utils/locale_text.dart';

class ScheduleItem {
  const ScheduleItem({
    required this.id,
    required this.startsAt,
    required this.endsAt,
    required this.titleEn,
    required this.titleSw,
    required this.descriptionEn,
    required this.descriptionSw,
    required this.locationEn,
    required this.locationSw,
    required this.lat,
    required this.lng,
    required this.mapsUrl,
    required this.category,
    required this.sortOrder,
  });

  final int id;
  final DateTime? startsAt;
  final DateTime? endsAt;
  final String? titleEn;
  final String? titleSw;
  final String? descriptionEn;
  final String? descriptionSw;
  final String? locationEn;
  final String? locationSw;
  final double? lat;
  final double? lng;
  final String? mapsUrl;
  final String? category;
  final int sortOrder;

  factory ScheduleItem.fromJson(Map<String, dynamic> json) {
    return ScheduleItem(
      id: (json['id'] as num).toInt(),
      startsAt: DateTime.tryParse(json['starts_at'] as String? ?? ''),
      endsAt: DateTime.tryParse(json['ends_at'] as String? ?? ''),
      titleEn: json['title_en'] as String?,
      titleSw: json['title_sw'] as String?,
      descriptionEn: json['description_en'] as String?,
      descriptionSw: json['description_sw'] as String?,
      locationEn: json['location_en'] as String?,
      locationSw: json['location_sw'] as String?,
      lat: _parseCoord(json['lat']),
      lng: _parseCoord(json['lng']),
      mapsUrl: json['maps_url'] as String?,
      category: json['category'] as String?,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }

  static double? _parseCoord(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString());
  }

  bool get hasMapCoordinates => lat != null && lng != null;

  String? get googleMapsUrl {
    if (mapsUrl != null && mapsUrl!.trim().isNotEmpty) return mapsUrl;
    if (!hasMapCoordinates) return null;
    return 'https://www.google.com/maps/search/?api=1&query=$lat,$lng';
  }

  String? get mapPreviewUrl {
    if (!hasMapCoordinates) return null;
    return 'https://staticmap.openstreetmap.de/staticmap.php?center=$lat,$lng&zoom=15&size=600x280&markers=$lat,$lng,red-pushpin';
  }

  String title(BuildContext context) =>
      localizedText(en: titleEn, sw: titleSw, context: context);

  String description(BuildContext context) =>
      localizedText(en: descriptionEn, sw: descriptionSw, context: context);

  String location(BuildContext context) =>
      localizedText(en: locationEn, sw: locationSw, context: context);

  String get dayLabel {
    if (startsAt == null) return '—';
    return DateFormat('d').format(startsAt!.toLocal());
  }

  String get monthLabel {
    if (startsAt == null) return '';
    return DateFormat('MMM').format(startsAt!.toLocal()).toUpperCase();
  }

  String get timeLabel {
    if (startsAt == null) return '';
    final start = DateFormat('h:mm a').format(startsAt!.toLocal());
    if (endsAt == null) return start;
    final end = DateFormat('h:mm a').format(endsAt!.toLocal());
    return '$start – $end';
  }
}
