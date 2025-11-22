import 'package:flutter/material.dart';

class ApprovalPendingView extends StatelessWidget {
  const ApprovalPendingView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Pending Approval',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: const Color(0xFF129166),
      ),

      body: Center(
        child: _PendingContent(),
      ),
    );
  }
}

class _PendingContent extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Image.asset(
          'assets/images/waitlist.png',
          height: 150,
        ),

        const SizedBox(height: 18),

        const Text(
          'You are currently on the waitlist',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.w600),
        ),

        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 20, vertical: 12),
          child: Text(
            'Please wait for the admin to approve your account.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 16, color: Colors.black54),
          ),
        ),

        const SizedBox(height: 35),

        ElevatedButton(
          onPressed: () {
            Navigator.of(context).pop();
          },
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF129166),
            padding: const EdgeInsets.symmetric(horizontal: 75, vertical: 15),
          ),
          child: const Text(
            'GO BACK',
            style: TextStyle(color: Colors.white, fontSize: 18),
          ),
        ),
      ],
    );
  }
}
