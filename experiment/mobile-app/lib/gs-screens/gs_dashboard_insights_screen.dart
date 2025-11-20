import 'package:flutter/material.dart';
import '../auth-screens/auth_screen.dart';
import 'gs_distance_actions_screen.dart'; // Replace with your actual screen

class DashboardInsightsScreen extends StatelessWidget {
  const DashboardInsightsScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // Blue Background
      backgroundColor: Color(0xFF1976D2),

      appBar: AppBar(
        backgroundColor: Color(0xFF1976D2),
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
                    'Monitor & Manage',
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      color: Colors.white, // White for contrast
                    ),
                    textAlign: TextAlign.center,
                  ),
                  SizedBox(height: 20),
                  Text(
                    'Access live data, incident reports, and intelligent insights through the mobile dashboard. Analyze patterns to improve safety strategies and protect both humans and elephants.',
                    style: TextStyle(
                      fontSize: 17,
                      color: Colors.white,
                      height: 1.5,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),

              // Center Image
              Expanded(
                child: Center(
                  child: Image.asset(
                    'assets/images/dashboard_insights.png',
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
                          builder: (context) => const DistanceActionsScreen(),
                        ),
                      );
                    },
                    child: Container(
                      height: 55,
                      decoration: BoxDecoration(
                        color: Colors.red, // Red button
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
