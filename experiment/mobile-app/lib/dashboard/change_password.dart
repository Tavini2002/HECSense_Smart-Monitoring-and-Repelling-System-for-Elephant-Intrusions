import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config.dart';
import '../auth-screens/login_screen.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();

  final TextEditingController _currentPwdCtrl = TextEditingController();
  final TextEditingController _newPwdCtrl = TextEditingController();
  final TextEditingController _confirmPwdCtrl = TextEditingController();

  final FlutterSecureStorage _secureStore = const FlutterSecureStorage();

  String _feedbackMsg = '';
  bool _loading = false;

  Future<void> _handlePasswordUpdate() async {
    setState(() {
      _loading = true;
      _feedbackMsg = '';
    });

    final savedUserId = await _secureStore.read(key: 'userId');

    if (savedUserId == null) {
      setState(() {
        _feedbackMsg = 'User session expired. Please login again.';
        _loading = false;
      });
      return;
    }

    final endpoint = Uri.parse('${Config.baseUrl}/user/change-password/mobile');

    final response = await http.post(
      endpoint,
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'user_id': savedUserId,
        'old_password': _currentPwdCtrl.text,
        'new_password': _newPwdCtrl.text,
      }),
    );

    setState(() => _loading = false);

    if (response.statusCode == 200) {
      final decoded = jsonDecode(response.body);

      if (decoded['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Password changed successfully! Logging out...'),
          ),
        );

        await _secureStore.delete(key: 'userId');
        await _secureStore.delete(key: 'isLoggedIn');

        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (_) => const LoginScreen()),
              (_) => false,
        );
      } else {
        setState(() {
          _feedbackMsg = decoded['message'] ?? 'Unable to update password.';
        });
      }
    } else {
      setState(() {
        _feedbackMsg = 'Something went wrong. Please try again.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Change Password'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(18.0),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              _buildPasswordField(
                controller: _currentPwdCtrl,
                label: 'Old Password',
                validatorMsg: 'Enter your current password',
              ),
              const SizedBox(height: 12),
              _buildPasswordField(
                controller: _newPwdCtrl,
                label: 'New Password',
                validatorMsg: 'Enter a new password',
              ),
              const SizedBox(height: 12),
              _buildPasswordField(
                controller: _confirmPwdCtrl,
                label: 'Confirm New Password',
                validatorMsg: 'Confirm your new password',
                confirmMatch: true,
              ),
              const SizedBox(height: 18),

              if (_feedbackMsg.isNotEmpty)
                Text(
                  _feedbackMsg,
                  style: const TextStyle(color: Colors.red),
                  textAlign: TextAlign.center,
                ),

              const SizedBox(height: 14),

              _loading
                  ? const CircularProgressIndicator()
                  : SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.orange,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                  onPressed: () {
                    if (_formKey.currentState!.validate()) {
                      _handlePasswordUpdate();
                    }
                  },
                  child: const Text(
                    'Update Password',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
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

  Widget _buildPasswordField({
    required TextEditingController controller,
    required String label,
    required String validatorMsg,
    bool confirmMatch = false,
  }) {
    return TextFormField(
      controller: controller,
      obscureText: true,
      decoration: InputDecoration(labelText: label),
      validator: (value) {
        if (value == null || value.isEmpty) {
          return validatorMsg;
        }
        if (confirmMatch && value != _newPwdCtrl.text) {
          return 'Passwords do not match';
        }
        return null;
      },
    );
  }
}
