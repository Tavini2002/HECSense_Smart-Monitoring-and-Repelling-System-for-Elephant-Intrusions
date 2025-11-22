import 'package:flutter/material.dart';
import '../auth-screens/auth_screen.dart';

class EmergencyMedicalSupportScreen extends StatelessWidget {
  const EmergencyMedicalSupportScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF2196F3),
      appBar: AppBar(
        elevation: 0,
        backgroundColor: const Color(0xFF2196F3),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(22.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const _HeaderSection(),

              // Illustration
              Expanded(
                child: Center(
                  child: Image.asset(
                    'assets/images/medical_support.png',
                    height: 330,
                    width: 330,
                    fit: BoxFit.contain,
                  ),
                ),
              ),

              // Next button
              _NextButton(
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const HomeScreen()),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _HeaderSection extends StatelessWidget {
  const _HeaderSection();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: const [
        Text(
          'Elephant Injury & First Aid',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 28,
            fontWeight: FontWeight.w700,
            color: Colors.white,
          ),
        ),
        SizedBox(height: 18),
        Text(
          'When an elephant is injured, the app instantly connects rescuers and vets. AI guidance suggests first-aid steps to stabilize the animal until help arrives.',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 17,
            height: 1.5,
            color: Colors.white,
          ),
        ),
        SizedBox(height: 18),
      ],
    );
  }
}

class _NextButton extends StatelessWidget {
  final VoidCallback onTap;
  const _NextButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          height: 55,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            gradient: const LinearGradient(
              begin: Alignment.centerLeft,
              end: Alignment.centerRight,
              colors: [
                Color(0xFFFF3D00),
                Color(0xFFFF6E40),
              ],
            ),
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
    );
  }
}

class _InfoBullet extends StatelessWidget {
  final String content;

  const _InfoBullet(this.content, {super.key});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '•  ',
            style: TextStyle(
              fontSize: 18,
              height: 1.25,
              color: Colors.white,
            ),
          ),
          Expanded(
            child: Text(
              content,
              style: const TextStyle(
                fontSize: 15.5,
                color: Colors.white,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
