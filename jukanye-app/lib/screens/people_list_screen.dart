import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../models/person.dart';
import '../navigation/app_page_route.dart';
import '../theme/app_colors.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/async_body.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';
import 'person_detail_screen.dart';

class PeopleListScreen extends StatefulWidget {
  const PeopleListScreen({
    super.key,
    required this.title,
    required this.type,
    this.emptyIcon = Icons.people_outline,
  });

  final String title;
  final String type;
  final IconData emptyIcon;

  @override
  State<PeopleListScreen> createState() => _PeopleListScreenState();
}

class _PeopleListScreenState extends State<PeopleListScreen> {
  List<Person> _people = const [];
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
      final people = await JukanyeApi.instance.fetchPeople(type: widget.type);
      if (!mounted) return;
      setState(() {
        _people = people;
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
      appBar: AppPageBar(title: widget.title),
      body: RefreshIndicator(
        color: AppColors.gold,
        onRefresh: _load,
        child: AsyncBody<Person>(
          loading: _loading,
          error: _error,
          items: _people,
          skeleton: ScreenSkeletons.listCards(),
          onRetry: _load,
          emptyMessage: 'No ${widget.title.toLowerCase()} yet',
          emptyIcon: widget.emptyIcon,
          builder: (context, people) {
            return ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
              itemCount: people.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final person = people[index];
                return AppCard(
                  onTap: () {
                    AppNav.push(
                      context,
                      PersonDetailScreen(personId: person.id),
                    );
                  },
                  child: Row(
                    children: [
                      if (person.photo != null)
                        AppNetworkImage(
                          url: person.photo!,
                          width: 72,
                          height: 72,
                          borderRadius: BorderRadius.circular(12),
                        )
                      else
                        Container(
                          width: 72,
                          height: 72,
                          decoration: BoxDecoration(
                            color: colors.surfaceElevated,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(
                            widget.emptyIcon,
                            color: colors.textMuted,
                          ),
                        ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              person.name,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.dmSans(
                                color: colors.textPrimary,
                                fontWeight: FontWeight.w700,
                                fontSize: 15,
                              ),
                            ),
                            if (person.subtitle(context).trim().isNotEmpty) ...[
                              const SizedBox(height: 4),
                              Text(
                                person.subtitle(context),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: GoogleFonts.dmSans(
                                  color: colors.textMuted,
                                  fontSize: 13,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                      Icon(Icons.chevron_right, color: colors.textMuted),
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
