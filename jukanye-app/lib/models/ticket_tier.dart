import 'package:flutter/widgets.dart';

import '../utils/locale_text.dart';

class TicketTier {
  const TicketTier({
    required this.id,
    required this.slug,
    required this.nameEn,
    required this.nameSw,
    required this.price,
    required this.currency,
    required this.descriptionEn,
    required this.descriptionSw,
    required this.includes,
    required this.sortOrder,
  });

  final int id;
  final String slug;
  final String? nameEn;
  final String? nameSw;
  final int price;
  final String? currency;
  final String? descriptionEn;
  final String? descriptionSw;
  final List<String> includes;
  final int sortOrder;

  factory TicketTier.fromJson(Map<String, dynamic> json) {
    final rawIncludes = json['includes'];
    final includes = <String>[];
    if (rawIncludes is List) {
      for (final item in rawIncludes) {
        final text = item?.toString().trim() ?? '';
        if (text.isNotEmpty) includes.add(text);
      }
    }

    return TicketTier(
      id: (json['id'] as num).toInt(),
      slug: json['slug'] as String? ?? '',
      nameEn: json['name_en'] as String?,
      nameSw: json['name_sw'] as String?,
      price: (json['price'] as num?)?.toInt() ?? 0,
      currency: json['currency'] as String?,
      descriptionEn: json['description_en'] as String?,
      descriptionSw: json['description_sw'] as String?,
      includes: includes,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }

  String name(BuildContext context) =>
      localizedText(en: nameEn, sw: nameSw, context: context);

  String description(BuildContext context) =>
      localizedText(en: descriptionEn, sw: descriptionSw, context: context);
}
