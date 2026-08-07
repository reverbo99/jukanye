import '../config/api_config.dart';

class Sponsor {
  const Sponsor({
    required this.id,
    required this.name,
    required this.logo,
    required this.url,
    required this.tier,
    required this.sortOrder,
  });

  final int id;
  final String name;
  final String? logo;
  final String? url;
  final String? tier;
  final int sortOrder;

  factory Sponsor.fromJson(Map<String, dynamic> json) {
    return Sponsor(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String? ?? '',
      logo: ApiConfig.mediaUrl(json['logo'] as String?),
      url: json['url'] as String?,
      tier: json['tier'] as String?,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }
}
