import 'package:flutter/material.dart';
import 'gs_instant_alerts_screen.dart';
import 'gs_dashboard_insights_screen.dart'; // Import the next screen

class InstantAlertsScreen extends StatelessWidget {
  const InstantAlertsScreen({Key? key}) : super(key: key);

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
                    'Smart Alerts && Responses',
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      color: Colors.white, // White heading on blue
                    ),
                    textAlign: TextAlign.center,
                  ),
                  SizedBox(height: 20),
                  Text(
                    'Get instant alerts on your phone when elephants are nearby. The system automatically plays warning sounds and triggers deterrent actions to keep everyone safe.',
                    style: TextStyle(
                      fontSize: 17,
                      color: Colors.white, // White text on blue
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
                    'assets/images/instant_alerts.png',
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
                          builder: (context) => const DashboardInsightsScreen(),
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
