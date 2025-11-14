import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:HECSense/gs-screens/get_started_screen.dart';
import 'package:HECSense/dashboard/dashboard_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  // Create a storage instance
  final _storage = const FlutterSecureStorage();

  // Function to check login status
  Future<bool> _isLoggedIn() async {
    String? loggedIn = await _storage.read(key: 'isLoggedIn');
    return loggedIn == 'true';
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<bool>(
      future: _isLoggedIn(),
      builder: (context, snapshot) {
        // Display a loading indicator while checking login status
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const MaterialApp(
            home: Scaffold(
              body: Center(child: CircularProgressIndicator()),
            ),
          );
        }

        // Once done, navigate to Dashboard if logged in, else Get Started screen
        bool isLoggedIn = snapshot.data ?? false;
        return MaterialApp(
          title: 'Organ Donation App',
          theme: ThemeData(
            colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepPurple),
            useMaterial3: true,
          ),
          home: isLoggedIn ? const DashboardScreen() : const GetStartedScreen(),
        );
      },
    );
  }
}
