import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/locale_service.dart';
import '../screens/language_selection_screen.dart';
import 'gs_elephant_detection_screen.dart';

class GetStartedScreen extends StatelessWidget {
  const GetStartedScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // Gradient background
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF129166), Color(0xFF7FD188)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: SafeArea(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // Back button and heading
              Column(
                children: [
                  // Back button at the top
                  Align(
                    alignment: Alignment.topLeft,
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: IconButton(
                        icon: const Icon(
                          Icons.arrow_back,
                          color: Colors.white,
                          size: 28,
                        ),
                        onPressed: () {
                          Navigator.pushReplacement(
                            context,
                            MaterialPageRoute(
                              builder: (context) => const LanguageSelectionScreen(isFirstTime: true),
                            ),
                          );
                        },
                      ),
                    ),
                  ),
                  // Heading
                  Consumer<LocaleService>(
                    builder: (context, localeService, child) {
                      final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
                      return Padding(
                        padding: const EdgeInsets.only(top: 20.0),
                        child: Center(
                          child: Text(
                            t('welcome'),
                            style: const TextStyle(
                              fontSize: 28,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                              fontFamily: 'Helvetica',
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ],
              ),

              // Center image
              Column(
                children: [
                  Center(
                    child: Image.asset(
                      'assets/images/logo.png',
                      height: 300,
                      width: 300,
                    ),
                  ),
                  const SizedBox(height: 30),
                  Consumer<LocaleService>(
                    builder: (context, localeService, child) {
                      final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
                      return Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20.0),
                        child: Text(
                          t('description'),
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontSize: 20, // bigger text
                            color: Colors.white, // pure white
                            fontWeight: FontWeight.w500,
                            height: 1.4,
                          ),
                        ),
                      );
                    },
                  ),
                ],
              ),

              // Button at the bottom
              Padding(
                padding: const EdgeInsets.only(bottom: 20.0),
                child: Center(
                  child: GestureDetector(
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const DetectionElephantScreen(),
                        ),
                      );
                    },
                      child: Consumer<LocaleService>(
                        builder: (context, localeService, child) {
                          final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
                          return Container(
                            width: 300,
                            height: 50,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(25),
                            ),
                            alignment: Alignment.center,
                            child: Text(
                              t('get_started'),
                              style: const TextStyle(
                                color: Color(0xFF28A061),
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          );
                        },
                      ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
