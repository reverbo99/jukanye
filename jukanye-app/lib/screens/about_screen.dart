import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../data/app_images.dart';
import '../models/festival_settings.dart';
import '../models/team_member.dart';
import '../navigation/app_drawer.dart';
import '../navigation/shell_scope.dart';
import '../theme/app_colors.dart';
import '../widgets/app_network_image.dart';
import '../widgets/async_body.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';

class AboutScreen extends StatefulWidget {
  const AboutScreen({super.key});

  @override
  State<AboutScreen> createState() => _AboutScreenState();
}

class _AboutScreenState extends State<AboutScreen> {
  FestivalSettings? _settings;
  List<TeamMember> _team = const [];
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
      final results = await Future.wait([
        JukanyeApi.instance.fetchSettings(),
        JukanyeApi.instance.fetchTeam(),
      ]);
      if (!mounted) return;
      setState(() {
        _settings = results[0] as FestivalSettings;
        _team = results[1] as List<TeamMember>;
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
    final intro = _settings?.aboutIntro(context).trim() ?? '';

    return Scaffold(
      body: RefreshIndicator(
        color: AppColors.gold,
        onRefresh: _load,
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverAppBar(
              expandedHeight: 240,
              pinned: true,
              backgroundColor: colors.background,
              automaticallyImplyLeading: false,
              leading: IconButton(
                tooltip: 'Open menu',
                onPressed: () => AppDrawer.open(
                  context,
                  goToTab: ShellScope.maybeOf(context)?.goToTab,
                ),
                icon: const Icon(Icons.menu_rounded, color: Colors.white),
              ),
              actions: [
                Padding(
                  padding: const EdgeInsets.only(right: 12),
                  child: GestureDetector(
                    onTap: () => AppRouter.open(
                      context,
                      'profile',
                      goToTab: ShellScope.maybeOf(context)?.goToTab,
                    ),
                    child: Container(
                      padding: const EdgeInsets.all(2),
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: AppColors.gold, width: 1.4),
                      ),
                      child: const CircleAvatar(
                        radius: 16,
                        backgroundImage: NetworkImage(AppImages.profileAvatar),
                      ),
                    ),
                  ),
                ),
              ],
              flexibleSpace: FlexibleSpaceBar(
                title: Text(
                  'About Jukanye',
                  style: GoogleFonts.cinzel(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                background: Stack(
                  fit: StackFit.expand,
                  children: [
                    const AppNetworkImage(url: AppImages.africanCelebration),
                    DecoratedBox(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                          colors: [
                            Colors.black.withValues(alpha: 0.2),
                            Colors.black.withValues(alpha: 0.85),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 40),
              sliver: SliverToBoxAdapter(
                child: AsyncBody<Object>(
                  loading: _loading,
                  error: _error,
                  items: _loading
                      ? const []
                      : (_error != null && intro.isEmpty && _team.isEmpty
                          ? const []
                          : const [Object()]),
                  skeleton: ScreenSkeletons.detail(),
                  onRetry: _load,
                  emptyMessage: 'About content unavailable',
                  emptyIcon: Icons.info_outline,
                  builder: (context, _) {
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Celebrating Pan-African Unity',
                          style: GoogleFonts.cinzel(
                            color: AppColors.gold,
                            fontSize: 22,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          intro.isNotEmpty
                              ? intro
                              : 'Jukanye Festival is a landmark cultural gathering in Arusha, uniting music, heritage, dialogue, and tourism across the African continent.',
                          style: GoogleFonts.dmSans(
                            color: colors.textSecondary,
                            height: 1.55,
                            fontSize: 15,
                          ),
                        ),
                        if (_team.isNotEmpty) ...[
                          const SizedBox(height: 28),
                          Text(
                            'Our Team',
                            style: GoogleFonts.cinzel(
                              color: AppColors.gold,
                              fontSize: 20,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          const SizedBox(height: 14),
                          ..._team.map(
                            (member) => Padding(
                              padding: const EdgeInsets.only(bottom: 12),
                              child: AppCard(
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    if (member.photo != null)
                                      AppNetworkImage(
                                        url: member.photo!,
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
                                          borderRadius:
                                              BorderRadius.circular(12),
                                        ),
                                        child: Icon(
                                          Icons.person_outline,
                                          color: colors.textMuted,
                                        ),
                                      ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            member.name,
                                            style: GoogleFonts.dmSans(
                                              color: colors.textPrimary,
                                              fontWeight: FontWeight.w700,
                                              fontSize: 15,
                                            ),
                                          ),
                                          if (member
                                              .role(context)
                                              .trim()
                                              .isNotEmpty) ...[
                                            const SizedBox(height: 4),
                                            Text(
                                              member.role(context),
                                              style: GoogleFonts.dmSans(
                                                color: AppColors.gold,
                                                fontSize: 13,
                                                fontWeight: FontWeight.w600,
                                              ),
                                            ),
                                          ],
                                          if (member
                                              .bio(context)
                                              .trim()
                                              .isNotEmpty) ...[
                                            const SizedBox(height: 6),
                                            Text(
                                              member.bio(context),
                                              style: GoogleFonts.dmSans(
                                                color: colors.textMuted,
                                                fontSize: 13,
                                                height: 1.4,
                                              ),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ],
                      ],
                    );
                  },
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
