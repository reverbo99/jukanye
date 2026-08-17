import 'package:flutter/widgets.dart';

import '../utils/locale_text.dart';

class SiteMediaItem {
  const SiteMediaItem({
    required this.id,
    required this.slot,
    required this.kind,
    required this.imageUrl,
    required this.youtubeUrl,
    required this.youtubeId,
    required this.youtubeEmbedUrl,
    required this.youtubeThumbnailUrl,
    required this.titleEn,
    required this.titleSw,
    required this.captionEn,
    required this.captionSw,
    required this.link,
    required this.sortOrder,
  });

  final int id;
  final String slot;
  final String kind;
  final String? imageUrl;
  final String? youtubeUrl;
  final String? youtubeId;
  final String? youtubeEmbedUrl;
  final String? youtubeThumbnailUrl;
  final String? titleEn;
  final String? titleSw;
  final String? captionEn;
  final String? captionSw;
  final String? link;
  final int sortOrder;

  bool get isImage => kind == 'image';
  bool get isYoutube => kind == 'youtube';

  String? displayUrl(BuildContext context) {
    if (isImage && imageUrl != null && imageUrl!.trim().isNotEmpty) {
      return imageUrl;
    }
    if (isYoutube &&
        youtubeThumbnailUrl != null &&
        youtubeThumbnailUrl!.trim().isNotEmpty) {
      return youtubeThumbnailUrl;
    }
    return null;
  }

  String title(BuildContext context) =>
      localizedText(en: titleEn, sw: titleSw, context: context);

  String caption(BuildContext context) =>
      localizedText(en: captionEn, sw: captionSw, context: context);

  factory SiteMediaItem.fromJson(Map<String, dynamic> json) {
    return SiteMediaItem(
      id: (json['id'] as num).toInt(),
      slot: json['slot'] as String? ?? '',
      kind: json['kind'] as String? ?? 'image',
      imageUrl: json['image_url'] as String?,
      youtubeUrl: json['youtube_url'] as String?,
      youtubeId: json['youtube_id'] as String?,
      youtubeEmbedUrl: json['youtube_embed_url'] as String?,
      youtubeThumbnailUrl: json['youtube_thumbnail_url'] as String?,
      titleEn: json['title_en'] as String?,
      titleSw: json['title_sw'] as String?,
      captionEn: json['caption_en'] as String?,
      captionSw: json['caption_sw'] as String?,
      link: json['link'] as String?,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }
}
