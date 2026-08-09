import 'package:flutter/widgets.dart';

import '../utils/locale_text.dart';

class FestivalSettings {
  const FestivalSettings({
    required this.taglineEn,
    required this.taglineSw,
    required this.dateLabel,
    required this.locationLabel,
    required this.festivalStartsAt,
    required this.countdownAt,
    required this.donateEmbedUrl,
    required this.donateBodyEn,
    required this.donateBodySw,
    required this.totalRaised,
    required this.raisedCurrency,
    required this.aboutIntroEn,
    required this.aboutIntroSw,
    required this.downloadTextEn,
    required this.downloadTextSw,
    required this.footerContact,
    required this.social,
  });

  final String? taglineEn;
  final String? taglineSw;
  final String? dateLabel;
  final String? locationLabel;
  final DateTime? festivalStartsAt;
  final DateTime? countdownAt;
  final String? donateEmbedUrl;
  final String? donateBodyEn;
  final String? donateBodySw;
  final int totalRaised;
  final String raisedCurrency;
  final String? aboutIntroEn;
  final String? aboutIntroSw;
  final String? downloadTextEn;
  final String? downloadTextSw;
  final Map<String, dynamic> footerContact;
  final Map<String, dynamic> social;

  factory FestivalSettings.fromJson(Map<String, dynamic> json) {
    return FestivalSettings(
      taglineEn: json['tagline_en'] as String?,
      taglineSw: json['tagline_sw'] as String?,
      dateLabel: json['date_label'] as String?,
      locationLabel: json['location_label'] as String?,
      festivalStartsAt:
          DateTime.tryParse(json['festival_starts_at'] as String? ?? ''),
      countdownAt: DateTime.tryParse(json['countdown_at'] as String? ?? ''),
      donateEmbedUrl: json['donate_embed_url'] as String?,
      donateBodyEn: json['donate_body_en'] as String?,
      donateBodySw: json['donate_body_sw'] as String?,
      totalRaised: (json['total_raised'] as num?)?.toInt() ?? 0,
      raisedCurrency: (json['raised_currency'] as String?)?.trim().isNotEmpty ==
              true
          ? (json['raised_currency'] as String).trim()
          : 'TZS',
      aboutIntroEn: json['about_intro_en'] as String?,
      aboutIntroSw: json['about_intro_sw'] as String?,
      downloadTextEn: json['download_text_en'] as String?,
      downloadTextSw: json['download_text_sw'] as String?,
      footerContact: json['footer_contact'] is Map<String, dynamic>
          ? json['footer_contact'] as Map<String, dynamic>
          : const {},
      social: json['social'] is Map<String, dynamic>
          ? json['social'] as Map<String, dynamic>
          : const {},
    );
  }

  String tagline(BuildContext context) =>
      localizedText(en: taglineEn, sw: taglineSw, context: context);

  String donateBody(BuildContext context) =>
      localizedText(en: donateBodyEn, sw: donateBodySw, context: context);

  String aboutIntro(BuildContext context) =>
      localizedText(en: aboutIntroEn, sw: aboutIntroSw, context: context);

  String downloadText(BuildContext context) =>
      localizedText(en: downloadTextEn, sw: downloadTextSw, context: context);

  DateTime get countdownTarget =>
      countdownAt ?? festivalStartsAt ?? DateTime(2027, 7, 19);

  String? contactField(String key) {
    final value = footerContact[key];
    if (value == null) return null;
    final text = value.toString().trim();
    return text.isEmpty ? null : text;
  }

  String? socialField(String key) {
    final value = social[key];
    if (value == null) return null;
    final text = value.toString().trim();
    return text.isEmpty ? null : text;
  }
}
