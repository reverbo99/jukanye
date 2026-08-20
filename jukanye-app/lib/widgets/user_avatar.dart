import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../data/app_images.dart';
import '../services/auth_session.dart';
import '../theme/app_colors.dart';

/// Circular avatar that follows the logged-in user's photo.
class UserAvatar extends StatelessWidget {
  const UserAvatar({super.key, this.radius = 16});

  final double radius;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final size = radius * 2;

    return ListenableBuilder(
      listenable: AuthSession.instance,
      builder: (context, _) {
        final url = AuthSession.instance.avatarUrl ?? AppImages.profileAvatar;
        return ClipOval(
          child: ColoredBox(
            color: colors.card,
            child: CachedNetworkImage(
              imageUrl: url,
              width: size,
              height: size,
              fit: BoxFit.cover,
              fadeInDuration: const Duration(milliseconds: 180),
              placeholder: (context, url) => SizedBox(
                width: size,
                height: size,
                child: Icon(Icons.person, size: radius, color: colors.textMuted),
              ),
              errorWidget: (context, url, error) => SizedBox(
                width: size,
                height: size,
                child: Icon(Icons.person, size: radius, color: colors.textMuted),
              ),
            ),
          ),
        );
      },
    );
  }
}
