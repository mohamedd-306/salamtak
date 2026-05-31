import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../theme.dart';
import '../../l10n/app_localizations.dart';
import 'admin_home_screen.dart';
import 'orders_management_screen.dart';
import 'product_management_screen.dart';
import 'admin_profile_screen.dart';

class AdminNavigation extends StatefulWidget {
  const AdminNavigation({super.key});

  @override
  State<AdminNavigation> createState() => _AdminNavigationState();
}

class _AdminNavigationState extends State<AdminNavigation> {
  int _currentIndex = 0;
  String _userType = 'product_manager'; // Default to product_manager

  late List<Widget> _screens;
  late List<BottomNavigationBarItem> _navItems;

  @override
  void initState() {
    super.initState();
    _loadUserType();
  }

  Future<void> _loadUserType() async {
    final prefs = await SharedPreferences.getInstance();
    final userType = prefs.getString('userType') ?? 'product_manager';
    setState(() {
      _userType = userType;
      _buildNavigationItems();
    });
  }

  void _buildNavigationItems() {
    final l10n = AppLocalizations.of(context);

    if (_userType == 'moderator') {
      // Moderator: Only Reports and Profile
      _screens = [
        const AdminHomeScreen(),
        const AdminProfileScreen(),
      ];
      _navItems = [
        BottomNavigationBarItem(
          icon: const Icon(Icons.report_outlined),
          activeIcon: const Icon(Icons.report),
          label: l10n.reports,
        ),
        BottomNavigationBarItem(
          icon: const Icon(Icons.person_outline),
          activeIcon: const Icon(Icons.person),
          label: l10n.profile,
        ),
      ];
    } else {
      // Product Manager: Orders, Products, and Profile
      _screens = [
        const OrdersManagementScreen(),
        const ProductManagementScreen(),
        const AdminProfileScreen(),
      ];
      _navItems = [
        BottomNavigationBarItem(
          icon: const Icon(Icons.shopping_bag_outlined),
          activeIcon: const Icon(Icons.shopping_bag),
          label: l10n.orders,
        ),
        BottomNavigationBarItem(
          icon: const Icon(Icons.inventory_outlined),
          activeIcon: const Icon(Icons.inventory),
          label: l10n.products,
        ),
        BottomNavigationBarItem(
          icon: const Icon(Icons.person_outline),
          activeIcon: const Icon(Icons.person),
          label: l10n.profile,
        ),
      ];
    }
  }

  @override
  Widget build(BuildContext context) {
    // Rebuild navigation items if context changes (for localization)
    _buildNavigationItems();

    return Scaffold(
      body: IndexedStack(index: _currentIndex, children: _screens),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.1),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) {
            setState(() {
              _currentIndex = index;
            });
          },
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          selectedItemColor: AppTheme.primary,
          unselectedItemColor: AppTheme.textSecondary,
          selectedFontSize: 12,
          unselectedFontSize: 11,
          selectedLabelStyle: const TextStyle(fontWeight: FontWeight.w600),
          elevation: 0,
          items: _navItems,
        ),
      ),
    );
  }
}
