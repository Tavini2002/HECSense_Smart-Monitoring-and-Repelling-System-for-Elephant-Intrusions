import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:provider/provider.dart';
import 'package:HECSense/gs-screens/get_started_screen.dart';
import 'package:HECSense/dashboard/dashboard_screen.dart';
import 'package:HECSense/services/locale_service.dart';
import 'package:HECSense/screens/language_selection_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({super.key});

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  final _storage = const FlutterSecureStorage();
  final LocaleService _localeService = LocaleService();

  // Function to check login status
  Future<bool> _isLoggedIn() async {
    String? loggedIn = await _storage.read(key: 'isLoggedIn');
    return loggedIn == 'true';
  }

  // Function to check if language is selected
  Future<bool> _isLanguageSelected() async {
    String? language = await _storage.read(key: 'selected_language');
    return language != null;
  }

  @override
  void initState() {
    super.initState();
    _localeService.addListener(() {
      setState(() {});
    });
  }

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider<LocaleService>.value(
      value: _localeService,
      child: Consumer<LocaleService>(
        builder: (context, localeService, child) {
          return FutureBuilder<Map<String, bool>>(
            future: Future.wait([
              _isLoggedIn(),
              _isLanguageSelected(),
            ]).then((results) => {
              'isLoggedIn': results[0],
              'isLanguageSelected': results[1],
            }),
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return MaterialApp(
                  locale: localeService.locale,
                  home: const Scaffold(
                    body: Center(child: CircularProgressIndicator()),
                  ),
                );
              }

              final isLoggedIn = snapshot.data?['isLoggedIn'] ?? false;
              final isLanguageSelected = snapshot.data?['isLanguageSelected'] ?? false;

              // Show language selection if not selected
              Widget homeWidget;
              if (!isLanguageSelected) {
                homeWidget = const LanguageSelectionScreen(isFirstTime: true);
              } else if (isLoggedIn) {
                homeWidget = const DashboardScreen();
              } else {
                homeWidget = const GetStartedScreen();
              }

              return MaterialApp(
                title: 'HEC-Sense Elephant Detection',
                locale: localeService.locale,
                supportedLocales: const [
                  Locale('en', ''),
                  Locale('si', ''),
                  Locale('ta', ''),
                ],
                localizationsDelegates: const [
                  GlobalMaterialLocalizations.delegate,
                  GlobalWidgetsLocalizations.delegate,
                  GlobalCupertinoLocalizations.delegate,
                ],
                theme: ThemeData(
                  colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepPurple),
                  useMaterial3: true,
                ),
                home: homeWidget,
              );
            },
          );
        },
      ),
    );
  }
}
