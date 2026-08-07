import 'package:flutter/widgets.dart';

import '../config/api_config.dart';
import '../utils/locale_text.dart';

class Tour {
  const Tour({
    required this.id,
    required this.nameEn,
    required this.nameSw,
    required this.fromPrice,
    required this.currency,
    required this.durationEn,
    required this.durationSw,
    required this.image,
    required this.descriptionEn,
    required this.descriptionSw,
    required this.sortOrder,
  });

  final int id;
  final String? nameEn;
  final String? nameSw;
  final int fromPrice;
  final String? currency;
  final String? durationEn;
  final String? durationSw;
  final String? image;
  final String? descriptionEn;
  final String? descriptionSw;
  final int sortOrder;

  factory Tour.fromJson(Map<String, dynamic> json) {
    return Tour(
      id: (json['id'] as num).toInt(),
      nameEn: json['name_en'] as String?,
      nameSw: json['name_sw'] as String?,
      fromPrice: (json['from_price'] as num?)?.toInt() ?? 0,
      currency: json['currency'] as String?,
      durationEn: json['duration_en'] as String?,
      durationSw: json['duration_sw'] as String?,
      image: ApiConfig.mediaUrl(json['image'] as String?),
      descriptionEn: json['description_en'] as String?,
      descriptionSw: json['description_sw'] as String?,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }

  String name(BuildContext context) =>
      localizedText(en: nameEn, sw: nameSw, context: context);

  String duration(BuildContext context) =>
      localizedText(en: durationEn, sw: durationSw, context: context);

  String description(BuildContext context) =>
      localizedText(en: descriptionEn, sw: descriptionSw, context: context);
}
