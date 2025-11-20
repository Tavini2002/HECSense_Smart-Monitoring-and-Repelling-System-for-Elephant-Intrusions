import 'package:flutter/material.dart';
import 'gs_instant_alerts_screen.dart'; // Import the next screen

class DetectionElephantScreen extends StatelessWidget {
  const DetectionElephantScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.blue, // 🔵 Background changed to blue
      appBar: AppBar(
        backgroundColor: Colors.blue, // Match app bar color
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () {
            Navigator.pop(context);
          },
        ),
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Heading and Description
              Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: const [
                  Text(
                    'AI-Powered Elephant Detection',
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      color: Colors.white, // White heading
                    ),
                    textAlign: TextAlign.center,
                  ),
                  SizedBox(height: 20),
                  Text(
                    'Detect elephants in real-time using smart cameras and sensors. '
                        'The system analyzes movement and predicts aggressive actions early to ensure safety.',
                    style: TextStyle(
                      fontSize: 17,
                      color: Colors.white, // White text
                      height: 1.5,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),

              const SizedBox(height: 0),

              // Centered Image
              Expanded(
                child: Center(
                  child: Image.asset(
                    'assets/images/elephant_detection.png',
                    height: 400,
                    width: 400,
                  ),
                ),
              ),

              // Red Button
              Padding(
                padding: const EdgeInsets.only(bottom: 20.0),
                child: SizedBox(
                  width: double.infinity,
                  child: GestureDetector(
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const InstantAlertsScreen(),
                        ),
                      );
                    },
                    child: Container(
                      height: 55,
                      decoration: BoxDecoration(
                        color: Colors.red, // 🔴 Solid red button
                        borderRadius: BorderRadius.circular(12),
                      ),
                      alignment: Alignment.center,
                      child: const Text(
                        'Next',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
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
