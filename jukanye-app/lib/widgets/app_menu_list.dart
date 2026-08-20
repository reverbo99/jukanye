import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../data/app_data.dart';
import '../navigation/app_drawer.dart';
import '../theme/app_colors.dart';
import 'app_button.dart';

/// Expandable festival menu + primary action buttons.
class AppMenuList extends StatefulWidget {
  const AppMenuList({
    super.key,
    required this.onSelectRoute,
    this.dense = false,
  });

  final ValueChanged<String> onSelectRoute;
  final bool dense;

  @override
  State<AppMenuList> createState() => _AppMenuListState();
}

class _AppMenuListState extends State<AppMenuList> {
  final _expanded = <String>{};

  void _toggle(String route) {
    setState(() {
      if (_expanded.contains(route)) {
        _expanded.remove(route);
      } else {
        _expanded.add(route);
      }
    });
  }

  AppButtonVariant _variantFor(AppActionVariant variant) {
    return switch (variant) {
      AppActionVariant.register => AppButtonVariant.green,
      AppActionVariant.tickets => AppButtonVariant.gold,
      AppActionVariant.donate => AppButtonVariant.blue,
    };
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Padding(
          padding: EdgeInsets.fromLTRB(widget.dense ? 16 : 20, 8, widget.dense ? 16 : 20, 12),
          child: Column(
            children: [
              for (final action in AppData.actionButtons) ...[
                _ActionButton(
                  labelEn: action.$1,
                  labelSw: action.$2,
                  variant: _variantFor(action.$4),
                  onPressed: () => widget.onSelectRoute(action.$3),
                ),
                const SizedBox(height: 8),
              ],
            ],
          ),
        ),
        const Divider(height: 1),
        Expanded(
          child: ListView(
            padding: EdgeInsets.symmetric(
              vertical: 8,
              horizontal: widget.dense ? 8 : 4,
            ),
            children: [
              for (final entry in AppData.menuTree)
                if (entry.hasChildren)
                  _ExpandableMenuTile(
                    entry: entry,
                    expanded: _expanded.contains(entry.route),
                    onToggle: () => _toggle(entry.route),
                    onSelectRoute: widget.onSelectRoute,
                    dense: widget.dense,
                  )
                else
                  _MenuTile(
                    entry: entry,
                    onTap: () => widget.onSelectRoute(entry.route),
                    dense: widget.dense,
                  ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ActionButton extends StatelessWidget {
  const _ActionButton({
    required this.labelEn,
    required this.labelSw,
    required this.variant,
    required this.onPressed,
  });

  final String labelEn;
  final String labelSw;
  final AppButtonVariant variant;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return AppButton(
      label: '$labelEn / $labelSw',
      variant: variant,
      height: 46,
      onPressed: onPressed,
    );
  }
}

class _MenuTile extends StatelessWidget {
  const _MenuTile({
    required this.entry,
    required this.onTap,
    required this.dense,
    this.indent = 0,
  });

  final MenuEntry entry;
  final VoidCallback onTap;
  final bool dense;
  final double indent;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);

    if (dense) {
      return ListTile(
        contentPadding: EdgeInsets.only(left: 20 + indent, right: 12),
        leading: Icon(menuIconFor(entry.icon), color: AppColors.gold, size: 22),
        title: Text(
          entry.label,
          style: TextStyle(
            color: colors.textPrimary,
            fontWeight: FontWeight.w600,
          ),
        ),
        onTap: onTap,
      );
    }

    return Padding(
      padding: EdgeInsets.fromLTRB(16 + indent, 0, 16, 6),
      child: Material(
        color: colors.card,
        borderRadius: BorderRadius.circular(14),
        child: ListTile(
          onTap: onTap,
          leading: Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: colors.surfaceElevated,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(menuIconFor(entry.icon), color: AppColors.gold, size: 20),
          ),
          title: Text(
            entry.label,
            style: GoogleFonts.dmSans(
              color: colors.textPrimary,
              fontWeight: FontWeight.w600,
            ),
          ),
          trailing: Icon(Icons.chevron_right, color: colors.textMuted),
        ),
      ),
    );
  }
}

class _ExpandableMenuTile extends StatelessWidget {
  const _ExpandableMenuTile({
    required this.entry,
    required this.expanded,
    required this.onToggle,
    required this.onSelectRoute,
    required this.dense,
  });

  final MenuEntry entry;
  final bool expanded;
  final VoidCallback onToggle;
  final ValueChanged<String> onSelectRoute;
  final bool dense;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (dense)
          ListTile(
            contentPadding: const EdgeInsets.symmetric(horizontal: 20),
            leading: Icon(menuIconFor(entry.icon), color: AppColors.gold, size: 22),
            title: Text(
              entry.label,
              style: TextStyle(
                color: colors.textPrimary,
                fontWeight: FontWeight.w700,
              ),
            ),
            trailing: Icon(
              expanded ? Icons.expand_less : Icons.expand_more,
              color: colors.textMuted,
            ),
            onTap: onToggle,
          )
        else
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 6),
            child: Material(
              color: colors.card,
              borderRadius: BorderRadius.circular(14),
              child: ListTile(
                onTap: onToggle,
                leading: Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: colors.surfaceElevated,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(menuIconFor(entry.icon), color: AppColors.gold, size: 20),
                ),
                title: Text(
                  entry.label,
                  style: GoogleFonts.dmSans(
                    color: colors.textPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                trailing: Icon(
                  expanded ? Icons.expand_less : Icons.expand_more,
                  color: colors.textMuted,
                ),
              ),
            ),
          ),
        if (expanded)
          for (final child in entry.children)
            _MenuTile(
              entry: child,
              indent: dense ? 12 : 8,
              dense: dense,
              onTap: () => onSelectRoute(child.route),
            ),
      ],
    );
  }
}
