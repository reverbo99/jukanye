import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

/// Soft shimmer bone used everywhere instead of CircularProgressIndicator.
class SkeletonBox extends StatefulWidget {
  const SkeletonBox({
    super.key,
    this.width,
    this.height,
    this.borderRadius = const BorderRadius.all(Radius.circular(12)),
  });

  final double? width;
  final double? height;
  final BorderRadius borderRadius;

  @override
  State<SkeletonBox> createState() => _SkeletonBoxState();
}

class _SkeletonBoxState extends State<SkeletonBox>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return ShaderMask(
          blendMode: BlendMode.srcATop,
          shaderCallback: (bounds) {
            final t = _controller.value;
            return LinearGradient(
              begin: Alignment(-1.5 + 3 * t, 0),
              end: Alignment(-0.5 + 3 * t, 0),
              colors: [
                colors.skeletonBase,
                colors.skeletonHighlight,
                colors.skeletonBase,
              ],
              stops: const [0.25, 0.5, 0.75],
            ).createShader(bounds);
          },
          child: child,
        );
      },
      child: Container(
        width: widget.width,
        height: widget.height,
        decoration: BoxDecoration(
          color: colors.skeletonBase,
          borderRadius: widget.borderRadius,
        ),
      ),
    );
  }
}

class SkeletonLine extends StatelessWidget {
  const SkeletonLine({
    super.key,
    this.width,
    this.height = 12,
  });

  final double? width;
  final double height;

  @override
  Widget build(BuildContext context) {
    return SkeletonBox(
      width: width,
      height: height,
      borderRadius: BorderRadius.circular(8),
    );
  }
}

/// Shows [skeleton] briefly, then fades in [child]. Used as page loader.
class PageSkeletonGate extends StatefulWidget {
  const PageSkeletonGate({
    super.key,
    required this.child,
    required this.skeleton,
    this.duration = const Duration(milliseconds: 700),
  });

  final Widget child;
  final Widget skeleton;
  final Duration duration;

  @override
  State<PageSkeletonGate> createState() => _PageSkeletonGateState();
}

class _PageSkeletonGateState extends State<PageSkeletonGate> {
  bool _ready = false;

  @override
  void initState() {
    super.initState();
    Future<void>.delayed(widget.duration, () {
      if (mounted) setState(() => _ready = true);
    });
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 350),
      switchInCurve: Curves.easeOut,
      switchOutCurve: Curves.easeIn,
      child: _ready
          ? KeyedSubtree(key: const ValueKey('content'), child: widget.child)
          : KeyedSubtree(key: const ValueKey('skeleton'), child: widget.skeleton),
    );
  }
}

/// Common full-screen list skeletons matching Jukanye UI.
class ScreenSkeletons {
  static Widget feed(BuildContext context) {
    return SafeArea(
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
        physics: const NeverScrollableScrollPhysics(),
        children: [
          Row(
            children: [
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SkeletonLine(width: 80, height: 10),
                    SizedBox(height: 10),
                    SkeletonLine(width: 140, height: 18),
                  ],
                ),
              ),
              const SkeletonBox(
                width: 44,
                height: 44,
                borderRadius: BorderRadius.all(Radius.circular(22)),
              ),
            ],
          ),
          const SizedBox(height: 18),
          const SkeletonBox(height: 180),
          const SizedBox(height: 16),
          const SkeletonBox(height: 130),
          const SizedBox(height: 20),
          const SkeletonLine(width: 120, height: 16),
          const SizedBox(height: 12),
          SizedBox(
            height: 150,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: 3,
              separatorBuilder: (_, _) => const SizedBox(width: 12),
              itemBuilder: (_, _) => const SkeletonBox(width: 210, height: 150),
            ),
          ),
        ],
      ),
    );
  }

  static Widget listCards({int count = 5}) {
    return SafeArea(
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
        physics: const NeverScrollableScrollPhysics(),
        itemCount: count,
        separatorBuilder: (_, _) => const SizedBox(height: 12),
        itemBuilder: (_, _) => const SkeletonBox(height: 110),
      ),
    );
  }

  static Widget grid({int count = 4}) {
    return SafeArea(
      child: GridView.builder(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          childAspectRatio: 0.72,
        ),
        itemCount: count,
        itemBuilder: (_, _) => const SkeletonBox(height: 220),
      ),
    );
  }

  static Widget detail() {
    return SafeArea(
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
        physics: const NeverScrollableScrollPhysics(),
        children: const [
          SkeletonBox(height: 180),
          SizedBox(height: 18),
          SkeletonLine(width: 180, height: 22),
          SizedBox(height: 10),
          SkeletonLine(width: 120, height: 16),
          SizedBox(height: 16),
          SkeletonLine(height: 12),
          SizedBox(height: 8),
          SkeletonLine(height: 12),
          SizedBox(height: 8),
          SkeletonLine(width: 220, height: 12),
          SizedBox(height: 24),
          SkeletonBox(height: 52),
        ],
      ),
    );
  }

  static Widget programme({int count = 5}) {
    return SafeArea(
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(16, 62, 16, 24),
        physics: const NeverScrollableScrollPhysics(),
        itemCount: count,
        separatorBuilder: (_, _) => const SizedBox(height: 12),
        itemBuilder: (_, _) => const Padding(
          padding: EdgeInsets.all(14),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SkeletonBox(width: 56, height: 58),
              SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SkeletonLine(width: 180, height: 14),
                    SizedBox(height: 10),
                    SkeletonLine(width: 140, height: 11),
                    SizedBox(height: 8),
                    SkeletonLine(width: 160, height: 11),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  static Widget paymentBusy() {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            SkeletonBox(
              width: 88,
              height: 88,
              borderRadius: BorderRadius.all(Radius.circular(44)),
            ),
            SizedBox(height: 22),
            SkeletonLine(width: 160, height: 18),
            SizedBox(height: 12),
            SkeletonLine(width: 220, height: 12),
            SizedBox(height: 8),
            SkeletonLine(width: 180, height: 12),
          ],
        ),
      ),
    );
  }
}
