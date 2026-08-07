import 'package:flutter/widgets.dart';

import '../config/api_config.dart';
import '../utils/locale_text.dart';

class PersonLink {
  const PersonLink({required this.label, required this.url});

  final String? label;
  final String url;

  factory PersonLink.fromJson(Map<String, dynamic> json) {
    return PersonLink(
      label: json['label'] as String?,
      url: json['url'] as String? ?? '',
    );
  }
}

class Person {
  const Person({
    required this.id,
    required this.type,
    required this.name,
    required this.subtitleEn,
    required this.subtitleSw,
    required this.photo,
    required this.bioEn,
    required this.bioSw,
    required this.links,
    required this.sortOrder,
  });

  final int id;
  final String type;
  final String name;
  final String? subtitleEn;
  final String? subtitleSw;
  final String? photo;
  final String? bioEn;
  final String? bioSw;
  final List<PersonLink> links;
  final int sortOrder;

  factory Person.fromJson(Map<String, dynamic> json) {
    final rawLinks = json['links'];
    final links = <PersonLink>[];
    if (rawLinks is List) {
      for (final item in rawLinks) {
        if (item is Map<String, dynamic>) {
          links.add(PersonLink.fromJson(item));
        } else if (item is String && item.trim().isNotEmpty) {
          links.add(PersonLink(label: null, url: item.trim()));
        }
      }
    }

    return Person(
      id: (json['id'] as num).toInt(),
      type: json['type'] as String? ?? '',
      name: json['name'] as String? ?? '',
      subtitleEn: json['subtitle_en'] as String?,
      subtitleSw: json['subtitle_sw'] as String?,
      photo: ApiConfig.mediaUrl(json['photo'] as String?),
      bioEn: json['bio_en'] as String?,
      bioSw: json['bio_sw'] as String?,
      links: links,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }

  String subtitle(BuildContext context) =>
      localizedText(en: subtitleEn, sw: subtitleSw, context: context);

  String bio(BuildContext context) =>
      localizedText(en: bioEn, sw: bioSw, context: context);
}
