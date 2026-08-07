import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../models/sponsor.dart';
import '../theme/app_colors.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/async_body.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';

class SponsorsScreen extends StatefulWidget {
  const SponsorsScreen({super.key});

  @override
  State<SponsorsScreen> createState() => _SponsorsScreenState();
}

class _SponsorsScreenState extends State<SponsorsScreen> {
  List<Sponsor> _sponsors = const [];
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
      final sponsors = await JukanyeApi.instance.fetchSponsors();
      if (!mounted) return;
      setState(() {
        _sponsors = sponsors;
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
    return Scaffold(
      appBar: const AppPageBar(title: 'Sponsors'),
      body: RefreshIndicator(
        color: AppColors.gold,
        onRefresh: _load,
        child: AsyncBody<Sponsor>(
          loading: _loading,
          error: _error,
          items: _sponsors,
          skeleton: ScreenSkeletons.listCards(),
          onRetry: _load,
          emptyMessage: 'No sponsors yet',
          emptyIcon: Icons.handshake_outlined,
          builder: (context, sponsors) {
            return ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
              itemCount: sponsors.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final sponsor = sponsors[index];
                return AppCard(
                  onTap: sponsor.url == null || sponsor.url!.trim().isEmpty
                      ? null
                      : () async {
                          await Clipboard.setData(
                            ClipboardData(text: sponsor.url!),
                          );
                          if (!context.mounted) return;
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Sponsor link copied')),
                          );
                        },
                  child: Row(
                    children: [
                      if (sponsor.logo != null)
                        AppNetworkImage(
                          url: sponsor.logo!,
                          width: 64,
                          height: 64,
                          borderRadius: BorderRadius.circular(12),
                        )
                      else
                        Container(
                          width: 64,
                          height: 64,
                          decoration: BoxDecoration(
                            color: colors.surfaceElevated,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(
                            Icons.handshake_outlined,
                            color: colors.textMuted,
                          ),
                        ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              sponsor.name,
                              style: GoogleFonts.dmSans(
                                color: colors.textPrimary,
                                fontWeight: FontWeight.w700,
                                fontSize: 15,
                              ),
                            ),
                            if (sponsor.tier?.trim().isNotEmpty == true) ...[
                              const SizedBox(height: 4),
                              Text(
                                sponsor.tier!,
                                style: GoogleFonts.dmSans(
                                  color: AppColors.gold,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ],
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }
}
