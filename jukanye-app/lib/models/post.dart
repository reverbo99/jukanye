import 'package:flutter/widgets.dart';

import '../config/api_config.dart';
import '../utils/locale_text.dart';

class Post {
  const Post({
    required this.id,
    required this.slug,
    required this.titleEn,
    required this.titleSw,
    required this.excerptEn,
    required this.excerptSw,
    required this.coverImage,
    required this.publishedAt,
    this.bodyEn,
    this.bodySw,
  });

  final int id;
  final String slug;
  final String? titleEn;
  final String? titleSw;
  final String? excerptEn;
  final String? excerptSw;
  final String? coverImage;
  final DateTime? publishedAt;
  final String? bodyEn;
  final String? bodySw;

  factory Post.fromJson(Map<String, dynamic> json) {
    return Post(
      id: (json['id'] as num).toInt(),
      slug: json['slug'] as String? ?? '',
      titleEn: json['title_en'] as String?,
      titleSw: json['title_sw'] as String?,
      excerptEn: json['excerpt_en'] as String?,
      excerptSw: json['excerpt_sw'] as String?,
      coverImage: ApiConfig.mediaUrl(json['cover_image'] as String?),
      publishedAt: DateTime.tryParse(json['published_at'] as String? ?? ''),
      bodyEn: json['body_en'] as String?,
      bodySw: json['body_sw'] as String?,
    );
  }

  String title(BuildContext context) =>
      localizedText(en: titleEn, sw: titleSw, context: context);

  String excerpt(BuildContext context) =>
      localizedText(en: excerptEn, sw: excerptSw, context: context);

  String body(BuildContext context) =>
      localizedText(en: bodyEn, sw: bodySw, context: context);
}
