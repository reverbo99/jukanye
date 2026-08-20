import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../data/app_images.dart';
import '../main.dart';
import '../navigation/app_page_route.dart';
import '../screens/edit_profile_screen.dart';
import '../screens/my_tickets_screen.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';
import '../widgets/app_page_bar.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _registerMode = false;
  bool _busy = false;
  bool _donationsLoading = false;
  String? _donationsError;
  List<Map<String, dynamic>> _donations = const [];

  final nameController = TextEditingController();
  final emailController = TextEditingController();
  final phoneController = TextEditingController();
  final passwordController = TextEditingController();
  final confirmController = TextEditingController();

  @override
  void initState() {
    super.initState();
    authSession.addListener(_onSessionChanged);
    if (authSession.isLoggedIn) {
      _loadDonations();
    }
  }

  @override
  void dispose() {
    authSession.removeListener(_onSessionChanged);
    nameController.dispose();
    emailController.dispose();
    phoneController.dispose();
    passwordController.dispose();
    confirmController.dispose();
    super.dispose();
  }

  void _onSessionChanged() {
    if (!mounted) return;
    setState(() {});
    if (authSession.isLoggedIn) {
      _loadDonations();
    } else {
      setState(() {
        _donations = const [];
        _donationsError = null;
      });
    }
  }

  Future<void> _loadDonations() async {
    if (!authSession.isLoggedIn) return;
    setState(() {
      _donationsLoading = true;
      _donationsError = null;
    });
    try {
      final items = await JukanyeApi.instance.fetchMyDonations();
      if (!mounted) return;
      setState(() {
        _donations = items;
        _donationsLoading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _donationsError = e.message;
        _donationsLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _donationsError = 'Unable to load donations';
        _donationsLoading = false;
      });
    }
  }

  Future<void> _submitAuth() async {
    final email = emailController.text.trim();
    final password = passwordController.text;
    if (!email.contains('@') || password.length < 6) {
      _snack('Enter a valid email and password (min 6 chars)');
      return;
    }

    setState(() => _busy = true);
    try {
      if (_registerMode) {
        final name = nameController.text.trim();
        final confirm = confirmController.text;
        if (name.length < 2) {
          _snack('Enter your name');
          return;
        }
        if (password != confirm) {
          _snack('Passwords do not match');
          return;
        }
        final result = await JukanyeApi.instance.register(
          name: name,
          email: email,
          password: password,
          passwordConfirmation: confirm,
          phone: phoneController.text.trim(),
        );
        await authSession.saveSession(token: result.token, user: result.user);
        _snack('Welcome, ${result.user['name'] ?? name}');
      } else {
        final result = await JukanyeApi.instance.login(
          email: email,
          password: password,
        );
        await authSession.saveSession(token: result.token, user: result.user);
        _snack('Signed in');
      }
      passwordController.clear();
      confirmController.clear();
    } on ApiException catch (e) {
      _snack(e.message);
    } catch (_) {
      _snack('Authentication failed');
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _logout() async {
    setState(() => _busy = true);
    try {
      await JukanyeApi.instance.logout();
    } catch (_) {
      // ignore
    }
    await authSession.clear();
    if (!mounted) return;
    setState(() => _busy = false);
    _snack('Logged out');
  }

  void _snack(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: authSession,
      builder: (context, _) {
        if (!authSession.isLoggedIn) {
          return _buildAuth(context);
        }
        return _buildLoggedIn(context);
      },
    );
  }

  Widget _buildAuth(BuildContext context) {
    final colors = AppColors.of(context);
    return Scaffold(
      appBar: const AppPageBar(title: 'Profile'),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
        children: [
          Text(
            _registerMode ? 'Create account' : 'Sign in',
            style: GoogleFonts.cinzel(
              color: AppColors.gold,
              fontSize: 22,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            _registerMode
                ? 'Register to sync tickets and donations.'
                : 'Sign in to view your tickets and donations.',
            style: TextStyle(color: colors.textMuted),
          ),
          const SizedBox(height: 18),
          if (_registerMode) ...[
            TextField(
              controller: nameController,
              textCapitalization: TextCapitalization.words,
              style: TextStyle(color: colors.textPrimary),
              decoration: const InputDecoration(
                labelText: 'Full name',
                prefixIcon: Icon(Icons.person_outline, color: AppColors.gold),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: phoneController,
              keyboardType: TextInputType.phone,
              style: TextStyle(color: colors.textPrimary),
              decoration: const InputDecoration(
                labelText: 'Phone (optional)',
                prefixIcon: Icon(Icons.phone_outlined, color: AppColors.gold),
              ),
            ),
            const SizedBox(height: 12),
          ],
          TextField(
            controller: emailController,
            keyboardType: TextInputType.emailAddress,
            style: TextStyle(color: colors.textPrimary),
            decoration: const InputDecoration(
              labelText: 'Email',
              prefixIcon: Icon(Icons.mail_outline, color: AppColors.gold),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: passwordController,
            obscureText: true,
            style: TextStyle(color: colors.textPrimary),
            decoration: const InputDecoration(
              labelText: 'Password',
              prefixIcon: Icon(Icons.lock_outline, color: AppColors.gold),
            ),
          ),
          if (_registerMode) ...[
            const SizedBox(height: 12),
            TextField(
              controller: confirmController,
              obscureText: true,
              style: TextStyle(color: colors.textPrimary),
              decoration: const InputDecoration(
                labelText: 'Confirm password',
                prefixIcon: Icon(Icons.lock_outline, color: AppColors.gold),
              ),
            ),
          ],
          const SizedBox(height: 22),
          AppButton(
            label: _busy
                ? 'Please wait…'
                : (_registerMode ? 'Register' : 'Sign in'),
            variant: AppButtonVariant.green,
            onPressed: _busy ? null : _submitAuth,
          ),
          const SizedBox(height: 12),
          AppButton(
            label: _registerMode
                ? 'Have an account? Sign in'
                : 'Need an account? Register',
            variant: AppButtonVariant.outline,
            onPressed: _busy
                ? null
                : () => setState(() => _registerMode = !_registerMode),
          ),
          const SizedBox(height: 20),
          ListenableBuilder(
            listenable: themeController,
            builder: (context, _) => _themeCard(context),
          ),
        ],
      ),
    );
  }

  Widget _buildLoggedIn(BuildContext context) {
    final colors = AppColors.of(context);
    final name = authSession.displayName;
    final email = authSession.email ?? '';
    final phone = authSession.phone;
    final avatarUrl = authSession.avatarUrl;

    final links = [
      (Icons.edit_outlined, 'Edit Profile', 'edit'),
      (Icons.confirmation_number_outlined, 'My Tickets', 'tickets'),
      (Icons.volunteer_activism_outlined, 'My Donations', 'donations'),
      (Icons.logout, 'Log Out', 'logout'),
    ];

    return Scaffold(
      appBar: const AppPageBar(title: 'Profile'),
      body: PageSkeletonGate(
        skeleton: ScreenSkeletons.listCards(count: 7),
        child: RefreshIndicator(
          color: AppColors.gold,
          onRefresh: _loadDonations,
          child: ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
            children: [
              Center(
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(3),
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: AppColors.gold, width: 2),
                      ),
                      child: CircleAvatar(
                        radius: 48,
                        backgroundColor: colors.card,
                        backgroundImage: avatarUrl != null
                            ? NetworkImage(avatarUrl)
                            : const NetworkImage(AppImages.profileAvatar),
                      ),
                    ),
                    const SizedBox(height: 14),
                    Text(
                      name,
                      style: GoogleFonts.cinzel(
                        color: colors.textPrimary,
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    if (email.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        email,
                        style: TextStyle(color: colors.textMuted),
                      ),
                    ],
                    if (phone != null && phone.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Text(
                        phone,
                        style: TextStyle(color: colors.textMuted),
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 20),
              ListenableBuilder(
                listenable: themeController,
                builder: (context, _) => _themeCard(context),
              ),
              const SizedBox(height: 16),
              ...links.map(
                (item) => Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: AppCard(
                    onTap: () {
                      if (item.$3 == 'edit') {
                        AppNav.push(context, const EditProfileScreen());
                        return;
                      }
                      if (item.$3 == 'tickets') {
                        AppNav.push(context, const MyTicketsScreen());
                        return;
                      }
                      if (item.$3 == 'donations') {
                        // Scroll section is below; also refresh.
                        _loadDonations();
                        return;
                      }
                      if (item.$3 == 'logout') {
                        _logout();
                        return;
                      }
                    },
                    child: Row(
                      children: [
                        Icon(
                          item.$1,
                          color: item.$3 == 'logout'
                              ? AppColors.danger
                              : AppColors.gold,
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Text(
                            item.$2,
                            style: TextStyle(
                              color: item.$3 == 'logout'
                                  ? AppColors.danger
                                  : colors.textPrimary,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                        Icon(Icons.chevron_right, color: colors.textMuted),
                      ],
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 10),
              Text(
                'My Donations',
                style: GoogleFonts.cinzel(
                  color: colors.textPrimary,
                  fontWeight: FontWeight.w700,
                  fontSize: 16,
                ),
              ),
              const SizedBox(height: 10),
              if (_donationsLoading)
                const SkeletonBox(height: 88)
              else if (_donationsError != null)
                AppCard(
                  child: Text(
                    _donationsError!,
                    style: TextStyle(color: colors.textMuted),
                  ),
                )
              else if (_donations.isEmpty)
                AppCard(
                  child: Text(
                    'No donations yet.',
                    style: TextStyle(color: colors.textMuted),
                  ),
                )
              else
                ..._donations.map((item) {
                  final amount = (item['amount'] as num?)?.toInt() ?? 0;
                  final currency =
                      (item['currency'] as String?)?.trim().isNotEmpty == true
                          ? (item['currency'] as String)
                          : 'TZS';
                  final reference = item['reference'] as String? ?? '';
                  final paidAt = DateTime.tryParse(
                    item['paid_at'] as String? ?? '',
                  );
                  final amountLabel = currency.toUpperCase() == 'TZS'
                      ? formatTzs(amount)
                      : '$currency ${NumberFormat('#,###').format(amount)}';
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: AppCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            amountLabel,
                            style: GoogleFonts.dmSans(
                              color: AppColors.gold,
                              fontWeight: FontWeight.w700,
                              fontSize: 16,
                            ),
                          ),
                          if (reference.isNotEmpty) ...[
                            const SizedBox(height: 4),
                            Text(
                              reference,
                              style: TextStyle(
                                color: colors.textMuted,
                                fontSize: 12,
                              ),
                            ),
                          ],
                          if (paidAt != null) ...[
                            const SizedBox(height: 4),
                            Text(
                              DateFormat.yMMMd().format(paidAt.toLocal()),
                              style: TextStyle(
                                color: colors.textSecondary,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  );
                }),
            ],
          ),
        ),
      ),
    );
  }

  Widget _themeCard(BuildContext context) {
    final colors = AppColors.of(context);
    final isDark = themeController.isDark;
    return AppCard(
      child: Row(
        children: [
          Icon(
            isDark ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
            color: AppColors.gold,
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Appearance',
                  style: TextStyle(
                    color: colors.textPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  isDark ? 'Dark mode (design default)' : 'Light mode',
                  style: TextStyle(
                    color: colors.textMuted,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          Switch.adaptive(
            value: isDark,
            activeThumbColor: Colors.black,
            activeTrackColor: AppColors.gold,
            onChanged: (dark) {
              themeController.setMode(
                dark ? ThemeMode.dark : ThemeMode.light,
              );
            },
          ),
        ],
      ),
    );
  }
}
