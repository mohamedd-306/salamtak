import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:provider/provider.dart';
import '../../theme.dart';
import '../../l10n/app_localizations.dart';
import '../../services/database_service.dart';
import '../../providers/language_provider.dart';
import '../login_screen.dart';

class AdminProfileScreen extends StatefulWidget {
  const AdminProfileScreen({super.key});

  @override
  State<AdminProfileScreen> createState() => _AdminProfileScreenState();
}

class _AdminProfileScreenState extends State<AdminProfileScreen> {
  User? _currentUser;
  Map<String, dynamic>? _userData;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    setState(() => _isLoading = true);
    try {
      _currentUser = FirebaseAuth.instance.currentUser;
      if (_currentUser != null) {
        // Get user data from Firestore
        final userDoc =
            await FirebaseFirestore.instance
                .collection('users')
                .doc(_currentUser!.uid)
                .get();
        if (userDoc.exists) {
          _userData = userDoc.data();
        }
      }
    } catch (e) {
      print('Error loading user data: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _toggleLanguage() async {
    if (!mounted) return;

    final languageProvider = Provider.of<LanguageProvider>(
      context,
      listen: false,
    );
    final currentLocale = languageProvider.locale.languageCode;
    final newLocale = currentLocale == 'en' ? 'ar' : 'en';

    await languageProvider.setLocale(Locale(newLocale, ''));
  }

  Future<void> _signOut() async {
    final l10n = AppLocalizations.of(context);
    final confirmed = await showDialog<bool>(
      context: context,
      builder:
          (_) => AlertDialog(
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
            title: Text(
              l10n.signOut,
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
            content: Text(
              l10n.signOutConfirm,
              style: const TextStyle(color: AppTheme.textSecondary),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: Text(l10n.cancel),
              ),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.danger,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                child: Text(l10n.signOut),
              ),
            ],
          ),
    );

    if (confirmed == true && mounted) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.clear();
      await DatabaseService.instance.signOut();
      if (mounted) {
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (_) => const LoginScreen()),
          (_) => false,
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final languageProvider = Provider.of<LanguageProvider>(context);

    return Scaffold(
      backgroundColor: AppTheme.surface,
      body:
          _isLoading
              ? const Center(
                child: CircularProgressIndicator(color: AppTheme.primary),
              )
              : CustomScrollView(
                slivers: [
                  // Header
                  SliverAppBar(
                    expandedHeight: 200,
                    pinned: true,
                    backgroundColor: AppTheme.primaryDark,
                    flexibleSpace: FlexibleSpaceBar(
                      background: Container(
                        decoration: const BoxDecoration(
                          gradient: AppTheme.headerGradient,
                        ),
                        child: SafeArea(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const SizedBox(height: 40),
                              // Avatar
                              Container(
                                width: 80,
                                height: 80,
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withValues(
                                        alpha: 0.2,
                                      ),
                                      blurRadius: 10,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                child: const Icon(
                                  Icons.admin_panel_settings_rounded,
                                  size: 40,
                                  color: AppTheme.primary,
                                ),
                              ),
                              const SizedBox(height: 12),
                              // Name
                              Text(
                                _userData?['name'] ??
                                    _currentUser?.displayName ??
                                    'Admin',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 20,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 4),
                              // Email
                              Text(
                                _currentUser?.email ?? '',
                                style: TextStyle(
                                  color: Colors.white.withValues(alpha: 0.9),
                                  fontSize: 14,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),

                  // Content
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Actions Section
                          Text(
                            l10n.settings,
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.textPrimary,
                            ),
                          ),
                          const SizedBox(height: 12),

                          // Language Toggle Button
                          _ActionButton(
                            icon: Icons.language_rounded,
                            label:
                                languageProvider.isEnglish
                                    ? 'Switch to Arabic'
                                    : 'التبديل إلى الإنجليزية',
                            color: AppTheme.primary,
                            onTap: _toggleLanguage,
                          ),

                          const SizedBox(height: 8),

                          // Sign Out Button
                          _ActionButton(
                            icon: Icons.logout_rounded,
                            label: l10n.signOut,
                            color: AppTheme.danger,
                            onTap: _signOut,
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
    );
  }
}

class _ActionButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _ActionButton({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Row(
          children: [
            Icon(icon, size: 24, color: color),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                label,
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: color,
                ),
              ),
            ),
            Icon(Icons.arrow_forward_ios_rounded, size: 16, color: color),
          ],
        ),
      ),
    );
  }
}
