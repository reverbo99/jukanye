import 'package:flutter/widgets.dart';

import '../utils/locale_text.dart';

class MapPlace {
  const MapPlace({
    required this.id,
    required this.nameEn,
    required this.nameSw,
    required this.lat,
    required this.lng,
    required this.descriptionEn,
    required this.descriptionSw,
    required this.sortOrder,
  });

  final int id;
  final String? nameEn;
  final String? nameSw;
  final double? lat;
  final double? lng;
  final String? descriptionEn;
  final String? descriptionSw;
  final int sortOrder;

  factory MapPlace.fromJson(Map<String, dynamic> json) {
    return MapPlace(
      id: (json['id'] as num).toInt(),
      nameEn: json['name_en'] as String?,
      nameSw: json['name_sw'] as String?,
      lat: (json['lat'] as num?)?.toDouble() ?? double.tryParse('${json['lat'] ?? ''}'),
      lng: (json['lng'] as num?)?.toDouble() ?? double.tryParse('${json['lng'] ?? ''}'),
      descriptionEn: json['description_en'] as String?,
      descriptionSw: json['description_sw'] as String?,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }

  String name(BuildContext context) =>
      localizedText(en: nameEn, sw: nameSw, context: context);

  String description(BuildContext context) =>
      localizedText(en: descriptionEn, sw: descriptionSw, context: context);

  bool get hasMapCoordinates => lat != null && lng != null;

  String? get openStreetMapUrl {
    if (!hasMapCoordinates) return null;
    return 'https://www.openstreetmap.org/?mlat=$lat&mlon=$lng#map=16/$lat/$lng';
  }
}
