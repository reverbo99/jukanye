import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../models/schedule_item.dart';
import '../theme/app_colors.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/async_body.dart';
import '../widgets/skeleton.dart';

class ProgrammeScreen extends StatefulWidget {
  const ProgrammeScreen({super.key});

  @override
  State<ProgrammeScreen> createState() => _ProgrammeScreenState();
}

class _ProgrammeScreenState extends State<ProgrammeScreen> {
  String _filter = 'All Days';
  List<ScheduleItem> _items = const [];
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
      final items = await JukanyeApi.instance.fetchSchedule();
      if (!mounted) return;
      setState(() {
        _items = items;
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

  List<String> get _filters {
    final cats = _items
        .map((e) => e.category?.trim() ?? '')
        .where((c) => c.isNotEmpty)
        .toSet()
        .toList()
      ..sort();
    return ['All Days', ...cats];
  }

  @override
  Widget build(BuildContext context) {
    final filters = _filters;
    final activeFilter =
        filters.contains(_filter) ? _filter : 'All Days';
    final events = _items.where((e) {
      if (activeFilter == 'All Days') return true;
      return (e.category ?? '') == activeFilter;
    }).toList();

    return Scaffold(
      appBar: const AppPageBar(title: 'Festival Programme'),
      body: Column(
        children: [
          if (!_loading && _items.isNotEmpty)
            SizedBox(
              height: 56,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.fromLTRB(16, 10, 16, 10),
                itemCount: filters.length,
                separatorBuilder: (_, _) => const SizedBox(width: 8),
                itemBuilder: (context, index) {
                  final tab = filters[index];
                  return _FilterChip(
                    label: tab,
                    selected: activeFilter == tab,
                    onTap: () => setState(() => _filter = tab),
                  );
                },
              ),
            ),
          Expanded(
            child: RefreshIndicator(
              color: AppColors.gold,
              onRefresh: _load,
              child: AsyncBody<ScheduleItem>(
                loading: _loading,
                error: _error,
                items: events,
                skeleton: ScreenSkeletons.programme(),
                onRetry: _load,
                emptyMessage: 'No programme items yet',
                emptyIcon: Icons.event_note_outlined,
                builder: (context, items) {
                  return ListView.separated(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                    itemCount: items.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      return _ProgrammeCard(event: items[index]);
                    },
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _FilterChip extends StatelessWidget {
  const _FilterChip({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return Material(
      color: colors.card,
      borderRadius: BorderRadius.circular(10),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(10),
        child: Container(
          height: 40,
          padding: const EdgeInsets.symmetric(horizontal: 14),
          alignment: Alignment.center,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: selected ? AppColors.gold : Colors.transparent,
              width: 1.2,
            ),
          ),
          child: Text(
            label,
            style: GoogleFonts.dmSans(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: selected ? AppColors.gold : colors.textPrimary,
            ),
          ),
        ),
      ),
    );
  }
}

class _ProgrammeCard extends StatelessWidget {
  const _ProgrammeCard({required this.event});

  final ScheduleItem event;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: colors.card,
        borderRadius: BorderRadius.circular(14),
        border: colors.isDark ? null : Border.all(color: colors.border),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 56,
            padding: const EdgeInsets.symmetric(vertical: 10),
            decoration: BoxDecoration(
              color: colors.surfaceElevated,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Column(
              children: [
                Text(
                  event.dayLabel,
                  style: GoogleFonts.dmSans(
                    color: AppColors.gold,
                    fontSize: 22,
                    fontWeight: FontWeight.w800,
                    height: 1,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  event.monthLabel,
                  style: GoogleFonts.dmSans(
                    color: AppColors.gold,
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.8,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  event.title(context),
                  style: GoogleFonts.dmSans(
                    color: colors.textPrimary,
                    fontWeight: FontWeight.w700,
                    fontSize: 16,
                    height: 1.25,
                  ),
                ),
                if (event.location(context).isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Text(
                    event.location(context),
                    style: GoogleFonts.dmSans(
                      color: colors.textMuted,
                      fontSize: 13,
                    ),
                  ),
                ],
                if (event.timeLabel.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    event.timeLabel,
                    style: GoogleFonts.dmSans(
                      color: colors.textMuted,
                      fontSize: 13,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
