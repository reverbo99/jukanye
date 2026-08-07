import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../models/person.dart';
import '../theme/app_colors.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';

class PersonDetailScreen extends StatefulWidget {
  const PersonDetailScreen({super.key, required this.personId});

  final int personId;

  @override
  State<PersonDetailScreen> createState() => _PersonDetailScreenState();
}

class _PersonDetailScreenState extends State<PersonDetailScreen> {
  Person? _person;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final person = await JukanyeApi.instance.fetchPerson(widget.personId);
      if (!mounted) return;
      setState(() {
        _person = person;
        _loading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.message;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = 'Something went wrong';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final person = _person;

    return Scaffold(
      appBar: AppPageBar(title: person?.name ?? 'Profile'),
      body: _loading
          ? ScreenSkeletons.detail()
          : _error != null && person == null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(28),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.wifi_off_rounded,
                            size: 42, color: colors.textMuted),
                        const SizedBox(height: 14),
                        Text(
                          'Could not load',
                          style: GoogleFonts.dmSans(
                            color: colors.textPrimary,
                            fontSize: 17,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _error!,
                          textAlign: TextAlign.center,
                          style: GoogleFonts.dmSans(
                            color: colors.textMuted,
                            fontSize: 13,
                          ),
                        ),
                        TextButton(
                          onPressed: _load,
                          child: const Text(
                            'Retry',
                            style: TextStyle(
                              color: AppColors.gold,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  color: AppColors.gold,
                  onRefresh: _load,
                  child: ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
                    children: [
                      if (person!.photo != null)
                        ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: AppNetworkImage(
                            url: person.photo!,
                            height: 220,
                            width: double.infinity,
                          ),
                        )
                      else
                        Container(
                          height: 180,
                          decoration: BoxDecoration(
                            color: colors.surfaceElevated,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Icon(
                            Icons.person_outline,
                            size: 64,
                            color: colors.textMuted,
                          ),
                        ),
                      const SizedBox(height: 18),
                      Text(
                        person.name,
                        style: GoogleFonts.cinzel(
                          color: AppColors.gold,
                          fontSize: 24,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      if (person.subtitle(context).trim().isNotEmpty) ...[
                        const SizedBox(height: 6),
                        Text(
                          person.subtitle(context),
                          style: GoogleFonts.dmSans(
                            color: colors.textMuted,
                            fontSize: 14,
                          ),
                        ),
                      ],
                      if (person.bio(context).trim().isNotEmpty) ...[
                        const SizedBox(height: 16),
                        Text(
                          person.bio(context),
                          style: GoogleFonts.dmSans(
                            color: colors.textSecondary,
                            height: 1.55,
                            fontSize: 15,
                          ),
                        ),
                      ],
                      if (person.links.isNotEmpty) ...[
                        const SizedBox(height: 22),
                        Text(
                          'Links',
                          style: GoogleFonts.cinzel(
                            color: colors.textPrimary,
                            fontWeight: FontWeight.w700,
                            fontSize: 16,
                          ),
                        ),
                        const SizedBox(height: 10),
                        ...person.links.map(
                          (link) => Padding(
                            padding: const EdgeInsets.only(bottom: 8),
                            child: AppCard(
                              onTap: () async {
                                await Clipboard.setData(
                                  ClipboardData(text: link.url),
                                );
                                if (!context.mounted) return;
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text('Link copied'),
                                  ),
                                );
                              },
                              child: Row(
                                children: [
                                  const Icon(Icons.link, color: AppColors.gold),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Text(
                                      (link.label?.trim().isNotEmpty ?? false)
                                          ? link.label!
                                          : link.url,
                                      style: GoogleFonts.dmSans(
                                        color: colors.textPrimary,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                  Icon(Icons.copy,
                                      size: 16, color: colors.textMuted),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
    );
  }
}
