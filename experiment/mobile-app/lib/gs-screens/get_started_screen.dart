import 'package:flutter/material.dart';
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
            colors: [Color(0xFF2196F3), Color(0xFF64B5F6)], // Blue gradient
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: SafeArea(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // Heading at the top
              Padding(
                padding: const EdgeInsets.only(top: 40.0),
                child: Center(
                  child: Text(
                    'Welcome to HECSense',
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                      fontFamily: 'Helvetica',
                    ),
                  ),
                ),
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
                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 20.0),
                    child: Text(
                      'AI-powered monitoring to prevent human-elephant conflicts with real-time alerts and smart sensors.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 20, // bigger text
                        color: Colors.white, // pure white
                        fontWeight: FontWeight.w500,
                        height: 1.4,
                      ),
                    ),
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
                    child: Container(
                      width: 300,
                      height: 50,
                      decoration: BoxDecoration(
                        color: Colors.red, // Changed to red
                        borderRadius: BorderRadius.circular(25),
                      ),
                      alignment: Alignment.center,
                      child: const Text(
                        'Get Started',
                        style: TextStyle(
                          color: Colors.white, // white text on red button
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
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
