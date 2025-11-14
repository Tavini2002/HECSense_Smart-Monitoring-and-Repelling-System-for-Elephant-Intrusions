import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config.dart'; // kept in case you need it soon
import 'profile_info.dart';
import 'change_password.dart';
import '../main.dart'; // for navigation after logout

const Color customOrange = Color(0xFF129166); // HEC-Sense brand color

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({Key? key}) : super(key: key);

  @override
  _DashboardScreenState createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final _storage = const FlutterSecureStorage();
  int _selectedIndex = 0;

  static const List<String> _screenTitles = [
    'HEC-Sense Dashboard',
    'Detections',
    'Alerts',
    'Datasets',
  ];

  // Template placeholders (unique keys avoid hot-reload state issues)
  static final List<Widget> _screens = const [
    _ComingSoonScreen(
      key: ValueKey('tab-dashboard'),
      icon: Icons.dashboard_outlined,
      title: 'Dashboard coming soon',
      subtitle:
      'Live stats, hotspot maps, camera status, and system health will appear here.',
    ),
    _ComingSoonScreen(
      key: ValueKey('tab-detections'),
      icon: Icons.visibility_outlined,
      title: 'Detections coming soon',
      subtitle:
      'Recent elephant detections from cameras & sensors will be listed here.',
    ),
    _ComingSoonScreen(
      key: ValueKey('tab-alerts'),
      icon: Icons.notifications_active_outlined,
      title: 'Alerts coming soon',
      subtitle:
      'Early-warning notifications, SMS/email history, and alert rules will go here.',
    ),
    _ComingSoonScreen(
      key: ValueKey('tab-datasets'),
      icon: Icons.folder_open_outlined,
      title: 'Datasets coming soon',
      subtitle:
      'Open datasets, model cards, and downloads (images, annotations, models).',
    ),
  ];

  Future<void> _logout(BuildContext context) async {
    await _storage.deleteAll();
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (context) => const MyApp()),
          (route) => false,
    );
  }

  void _handleMenuSelection(String choice) {
    switch (choice) {
      case 'Profile Info':
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const ProfileInfoScreen()),
        );
        break;
      case 'Change Password':
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const ChangePasswordScreen()),
        );
        break;
      case 'Logout':
        _logout(context);
        break;
    }
  }

  void _onTabSelected(int index) {
    setState(() => _selectedIndex = index);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          _screenTitles[_selectedIndex],
          style: const TextStyle(color: Colors.white),
        ),
        backgroundColor: customOrange,
        automaticallyImplyLeading: false,
        actions: [
          PopupMenuButton<String>(
            onSelected: _handleMenuSelection,
            icon: const Icon(Icons.person, color: Colors.white),
            itemBuilder: (BuildContext context) => const [
              PopupMenuItem<String>(value: 'Profile Info', child: Text('Profile Info')),
              PopupMenuItem<String>(value: 'Change Password', child: Text('Change Password')),
              PopupMenuItem<String>(value: 'Logout', child: Text('Logout')),
            ],
          ),
        ],
      ),
      body: _screens[_selectedIndex],
      bottomNavigationBar: BottomNavigationBar(
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.dashboard_outlined), label: 'Dashboard'),
          BottomNavigationBarItem(icon: Icon(Icons.visibility_outlined), label: 'Detections'),
          BottomNavigationBarItem(icon: Icon(Icons.notifications_active_outlined), label: 'Alerts'),
          BottomNavigationBarItem(icon: Icon(Icons.folder_open_outlined), label: 'Datasets'),
        ],
        currentIndex: _selectedIndex,
        selectedItemColor: Colors.white,
        unselectedItemColor: Colors.white70,
        backgroundColor: customOrange,
        onTap: _onTabSelected,
        type: BottomNavigationBarType.fixed,
      ),
    );
  }
}

/// Simple reusable “Coming soon” placeholder for HEC-Sense
class _ComingSoonScreen extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;

  const _ComingSoonScreen({
    Key? key,
    required this.title,
    required this.subtitle,
    required this.icon,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final t = Theme.of(context).textTheme;
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24.0),
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 520),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 80, color: customOrange),
              const SizedBox(height: 16),
              Text(title, textAlign: TextAlign.center,
                  style: t.headlineSmall?.copyWith(fontWeight: FontWeight.w700)),
              const SizedBox(height: 10),
              Text(subtitle, textAlign: TextAlign.center, style: t.bodyMedium),
              const SizedBox(height: 24),
              OutlinedButton.icon(
                onPressed: () => ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Template button tapped')),
                ),
                icon: const Icon(Icons.construction),
                label: const Text('I’ll customize this later'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
