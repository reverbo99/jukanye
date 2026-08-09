import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import 'screens/splash_screen.dart';
import 'services/auth_session.dart';
import 'theme/app_theme.dart';
import 'theme/theme_controller.dart';

final themeController = ThemeController();
final authSession = AuthSession.instance;

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.light,
    ),
  );
  await Future.wait([
    themeController.load(),
    authSession.load(),
  ]);
  runApp(const JukanyeApp());
}

class JukanyeApp extends StatelessWidget {
  const JukanyeApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: themeController,
      builder: (context, _) {
        return MaterialApp(
          title: 'Jukanye Festival',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.light(),
          darkTheme: AppTheme.dark(),
          themeMode: themeController.mode,
          home: const SplashScreen(),
        );
      },
    );
  }
}
