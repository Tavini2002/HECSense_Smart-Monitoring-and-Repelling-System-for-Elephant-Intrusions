import 'package:flutter/material.dart';
import '../auth-screens/auth_screen.dart';

class EmergencyMedicalSupportScreen extends StatelessWidget {
  const EmergencyMedicalSupportScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF2196F3),
      appBar: AppBar(
        backgroundColor: const Color(0xFF2196F3),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: const SafeArea(
        child: Padding(
          padding: EdgeInsets.all(22.0),
          child: _MainContent(),
        ),
      ),
    );
  }
}

class _MainContent extends StatelessWidget {
  const _MainContent();

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        const _TitleDescription(),
        const Expanded(
          child: Center(
            child: _MedicalIllustration(),
          ),
        ),
        _ProceedButton(
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const HomeScreen()),
            );
          },
        ),
      ],
    );
  }
}

class _TitleDescription extends StatelessWidget {
  const _TitleDescription();

  @override
  Widget build(BuildContext context) {
    return const Column(
      children: [
        Text(
          'Elephant Injury & First Aid',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 28,
            fontWeight: FontWeight.w700,
            color: Colors.white,
          ),
        ),
        SizedBox(height: 16),
        Text(
          'In case of injuries, the system links rescuers with vets immediately. '
              'AI-based suggestions guide basic first-aid until medical teams arrives.',
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

class _MedicalIllustration extends StatelessWidget {
  const _MedicalIllustration();

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      'assets/images/medical_support.png',
      height: 330,
      width: 330,
      fit: BoxFit.contain,
    );
  }
}

class _ProceedButton extends StatelessWidget {
  final VoidCallback onPressed;
  const _ProceedButton({required this.onPressed});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onPressed,
      child: Container(
        height: 55,
        width: double.infinity,
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
            color: Colors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }
}

class InfoLineItem extends StatelessWidget {
  final String text;
  const InfoLineItem({required this.text, super.key});

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          '•  ',
          style: TextStyle(fontSize: 18, color: Colors.white, height: 1.25),
        ),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(fontSize: 15.5, color: Colors.white),
          ),
        ),
      ],
    );
  }
}
