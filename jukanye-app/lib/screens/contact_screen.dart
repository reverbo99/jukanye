import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../models/festival_settings.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/app_page_bar.dart';
import '../widgets/async_body.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';

class ContactScreen extends StatefulWidget {
  const ContactScreen({super.key});

  @override
  State<ContactScreen> createState() => _ContactScreenState();
}

class _ContactScreenState extends State<ContactScreen> {
  FestivalSettings? _settings;
  bool _loading = true;
  String? _error;
  bool _submitting = false;

  final nameController = TextEditingController();
  final emailController = TextEditingController();
  final messageController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    nameController.dispose();
    emailController.dispose();
    messageController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final settings = await JukanyeApi.instance.fetchSettings();
      if (!mounted) return;
      setState(() {
        _settings = settings;
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

  Future<void> _copy(String label, String value) async {
    await Clipboard.setData(ClipboardData(text: value));
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('$label copied')),
    );
  }

  Future<void> _submitContact() async {
    final name = nameController.text.trim();
    final email = emailController.text.trim();
    final message = messageController.text.trim();

    if (name.length < 2) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Enter your name')),
      );
      return;
    }
    if (!email.contains('@')) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Enter a valid email')),
      );
      return;
    }
    if (message.length < 5) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Enter a short message')),
      );
      return;
    }

    setState(() => _submitting = true);
    try {
      await JukanyeApi.instance.submitForm(
        form: 'contact',
        name: name,
        email: email,
        message: message,
      );
      if (!mounted) return;
      nameController.clear();
      emailController.clear();
      messageController.clear();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Message sent. Thank you!')),
      );
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.message)),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Unable to send message')),
      );
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final settings = _settings;

    final contactRows = <(String, String, IconData)>[];
    final socialRows = <(String, String, IconData)>[];

    if (settings != null) {
      final email = settings.contactField('email');
      final phone = settings.contactField('phone');
      final address = settings.contactField('address');
      if (email != null) {
        contactRows.add(('Email', email, Icons.mail_outline));
      }
      if (phone != null) {
        contactRows.add(('Phone', phone, Icons.phone_outlined));
      }
      if (address != null) {
        contactRows.add(('Address', address, Icons.place_outlined));
      }

      for (final entry in [
        ('Facebook', 'facebook', Icons.facebook),
        ('Instagram', 'instagram', Icons.camera_alt_outlined),
        ('Twitter / X', 'twitter', Icons.alternate_email),
        ('YouTube', 'youtube', Icons.play_circle_outline),
      ]) {
        final url = settings.socialField(entry.$2);
        if (url != null) {
          socialRows.add((entry.$1, url, entry.$3));
        }
      }
    }

    return Scaffold(
      appBar: const AppPageBar(title: 'Contact'),
      body: RefreshIndicator(
        color: AppColors.gold,
        onRefresh: _load,
        child: AsyncBody<Object>(
          loading: _loading,
          error: null,
          items: _loading ? const [] : const [Object()],
          skeleton: ScreenSkeletons.listCards(count: 3),
          onRetry: _load,
          emptyMessage: 'No contact details yet',
          emptyIcon: Icons.mail_outline,
          builder: (context, _) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
              children: [
                Text(
                  'Get in touch',
                  style: GoogleFonts.cinzel(
                    color: AppColors.gold,
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Festival contact and social channels.',
                  style: TextStyle(color: colors.textMuted),
                ),
                if (_error != null) ...[
                  const SizedBox(height: 10),
                  Text(
                    'Contact details unavailable: $_error',
                    style: GoogleFonts.dmSans(
                      color: colors.textMuted,
                      fontSize: 12,
                    ),
                  ),
                ],
                const SizedBox(height: 18),
                Text(
                  'Send a message',
                  style: GoogleFonts.dmSans(
                    color: colors.textPrimary,
                    fontWeight: FontWeight.w700,
                    fontSize: 15,
                  ),
                ),
                const SizedBox(height: 10),
                AppCard(
                  child: Column(
                    children: [
                      TextField(
                        controller: nameController,
                        textCapitalization: TextCapitalization.words,
                        style: TextStyle(color: colors.textPrimary),
                        decoration: const InputDecoration(
                          labelText: 'Name',
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: emailController,
                        keyboardType: TextInputType.emailAddress,
                        style: TextStyle(color: colors.textPrimary),
                        decoration: const InputDecoration(
                          labelText: 'Email',
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: messageController,
                        maxLines: 4,
                        style: TextStyle(color: colors.textPrimary),
                        decoration: const InputDecoration(
                          labelText: 'Message',
                          alignLabelWithHint: true,
                        ),
                      ),
                      const SizedBox(height: 16),
                      AppButton(
                        label: _submitting ? 'Sending…' : 'Send message',
                        variant: AppButtonVariant.green,
                        onPressed: _submitting ? null : _submitContact,
                      ),
                    ],
                  ),
                ),
                if (contactRows.isNotEmpty) ...[
                  const SizedBox(height: 18),
                  Text(
                    'Contact',
                    style: GoogleFonts.dmSans(
                      color: colors.textPrimary,
                      fontWeight: FontWeight.w700,
                      fontSize: 15,
                    ),
                  ),
                  const SizedBox(height: 10),
                  ...contactRows.map(
                    (row) => Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: AppCard(
                        onTap: () => _copy(row.$1, row.$2),
                        child: Row(
                          children: [
                            Container(
                              width: 42,
                              height: 42,
                              decoration: BoxDecoration(
                                color: colors.surfaceElevated,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Icon(row.$3, color: AppColors.gold),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    row.$1,
                                    style: GoogleFonts.dmSans(
                                      color: colors.textMuted,
                                      fontSize: 12,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    row.$2,
                                    style: GoogleFonts.dmSans(
                                      color: colors.textPrimary,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
                if (socialRows.isNotEmpty) ...[
                  const SizedBox(height: 10),
                  Text(
                    'Social',
                    style: GoogleFonts.dmSans(
                      color: colors.textPrimary,
                      fontWeight: FontWeight.w700,
                      fontSize: 15,
                    ),
                  ),
                  const SizedBox(height: 10),
                  ...socialRows.map(
                    (row) => Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: AppCard(
                        onTap: () => _copy(row.$1, row.$2),
                        child: Row(
                          children: [
                            Container(
                              width: 42,
                              height: 42,
                              decoration: BoxDecoration(
                                color: colors.surfaceElevated,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Icon(row.$3, color: AppColors.gold),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    row.$1,
                                    style: GoogleFonts.dmSans(
                                      color: colors.textMuted,
                                      fontSize: 12,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    row.$2,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: GoogleFonts.dmSans(
                                      color: colors.textPrimary,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            Icon(Icons.copy, size: 16, color: colors.textMuted),
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
    );
  }
}
