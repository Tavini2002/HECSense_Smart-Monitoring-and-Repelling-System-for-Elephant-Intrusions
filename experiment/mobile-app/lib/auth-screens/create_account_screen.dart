import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'login_screen.dart'; // Import the LoginScreen
import '../config.dart'; // Import the config file

class CreateAccountScreen extends StatefulWidget {
  const CreateAccountScreen({Key? key}) : super(key: key);

  @override
  _CreateAccountScreenState createState() => _CreateAccountScreenState();
}

class _CreateAccountScreenState extends State<CreateAccountScreen> {
  final _formKey = GlobalKey<FormState>();
  String _errorMessage = ''; // To store the error message

  final backendPoint = Uri.parse('${Config.baseUrl}/register/mobile'); // Use the base URL from Config

  // Define controllers for text input fields
  final TextEditingController _fullNameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _dobController = TextEditingController();

  // Dropdown for Gender
  String? _selectedGender;
  final List<String> _genders = ['Male', 'Female'];

    Future<void> registerUser() async {
      try {
        final payload = {
          'full_name': _fullNameController.text,
          'email': _emailController.text,
          'password': _passwordController.text,
          'phone_number': _phoneController.text,
          'gender': _selectedGender,
          'dob': _dobController.text,
        };

        final response = await http.post(
          backendPoint,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json', // <- important
          },
          body: jsonEncode(payload),
        );

        if (response.statusCode == 201) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Registration successful!')),
          );
          setState(() => _errorMessage = '');
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (_) => const LoginScreen()),
          );
          return;
        }

        // Non-201: try JSON first, else show raw text
        String message;
        try {
          final data = jsonDecode(response.body);
          message = data['message']?.toString() ??
              data['error']?.toString() ??
              'Registration failed';
        } catch (_) {
          // Not JSON (likely HTML error page)
          message = 'Error ${response.statusCode}: ${response.reasonPhrase}\n'
              '${response.body.substring(0, response.body.length.clamp(0, 300))}';
        }

        setState(() => _errorMessage = message);
      } catch (e) {
        setState(() => _errorMessage = 'Network error: $e');
      }
    }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Create Account',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: const Color(0xFF28A061),
      ),
      body: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              // Full Name
              TextFormField(
                controller: _fullNameController,
                decoration: const InputDecoration(labelText: 'Full Name'),
                validator: (value) => value!.isEmpty ? 'Enter full name' : null,
              ),
              const SizedBox(height: 10),

              // Email
              TextFormField(
                controller: _emailController,
                decoration: const InputDecoration(labelText: 'Email'),
                keyboardType: TextInputType.emailAddress,
                validator: (value) => value!.isEmpty ? 'Enter email' : null,
              ),
              const SizedBox(height: 10),

              // Password
              TextFormField(
                controller: _passwordController,
                decoration: const InputDecoration(labelText: 'Password'),
                obscureText: true,
                validator: (value) => value!.isEmpty ? 'Enter password' : null,
              ),
              const SizedBox(height: 10),

              // Phone Number
              TextFormField(
                controller: _phoneController,
                decoration: const InputDecoration(labelText: 'Phone Number'),
                keyboardType: TextInputType.phone,
                validator: (value) => value!.isEmpty ? 'Enter phone number' : null,
              ),
              const SizedBox(height: 10),

              // Gender Dropdown
              DropdownButtonFormField<String>(
                decoration: const InputDecoration(labelText: 'Gender'),
                value: _selectedGender,
                items: _genders.map((gender) {
                  return DropdownMenuItem(
                    value: gender,
                    child: Text(gender),
                  );
                }).toList(),
                onChanged: (value) {
                  setState(() {
                    _selectedGender = value;
                  });
                },
                validator: (value) => value == null ? 'Select gender' : null,
              ),
              const SizedBox(height: 10),

              // Date of Birth
              TextFormField(
                controller: _dobController,
                decoration: const InputDecoration(labelText: 'Date of Birth (YYYY-MM-DD)'),
                keyboardType: TextInputType.datetime,
                validator: (value) => value!.isEmpty ? 'Enter date of birth' : null,
              ),
              const SizedBox(height: 20),

              // Display Error Message
              if (_errorMessage.isNotEmpty)
                Text(
                  _errorMessage,
                  style: const TextStyle(color: Colors.red),
                  textAlign: TextAlign.center,
                ),
              const SizedBox(height: 10),

              // Submit Button
              ElevatedButton(
                onPressed: () {
                  if (_formKey.currentState!.validate()) {
                    registerUser();
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF28A061),
                  padding: const EdgeInsets.symmetric(vertical: 15),
                ),
                child: const Text(
                  'Create Account',
                  style: TextStyle(color: Colors.white, fontSize: 16),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
