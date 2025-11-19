import 'package:flutter/material.dart';
import '../auth-screens/auth_screen.dart'; // or your next screen

class EmergencyMedicalSupportScreen extends StatelessWidget {
  const EmergencyMedicalSupportScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF2196F3), // Blue background
      appBar: AppBar(
        backgroundColor: const Color(0xFF2196F3),
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
                    'Elephant Injury & First Aid',
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      color: Colors.white, // White heading text
                    ),
                    textAlign: TextAlign.center,
                  ),
                  SizedBox(height: 16),
                  Text(
                    'When an elephant is injured, the app instantly connects rescuers and vets. AI guidance suggests first-aid steps to stabilize the animal until help arrives.',
                    style: TextStyle(
                      fontSize: 17,
                      color: Colors.white, // White paragraph text
                      height: 1.5,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  SizedBox(height: 18),
                ],
              ),

              // Illustration
              Expanded(
                child: Center(
                  child: Image.asset(
                    'assets/images/medical_support.png', // <- put your vector here
                    height: 340,
                    width: 340,
                    fit: BoxFit.contain,
                  ),
                ),
              ),

              // Red Gradient Next button
              SizedBox(
                width: double.infinity,
                child: GestureDetector(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const HomeScreen()),
                    );
                  },
                  child: Container(
                    height: 55,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFFFF3D00), Color(0xFFFF6E40)], // Red gradient
                        begin: Alignment.centerLeft,
                        end: Alignment.centerRight,
                      ),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    alignment: Alignment.center,
                    child: const Text(
                      'Next',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.white, // White button text
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
