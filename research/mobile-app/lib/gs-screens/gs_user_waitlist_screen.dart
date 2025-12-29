import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/locale_service.dart';

class UserWaitlistScreen extends StatelessWidget {
  const UserWaitlistScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Color(0xFF129166),
        title: Consumer<LocaleService>(
          builder: (context, localeService, child) {
            final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
            return Text(
              t('pending_approval'),
              style: const TextStyle(color: Colors.white),
            );
          },
        ),
      ),
      body: Center(
        child: Consumer<LocaleService>(
          builder: (context, localeService, child) {
            final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
            
            return Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Image.asset('assets/images/waitlist.png', height: 150),
                const SizedBox(height: 20),
                Text(
                  t('waitlist_message'),
                  style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                  textAlign: TextAlign.center,
                ),
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Text(
                    t('waitlist_description'),
                    style: const TextStyle(fontSize: 16, color: Colors.black54),
                    textAlign: TextAlign.center,
                  ),
                ),
                const SizedBox(height: 40),
                ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context); // Go back to the login screen
                  },
                  style: ElevatedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 80, vertical: 15),
                    backgroundColor: Color(0xFF129166),
                  ),
                  child: Text(
                    t('go_back'),
                    style: const TextStyle(color: Colors.white, fontSize: 18),
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }
}
