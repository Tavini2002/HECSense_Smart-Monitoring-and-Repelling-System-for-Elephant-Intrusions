import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config.dart';
import 'profile_info.dart';
import 'change_password.dart';
import '../main.dart';

/// App theme color (HEC-Sense Branding)
const Color appPrimaryShade = Color(0xFF129166);

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({Key? key}) : super(key: key);

  @override
  State<DashboardScreen> createState() => _DashboardController();
}

class _DashboardController extends State<DashboardScreen> {
  final FlutterSecureStorage _secureStore = const FlutterSecureStorage();
  int _activeTab = 0;

  static const List<String> _pageHeadings = [
    "HEC-Sense Dashboard",
    "Detections",
    "Alerts",
    "Datasets",
  ];

  /// Placeholder screens for not-yet-implemented pages
  static const List<Widget> _modules = [
    PlaceholderPanel(
      key: ValueKey("panel-dashboard"),
      iconData: Icons.dashboard_outlined,
      heading: "Dashboard coming soon",
      caption:
      "Live metrics, heatmaps, camera activity, and overall system health will display here.",
    ),
    PlaceholderPanel(
      key: ValueKey("panel-detections"),
      iconData: Icons.visibility_outlined,
      heading: "Detections coming soon",
      caption:
      "A list of elephant detections captured from field cameras & IoT sensors will appear here.",
    ),
    PlaceholderPanel(
      key: ValueKey("panel-alerts"),
      iconData: Icons.notifications_active_outlined,
      heading: "Alerts coming soon",
      caption:
      "Alert logs, SMS/email notifications, and early-warning configurations will be shown here.",
    ),
    PlaceholderPanel(
      key: ValueKey("panel-datasets"),
      iconData: Icons.folder_open_outlined,
      heading: "Datasets coming soon",
      caption:
      "Model data, open datasets, annotation files, and downloadable resources will be placed here.",
    ),
  ];

  /// Clear secure storage & navigate to main page
  Future<void> _handleLogout() async {
    await _secureStore.deleteAll();
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const MyApp()),
          (_) => false,
    );
  }

  /// Handle popup menu actions
  void _onProfileMenuTap(String option) {
    switch (option) {
      case "Profile Info":
        Navigator.push(
            context, MaterialPageRoute(builder: (_) => const ProfileInfoScreen()));
        break;

      case "Change Password":
        Navigator.push(
            context, MaterialPageRoute(builder: (_) => const ChangePasswordScreen()));
        break;

      case "Logout":
        _handleLogout();
        break;
    }
  }

  /// Change tab
  void _switchTab(int index) {
    setState(() => _activeTab = index);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          _pageHeadings[_activeTab],
          style: const TextStyle(color: Colors.white),
        ),
        backgroundColor: appPrimaryShade,
        automaticallyImplyLeading: false,
        actions: [
          PopupMenuButton<String>(
            onSelected: _onProfileMenuTap,
            icon: const Icon(Icons.person, color: Colors.white),
            itemBuilder: (_) => const [
              PopupMenuItem(value: "Profile Info", child: Text("Profile Info")),
              PopupMenuItem(value: "Change Password", child: Text("Change Password")),
              PopupMenuItem(value: "Logout", child: Text("Logout")),
            ],
          )
        ],
      ),

      body: _modules[_activeTab],

      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _activeTab,
        onTap: _switchTab,
        backgroundColor: appPrimaryShade,
        type: BottomNavigationBarType.fixed,
        selectedItemColor: Colors.white,
        unselectedItemColor: Colors.white70,
        items: const [
          BottomNavigationBarItem(
              icon: Icon(Icons.dashboard_outlined), label: "Dashboard"),
          BottomNavigationBarItem(
              icon: Icon(Icons.visibility_outlined), label: "Detections"),
          BottomNavigationBarItem(
              icon: Icon(Icons.notifications_active_outlined), label: "Alerts"),
          BottomNavigationBarItem(
              icon: Icon(Icons.folder_open_outlined), label: "Datasets"),
        ],
      ),
    );
  }
}

/// Generic placeholder widget for not-yet-developed screens
class PlaceholderPanel extends StatelessWidget {
  final String heading;
  final String caption;
  final IconData iconData;

  const PlaceholderPanel({
    Key? key,
    required this.heading,
    required this.caption,
    required this.iconData,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final themeText = Theme.of(context).textTheme;

    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 26),
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 520),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(iconData, size: 80, color: appPrimaryShade),
              const SizedBox(height: 18),
              Text(
                heading,
                textAlign: TextAlign.center,
                style: themeText.headlineSmall?.copyWith(
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 10),
              Text(
                caption,
                textAlign: TextAlign.center,
                style: themeText.bodyMedium,
              ),
              const SizedBox(height: 25),
              OutlinedButton.icon(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text("Template button tapped")),
                  );
                },
                icon: const Icon(Icons.construction),
                label: const Text("I’ll customize this later"),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
