import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../theme/app_colors.dart';
import 'skeleton.dart';

class AppNetworkImage extends StatelessWidget {
  const AppNetworkImage({
    super.key,
    required this.url,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
    this.borderRadius,
  });

  final String url;
  final double? width;
  final double? height;
  final BoxFit fit;
  final BorderRadius? borderRadius;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final image = CachedNetworkImage(
      imageUrl: url,
      width: width,
      height: height,
      fit: fit,
      fadeInDuration: const Duration(milliseconds: 280),
      placeholder: (context, url) => SkeletonBox(
        width: width ?? double.infinity,
        height: height ?? 160,
        borderRadius: borderRadius ?? BorderRadius.zero,
      ),
      errorWidget: (context, url, error) => Container(
        width: width,
        height: height,
        color: colors.surfaceElevated,
        alignment: Alignment.center,
        child: Icon(Icons.image_not_supported_outlined, color: colors.textMuted),
      ),
    );

    if (borderRadius == null) return image;
    return ClipRRect(borderRadius: borderRadius!, child: image);
  }
}
