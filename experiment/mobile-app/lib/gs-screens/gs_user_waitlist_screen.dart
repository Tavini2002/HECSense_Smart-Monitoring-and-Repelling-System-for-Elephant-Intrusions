import 'package:flutter/material.dart';

class ApprovalPendingView extends StatelessWidget {
  const ApprovalPendingView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: const Color(0xFF129166),
        title: const Text(
          'Pending Approval',
          style: TextStyle(color: Colors.white),
        ),
      ),
      body: const Center(
        child: _ApprovalMessageSection(),
      ),
    );
  }
}

class _ApprovalMessageSection extends StatelessWidget {
  const _ApprovalMessageSection();

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        const _PendingIllustration(),
        const SizedBox(height: 18),
        const _StatusHeading(),
        const _StatusDescription(),
        const SizedBox(height: 35),
        _BackActionButton(
          onTap: () => Navigator.pop(context),
        ),
      ],
    );
  }
}

class _PendingIllustration extends StatelessWidget {
  const _PendingIllustration();

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      'assets/images/waitlist.png',
      height: 150,
    );
  }
}

class _StatusHeading extends StatelessWidget {
  const _StatusHeading();

  @override
  Widget build(BuildContext context) {
    return const Text(
      'You are currently on the waitlist',
      textAlign: TextAlign.center,
      style: TextStyle(
        fontSize: 20,
        fontWeight: FontWeight.w600,
      ),
    );
  }
}

class _StatusDescription extends StatelessWidget {
  const _StatusDescription();

  @override
  Widget build(BuildContext context) {
    return const Padding(
      padding: EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      child: Text(
        'Please wait until an administrator approves your account.',
        textAlign: TextAlign.center,
        style: TextStyle(
          fontSize: 16,
          color: Colors.black54,
        ),
      ),
    );
  }
}

class _BackActionButton extends StatelessWidget {
  final VoidCallback onTap;

  const _BackActionButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: onTap,
      style: ElevatedButton.styleFrom(
        backgroundColor: const Color(0xFF129166),
        padding: const EdgeInsets.symmetric(horizontal: 75, vertical: 15),
      ),
      child: const Text(
        'GO BACK',
        style: TextStyle(
          fontSize: 18,
          color: Colors.white,
        ),
      ),
    );
  }
}
