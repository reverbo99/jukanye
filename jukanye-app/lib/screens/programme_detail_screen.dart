import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import '../models/schedule_item.dart';
import '../theme/app_colors.dart';
import '../widgets/app_page_bar.dart';

class ProgrammeDetailScreen extends StatelessWidget {
  const ProgrammeDetailScreen({super.key, required this.event});

  final ScheduleItem event;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final description = event.description(context).trim();
    final location = event.location(context).trim();
    final category = event.category?.trim() ?? '';
    final dateLabel = event.startsAt == null
        ? ''
        : DateFormat('EEEE, d MMMM yyyy').format(event.startsAt!.toLocal());

    return Scaffold(
      appBar: const AppPageBar(title: 'Programme'),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 32),
        children: [
          Text(
            event.title(context),
            style: GoogleFonts.cinzel(
              color: colors.textPrimary,
              fontSize: 26,
              fontWeight: FontWeight.w700,
              height: 1.2,
            ),
          ),
          const SizedBox(height: 18),
          if (dateLabel.isNotEmpty)
            _MetaRow(
              icon: Icons.calendar_month_outlined,
              label: dateLabel,
            ),
          if (event.timeLabel.isNotEmpty) ...[
            const SizedBox(height: 10),
            _MetaRow(
              icon: Icons.schedule_outlined,
              label: event.timeLabel,
            ),
          ],
          if (location.isNotEmpty) ...[
            const SizedBox(height: 10),
            _MetaRow(
              icon: Icons.place_outlined,
              label: location,
            ),
          ],
          if (category.isNotEmpty) ...[
            const SizedBox(height: 10),
            _MetaRow(
              icon: Icons.category_outlined,
              label: category,
            ),
          ],
          const SizedBox(height: 22),
          Divider(color: colors.border),
          const SizedBox(height: 18),
          Text(
            'About this session',
            style: GoogleFonts.dmSans(
              color: AppColors.gold,
              fontSize: 13,
              fontWeight: FontWeight.w700,
              letterSpacing: 0.6,
            ),
          ),
          const SizedBox(height: 10),
          Text(
            description.isEmpty
                ? 'No description has been added for this programme item yet.'
                : description,
            style: GoogleFonts.dmSans(
              color: description.isEmpty ? colors.textMuted : colors.textPrimary,
              fontSize: 15,
              height: 1.55,
            ),
          ),
        ],
      ),
    );
  }
}

class _MetaRow extends StatelessWidget {
  const _MetaRow({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 18, color: AppColors.gold),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            label,
            style: GoogleFonts.dmSans(
              color: colors.textPrimary,
              fontSize: 14,
              height: 1.35,
            ),
          ),
        ),
      ],
    );
  }
}
