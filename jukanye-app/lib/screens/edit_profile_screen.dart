import 'dart:io';

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';

import '../api/api_exception.dart';
import '../api/jukanye_api.dart';
import '../main.dart';
import '../theme/app_colors.dart';
import '../widgets/app_button.dart';
import '../widgets/app_page_bar.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmController = TextEditingController();
  final _currentPasswordController = TextEditingController();

  bool _busy = false;
  String? _pickedImagePath;

  @override
  void initState() {
    super.initState();
    final user = authSession.user;
    _nameController.text = (user?['name'] as String?) ?? '';
    _emailController.text = (user?['email'] as String?) ?? '';
    _phoneController.text = (user?['phone'] as String?) ?? '';
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmController.dispose();
    _currentPasswordController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(
      source: ImageSource.gallery,
      maxWidth: 1200,
      imageQuality: 85,
    );
    if (picked == null || !mounted) return;
    setState(() => _pickedImagePath = picked.path);
  }

  Future<void> _save() async {
    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    if (name.length < 2) {
      _snack('Enter your name');
      return;
    }
    if (!email.contains('@')) {
      _snack('Enter a valid email');
      return;
    }

    final password = _passwordController.text;
    final confirm = _confirmController.text;
    if (password.isNotEmpty) {
      if (password.length < 6) {
        _snack('Password must be at least 6 characters');
        return;
      }
      if (password != confirm) {
        _snack('Passwords do not match');
        return;
      }
      if (_currentPasswordController.text.isEmpty) {
        _snack('Enter your current password to set a new one');
        return;
      }
    }

    setState(() => _busy = true);
    try {
      var user = await JukanyeApi.instance.updateProfile(
        name: name,
        email: email,
        phone: _phoneController.text.trim(),
        password: password.isNotEmpty ? password : null,
        passwordConfirmation: password.isNotEmpty ? confirm : null,
        currentPassword: password.isNotEmpty ? _currentPasswordController.text : null,
      );

      if (_pickedImagePath != null) {
        user = await JukanyeApi.instance.uploadAvatar(_pickedImagePath!);
      }

      await authSession.updateUser(user);
      if (!mounted) return;
      _snack('Profile updated');
      Navigator.of(context).pop();
    } on ApiException catch (e) {
      _snack(e.message);
    } catch (_) {
      _snack('Unable to update profile');
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  void _snack(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final avatarUrl = authSession.avatarUrl;

    ImageProvider? avatarImage;
    if (_pickedImagePath != null) {
      avatarImage = FileImage(File(_pickedImagePath!));
    } else if (avatarUrl != null) {
      avatarImage = NetworkImage(avatarUrl);
    }

    return Scaffold(
      appBar: const AppPageBar(title: 'Edit Profile'),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
        children: [
          Center(
            child: Column(
              children: [
                GestureDetector(
                  onTap: _busy ? null : _pickImage,
                  child: Stack(
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
                          backgroundImage: avatarImage,
                          child: avatarImage == null
                              ? Icon(Icons.person, size: 48, color: colors.textMuted)
                              : null,
                        ),
                      ),
                      Positioned(
                        right: 0,
                        bottom: 0,
                        child: Container(
                          padding: const EdgeInsets.all(6),
                          decoration: const BoxDecoration(
                            color: AppColors.gold,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.camera_alt, size: 18, color: Colors.black),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Tap to change photo',
                  style: TextStyle(color: colors.textMuted, fontSize: 12),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          TextField(
            controller: _nameController,
            textCapitalization: TextCapitalization.words,
            style: TextStyle(color: colors.textPrimary),
            decoration: const InputDecoration(
              labelText: 'Full name',
              prefixIcon: Icon(Icons.person_outline, color: AppColors.gold),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _emailController,
            keyboardType: TextInputType.emailAddress,
            style: TextStyle(color: colors.textPrimary),
            decoration: const InputDecoration(
              labelText: 'Email',
              prefixIcon: Icon(Icons.mail_outline, color: AppColors.gold),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _phoneController,
            keyboardType: TextInputType.phone,
            style: TextStyle(color: colors.textPrimary),
            decoration: const InputDecoration(
              labelText: 'Phone',
              prefixIcon: Icon(Icons.phone_outlined, color: AppColors.gold),
            ),
          ),
          const SizedBox(height: 24),
          Text(
            'Change password',
            style: GoogleFonts.cinzel(
              color: colors.textPrimary,
              fontWeight: FontWeight.w700,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _currentPasswordController,
            obscureText: true,
            style: TextStyle(color: colors.textPrimary),
            decoration: const InputDecoration(
              labelText: 'Current password',
              prefixIcon: Icon(Icons.lock_outline, color: AppColors.gold),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _passwordController,
            obscureText: true,
            style: TextStyle(color: colors.textPrimary),
            decoration: const InputDecoration(
              labelText: 'New password (optional)',
              prefixIcon: Icon(Icons.lock_outline, color: AppColors.gold),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _confirmController,
            obscureText: true,
            style: TextStyle(color: colors.textPrimary),
            decoration: const InputDecoration(
              labelText: 'Confirm new password',
              prefixIcon: Icon(Icons.lock_outline, color: AppColors.gold),
            ),
          ),
          const SizedBox(height: 24),
          AppButton(
            label: _busy ? 'Saving…' : 'Save changes',
            variant: AppButtonVariant.green,
            onPressed: _busy ? null : _save,
          ),
        ],
      ),
    );
  }
}
