import 'package:flutter/widgets.dart';

import '../config/api_config.dart';
import '../utils/locale_text.dart';

class NomineeCategoryRef {
  const NomineeCategoryRef({
    required this.id,
    required this.slug,
    required this.nameEn,
    required this.nameSw,
  });

  final int id;
  final String slug;
  final String? nameEn;
  final String? nameSw;

  factory NomineeCategoryRef.fromJson(Map<String, dynamic> json) {
    return NomineeCategoryRef(
      id: (json['id'] as num).toInt(),
      slug: json['slug'] as String? ?? '',
      nameEn: json['name_en'] as String?,
      nameSw: json['name_sw'] as String?,
    );
  }

  String name(BuildContext context) =>
      localizedText(en: nameEn, sw: nameSw, context: context);
}

class Nominee {
  const Nominee({
    required this.id,
    required this.name,
    required this.country,
    required this.photo,
    required this.bioEn,
    required this.bioSw,
    required this.links,
    required this.sortOrder,
    required this.category,
  });

  final int id;
  final String name;
  final String? country;
  final String? photo;
  final String? bioEn;
  final String? bioSw;
  final List<dynamic> links;
  final int sortOrder;
  final NomineeCategoryRef? category;

  factory Nominee.fromJson(Map<String, dynamic> json) {
    final categoryJson = json['category'];
    return Nominee(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String? ?? '',
      country: json['country'] as String?,
      photo: ApiConfig.mediaUrl(json['photo'] as String?),
      bioEn: json['bio_en'] as String?,
      bioSw: json['bio_sw'] as String?,
      links: json['links'] is List ? json['links'] as List<dynamic> : const [],
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
      category: categoryJson is Map<String, dynamic>
          ? NomineeCategoryRef.fromJson(categoryJson)
          : null,
    );
  }

  String bio(BuildContext context) =>
      localizedText(en: bioEn, sw: bioSw, context: context);
}
