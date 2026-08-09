import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../main.dart';
import '../navigation/app_page_route.dart';
import '../screens/thank_you_screen.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';
import '../widgets/app_page_bar.dart';

class PaymentScreen extends StatefulWidget {
  const PaymentScreen({
    super.key,
    required this.amount,
    required this.method,
    required this.purpose,
    this.ticketTierId,
  });

  final int amount;
  final String method;
  final String purpose;
  final int? ticketTierId;

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  late final TextEditingController nameController;
  late final TextEditingController emailController;
  late final TextEditingController phoneController;

  bool loading = false;
  String? _pendingReference;
  String? _statusMessage;

  bool get _isTicket {
    if (widget.ticketTierId != null) return true;
    return widget.purpose.toLowerCase().contains('ticket');
  }

  @override
  void initState() {
    super.initState();
    final user = authSession.user;
    nameController = TextEditingController(
      text: (user?['name'] as String?)?.trim() ?? '',
    );
    emailController = TextEditingController(
      text: (user?['email'] as String?)?.trim() ?? '',
    );
    phoneController = TextEditingController(
      text: (user?['phone'] as String?)?.trim().isNotEmpty == true
          ? (user!['phone'] as String).trim()
          : '2557',
    );
  }

  @override
  void dispose() {
    nameController.dispose();
    emailController.dispose();
    phoneController.dispose();
    super.dispose();
  }

  Future<void> _pay() async {
    final name = nameController.text.trim();
    final email = emailController.text.trim();
    final phone = phoneController.text.trim();

    if (name.length < 2) {
      _snack('Enter your full name');
      return;
    }
    if (!email.contains('@')) {
      _snack('Enter a valid email');
      return;
    }
    if (phone.length < 10) {
      _snack('Enter a valid phone number');
      return;
    }
    if (_isTicket && widget.ticketTierId == null) {
      _snack('Missing ticket selection. Go back and choose a ticket.');
      return;
    }

    setState(() {
      loading = true;
      _statusMessage = 'Starting payment…';
      _pendingReference = null;
    });

    try {
      final data = await JukanyeApi.instance.initiatePayment(
        type: _isTicket ? 'ticket' : 'donation',
        amount: _isTicket ? null : widget.amount,
        ticketTierId: widget.ticketTierId,
        customerName: name,
        customerEmail: email,
        customerPhone: phone,
        method: widget.method,
      );

      final reference = data['reference'] as String? ?? '';
      final paymentLink = data['payment_link'] as String?;
      final status = (data['status'] as String?)?.toLowerCase() ?? '';
      final amount = (data['amount'] as num?)?.toInt() ?? widget.amount;

      if (reference.isEmpty) {
        throw const ApiException('Payment reference missing');
      }

      if (!mounted) return;
      setState(() => _pendingReference = reference);

      if (status == 'paid') {
        _goThankYou(reference: reference, amount: amount);
        return;
      }

      if (paymentLink != null && paymentLink.trim().isNotEmpty) {
        final uri = Uri.tryParse(paymentLink.trim());
        if (uri != null) {
          setState(() => _statusMessage = 'Opening payment page…');
          await launchUrl(uri, mode: LaunchMode.externalApplication);
        }
      }

      if (!mounted) return;
      setState(() => _statusMessage = 'Waiting for payment confirmation…');
      final paid = await _pollPayment(reference);
      if (!mounted) return;

      if (paid) {
        _goThankYou(reference: reference, amount: amount);
        return;
      }

      setState(() {
        loading = false;
        _statusMessage =
            'Complete payment in the browser, then tap “I’ve paid”.';
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        loading = false;
        _statusMessage = null;
      });
      if (e.requiresConfiguration || e.statusCode == 422) {
        _snack(
          e.requiresConfiguration
              ? 'Flutterwave is not configured on the server. Ask an admin to add payment keys.'
              : e.message,
        );
      } else {
        _snack(e.message);
      }
    } catch (_) {
      if (!mounted) return;
      setState(() {
        loading = false;
        _statusMessage = null;
      });
      _snack('Unable to start payment');
    }
  }

  Future<void> _ivePaid() async {
    final reference = _pendingReference;
    if (reference == null || reference.isEmpty) {
      _snack('Start payment first');
      return;
    }

    setState(() {
      loading = true;
      _statusMessage = 'Checking payment…';
    });

    try {
      var order = await JukanyeApi.instance.verifyPayment(reference: reference);
      var status = (order['status'] as String?)?.toLowerCase() ?? '';
      if (status != 'paid') {
        order = await JukanyeApi.instance.fetchPayment(reference);
        status = (order['status'] as String?)?.toLowerCase() ?? '';
      }

      if (!mounted) return;
      if (status == 'paid') {
        final amount = (order['amount'] as num?)?.toInt() ?? widget.amount;
        _goThankYou(reference: reference, amount: amount);
        return;
      }

      setState(() {
        loading = false;
        _statusMessage =
            'Payment is still pending. Finish checkout, then try again.';
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        loading = false;
        _statusMessage = null;
      });
      _snack(e.message);
    } catch (_) {
      if (!mounted) return;
      setState(() {
        loading = false;
        _statusMessage = null;
      });
      _snack('Unable to verify payment');
    }
  }

  Future<bool> _pollPayment(String reference) async {
    for (var i = 0; i < 5; i++) {
      await Future<void>.delayed(Duration(seconds: i == 0 ? 2 : 3));
      if (!mounted) return false;
      try {
        final order = await JukanyeApi.instance.fetchPayment(reference);
        final status = (order['status'] as String?)?.toLowerCase() ?? '';
        if (status == 'paid') return true;
      } catch (_) {
        // keep polling
      }
    }
    return false;
  }

  void _goThankYou({required String reference, required int amount}) {
    AppNav.pushAndRemoveUntil(
      context,
      ThankYouScreen(
        amount: amount,
        reference: reference,
        purpose: widget.purpose,
      ),
    );
  }

  void _snack(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final showPaidButton =
        _pendingReference != null && _pendingReference!.isNotEmpty;

    return Scaffold(
      appBar: const AppPageBar(title: 'Payment'),
      body: AnimatedSwitcher(
        duration: const Duration(milliseconds: 280),
        child: loading
            ? KeyedSubtree(
                key: const ValueKey('busy'),
                child: Column(
                  children: [
                    Expanded(child: ScreenSkeletons.paymentBusy()),
                    if (_statusMessage != null)
                      Padding(
                        padding: const EdgeInsets.fromLTRB(20, 0, 20, 28),
                        child: Text(
                          _statusMessage!,
                          textAlign: TextAlign.center,
                          style: GoogleFonts.dmSans(
                            color: colors.textMuted,
                            fontSize: 13,
                          ),
                        ),
                      ),
                  ],
                ),
              )
            : KeyedSubtree(
                key: const ValueKey('form'),
                child: ListView(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
                  children: [
                    AppCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            widget.purpose.toLowerCase().contains('donation')
                                ? 'You are donating'
                                : 'You are paying',
                            style: GoogleFonts.dmSans(color: colors.textMuted),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            formatTzs(widget.amount),
                            style: GoogleFonts.cinzel(
                              color: AppColors.gold,
                              fontSize: 28,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'via ${widget.method}',
                            style: TextStyle(color: colors.textSecondary),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 22),
                    Text(
                      'Your details',
                      style: GoogleFonts.cinzel(
                        color: colors.textPrimary,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 10),
                    TextField(
                      controller: nameController,
                      textCapitalization: TextCapitalization.words,
                      style: TextStyle(color: colors.textPrimary),
                      decoration: const InputDecoration(
                        hintText: 'Full name',
                        prefixIcon: Icon(Icons.person_outline, color: AppColors.gold),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: emailController,
                      keyboardType: TextInputType.emailAddress,
                      style: TextStyle(color: colors.textPrimary),
                      decoration: const InputDecoration(
                        hintText: 'Email',
                        prefixIcon: Icon(Icons.mail_outline, color: AppColors.gold),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: phoneController,
                      keyboardType: TextInputType.phone,
                      style: TextStyle(color: colors.textPrimary),
                      decoration: const InputDecoration(
                        hintText: '2557XXXXXXXX',
                        prefixIcon: Icon(Icons.phone, color: AppColors.gold),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'You will be redirected to complete payment securely.',
                      style: TextStyle(
                        color: colors.textMuted,
                        fontSize: 13,
                        height: 1.4,
                      ),
                    ),
                    if (_statusMessage != null) ...[
                      const SizedBox(height: 14),
                      Text(
                        _statusMessage!,
                        style: GoogleFonts.dmSans(
                          color: AppColors.goldLight,
                          fontSize: 13,
                          height: 1.4,
                        ),
                      ),
                    ],
                    const SizedBox(height: 28),
                    AppButton(
                      label: showPaidButton ? 'Pay again' : 'Pay Now',
                      variant: AppButtonVariant.green,
                      onPressed: _pay,
                    ),
                    if (showPaidButton) ...[
                      const SizedBox(height: 12),
                      AppButton(
                        label: "I've paid",
                        onPressed: _ivePaid,
                      ),
                    ],
                    const SizedBox(height: 12),
                    AppButton(
                      label: 'Cancel',
                      variant: AppButtonVariant.outline,
                      onPressed: () => Navigator.of(context).pop(),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}
