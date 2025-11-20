import 'package:flutter/material.dart';
import '../auth-screens/auth_screen.dart';
import 'gs_emergency_support_screen.dart'; // Update if the next screen differs

class AggressionDetectionScreen extends StatelessWidget {
  const AggressionDetectionScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.blue, // 🔵 Background changed to blue
      appBar: AppBar(
        backgroundColor: Colors.blue, // Match app bar with background
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Heading + Body
              Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: const [
                  Text(
                    'Elephant Aggressive Detection',
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      color: Colors.white, // Change text color for blue background
                    ),
                    textAlign: TextAlign.center,
                  ),
                  SizedBox(height: 16),
                  Text(
                    'Detect early signs of aggressive behavior using AI. '
                        'The system analyzes posture, speed, trunk and ear movement '
                        'to predict risk and trigger timely responses.',
                    style: TextStyle(
                      fontSize: 17,
                      color: Colors.white, // Change text color for visibility
                      height: 1.5,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),

              // Illustration
              Expanded(
                child: Center(
                  child: Image.asset(
                    'assets/images/aggression_detection.png',
                    height: 400,
                    width: 400,
                    fit: BoxFit.contain,
                  ),
                ),
              ),

              // Next Button (Red)
              SizedBox(
                width: double.infinity,
                child: GestureDetector(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const EmergencyMedicalSupportScreen()),
                    );
                  },
                  child: Container(
                    height: 55,
                    decoration: BoxDecoration(
                      color: Colors.red, // 🔴 Red button
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

              const SizedBox(height: 12),
            ],
          ),
        ),
      ),
    );
  }
}

class _Bullet extends StatelessWidget {
  final String text;
  const _Bullet(this.text, {Key? key}) : super(key: key);
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('•  ', style: TextStyle(fontSize: 18, height: 1.25, color: Colors.white)),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(fontSize: 15.5, color: Colors.white),
            ),
          ),
        ],
      ),
    );
  }
}

class _LegendDot extends StatelessWidget {
  final Color color;
  final String label;
  const _LegendDot({required this.color, required this.label, Key? key}) : super(key: key);
  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(width: 10, height: 10, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 6),
        Text(label, style: const TextStyle(fontSize: 13, color: Colors.white)),
      ],
    );
  }
}
