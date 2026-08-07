import 'package:flutter/widgets.dart';

import '../utils/locale_text.dart';

class HomeSection {
  const HomeSection({
    required this.id,
    required this.type,
    required this.titleEn,
    required this.titleSw,
    required this.bodyEn,
    required this.bodySw,
    required this.link,
    required this.sortOrder,
  });

  final int id;
  final String type;
  final String? titleEn;
  final String? titleSw;
  final String? bodyEn;
  final String? bodySw;
  final String? link;
  final int sortOrder;

  factory HomeSection.fromJson(Map<String, dynamic> json) {
    return HomeSection(
      id: (json['id'] as num).toInt(),
      type: json['type'] as String? ?? '',
      titleEn: json['title_en'] as String?,
      titleSw: json['title_sw'] as String?,
      bodyEn: json['body_en'] as String?,
      bodySw: json['body_sw'] as String?,
      link: json['link'] as String?,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }

  String title(BuildContext context) =>
      localizedText(en: titleEn, sw: titleSw, context: context);

  String body(BuildContext context) =>
      localizedText(en: bodyEn, sw: bodySw, context: context);
}
