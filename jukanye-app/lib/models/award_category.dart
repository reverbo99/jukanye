import 'package:flutter/widgets.dart';

import '../utils/locale_text.dart';

class AwardCategory {
  const AwardCategory({
    required this.id,
    required this.slug,
    required this.nameEn,
    required this.nameSw,
    required this.descriptionEn,
    required this.descriptionSw,
    required this.sortOrder,
  });

  final int id;
  final String slug;
  final String? nameEn;
  final String? nameSw;
  final String? descriptionEn;
  final String? descriptionSw;
  final int sortOrder;

  factory AwardCategory.fromJson(Map<String, dynamic> json) {
    return AwardCategory(
      id: (json['id'] as num).toInt(),
      slug: json['slug'] as String? ?? '',
      nameEn: json['name_en'] as String?,
      nameSw: json['name_sw'] as String?,
      descriptionEn: json['description_en'] as String?,
      descriptionSw: json['description_sw'] as String?,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }

  String name(BuildContext context) =>
      localizedText(en: nameEn, sw: nameSw, context: context);

  String description(BuildContext context) =>
      localizedText(en: descriptionEn, sw: descriptionSw, context: context);
}
