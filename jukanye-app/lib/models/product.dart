import 'package:flutter/widgets.dart';

import '../config/api_config.dart';
import '../utils/locale_text.dart';

class Product {
  const Product({
    required this.id,
    required this.nameEn,
    required this.nameSw,
    required this.price,
    required this.currency,
    required this.taglineEn,
    required this.taglineSw,
    required this.descriptionEn,
    required this.descriptionSw,
    required this.image,
    required this.sortOrder,
  });

  final int id;
  final String? nameEn;
  final String? nameSw;
  final int price;
  final String? currency;
  final String? taglineEn;
  final String? taglineSw;
  final String? descriptionEn;
  final String? descriptionSw;
  final String? image;
  final int sortOrder;

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: (json['id'] as num).toInt(),
      nameEn: json['name_en'] as String?,
      nameSw: json['name_sw'] as String?,
      price: (json['price'] as num?)?.toInt() ?? 0,
      currency: json['currency'] as String?,
      taglineEn: json['tagline_en'] as String?,
      taglineSw: json['tagline_sw'] as String?,
      descriptionEn: json['description_en'] as String?,
      descriptionSw: json['description_sw'] as String?,
      image: ApiConfig.mediaUrl(json['image'] as String?),
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }

  String name(BuildContext context) =>
      localizedText(en: nameEn, sw: nameSw, context: context);

  String tagline(BuildContext context) =>
      localizedText(en: taglineEn, sw: taglineSw, context: context);

  String description(BuildContext context) =>
      localizedText(en: descriptionEn, sw: descriptionSw, context: context);
}
