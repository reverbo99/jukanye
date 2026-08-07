import 'package:flutter/widgets.dart';

import '../config/api_config.dart';
import '../utils/locale_text.dart';

class TeamMember {
  const TeamMember({
    required this.id,
    required this.name,
    required this.roleEn,
    required this.roleSw,
    required this.photo,
    required this.bioEn,
    required this.bioSw,
    required this.sortOrder,
  });

  final int id;
  final String name;
  final String? roleEn;
  final String? roleSw;
  final String? photo;
  final String? bioEn;
  final String? bioSw;
  final int sortOrder;

  factory TeamMember.fromJson(Map<String, dynamic> json) {
    return TeamMember(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String? ?? '',
      roleEn: json['role_en'] as String?,
      roleSw: json['role_sw'] as String?,
      photo: ApiConfig.mediaUrl(json['photo'] as String?),
      bioEn: json['bio_en'] as String?,
      bioSw: json['bio_sw'] as String?,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }

  String role(BuildContext context) =>
      localizedText(en: roleEn, sw: roleSw, context: context);

  String bio(BuildContext context) =>
      localizedText(en: bioEn, sw: bioSw, context: context);
}
