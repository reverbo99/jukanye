import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../models/festival_settings.dart';
import '../theme/app_colors.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/skeleton.dart';

class DownloadScreen extends StatefulWidget {
  const DownloadScreen({super.key});

  @override
  State<DownloadScreen> createState() => _DownloadScreenState();
}

class _DownloadScreenState extends State<DownloadScreen> {
  FestivalSettings? _settings;
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
      final settings = await JukanyeApi.instance.fetchSettings();
      if (!mounted) return;
      setState(() {
        _settings = settings;
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
        _error = 'Unable to load download info';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final body = _settings?.downloadText(context).trim() ?? '';

    return Scaffold(
      appBar: const AppPageBar(title: 'Download'),
      body: PageSkeletonGate(
        skeleton: ScreenSkeletons.listCards(count: 3),
        child: RefreshIndicator(
          color: AppColors.gold,
          onRefresh: _load,
          child: _loading
              ? ScreenSkeletons.listCards(count: 3)
              : _error != null
                  ? ListView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      children: [
                        SizedBox(
                          height: MediaQuery.sizeOf(context).height * 0.5,
                          child: Center(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  _error!,
                                  style: GoogleFonts.dmSans(color: colors.textMuted),
                                ),
                                TextButton(
                                  onPressed: _load,
                                  child: const Text('Retry'),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    )
                  : ListView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
                      children: [
                        const Icon(
                          Icons.download_rounded,
                          color: AppColors.gold,
                          size: 48,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Download',
                          style: GoogleFonts.cinzel(
                            color: colors.textPrimary,
                            fontSize: 24,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          body.isNotEmpty
                              ? body
                              : 'Festival materials will appear here soon.',
                          style: GoogleFonts.dmSans(
                            color: colors.textMuted,
                            fontSize: 14,
                            height: 1.5,
                          ),
                        ),
                      ],
                    ),
        ),
      ),
    );
  }
}
