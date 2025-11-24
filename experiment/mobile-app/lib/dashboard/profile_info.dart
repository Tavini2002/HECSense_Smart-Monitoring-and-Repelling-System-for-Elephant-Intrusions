import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'dart:convert';
import '../config.dart';

class ProfileInfoScreen extends StatefulWidget {
  const ProfileInfoScreen({super.key});

  @override
  State<ProfileInfoScreen> createState() => _ProfileInfoScreenState();
}

class _ProfileInfoScreenState extends State<ProfileInfoScreen> {
  final FlutterSecureStorage _secureStore = const FlutterSecureStorage();

  String _userEmail = '';
  String _userName = '';
  String _contactNumber = '';
  String _bloodGroup = '';
  String _donatedOrgan = '';
  bool _isFetching = true;

  @override
  void initState() {
    super.initState();
    _loadProfileData();
  }

  Future<void> _loadProfileData() async {
    final userId = await _secureStore.read(key: "userId");

    if (userId == null) {
      _showMessage("User ID missing. Please log in again.");
      setState(() => _isFetching = false);
      return;
    }

    final fetchUrl = "${Config.baseUrl}/user/profile/mobile?user_id=$userId";

    try {
      final response = await http.get(Uri.parse(fetchUrl), headers: {
        "Content-Type": "application/json",
      });

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);

        if (body["success"] == true && body["data"] != null) {
          final info = body["data"];

          setState(() {
            _userName = info["full_name"] ?? "";
            _userEmail = info["email"] ?? "";
            _contactNumber = info["phone_number"] ?? "";
            _bloodGroup = info["blood_type"] ?? "";
            _donatedOrgan = info["organ"] ?? "";
          });
        } else {
          _showMessage(body["message"] ?? "Unable to retrieve profile.");
        }
      } else {
        _showMessage("Failed to fetch profile information.");
      }
    } catch (err) {
      _showMessage("Error occurred: $err");
    } finally {
      setState(() => _isFetching = false);
    }
  }

  void _showMessage(String text) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(text)),
    );
  }

  TableRow _infoRow(String title, String data) {
    return TableRow(children: [
      Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: Colors.black54,
          ),
        ),
      ),
      Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Text(
          data,
          style: const TextStyle(fontSize: 16, color: Colors.black87),
        ),
      ),
    ]);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Profile Info"),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
      ),

      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: _isFetching
              ? const CircularProgressIndicator()
              : Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircleAvatar(
                radius: 100,
                backgroundColor: Colors.grey.shade300,
                child: const Icon(
                  Icons.person,
                  size: 100,
                  color: Colors.grey,
                ),
              ),

              const SizedBox(height: 20),

              Table(
                columnWidths: const {
                  0: FlexColumnWidth(1),
                  1: FlexColumnWidth(2),
                },
                children: [
                  _infoRow("Full Name:", _userName),
                  _infoRow("Email:", _userEmail),
                  _infoRow("Phone Number:", _contactNumber),
                  _infoRow("Blood Type:", _bloodGroup),
                  _infoRow("Organ:", _donatedOrgan),
                ],
              ),

              const SizedBox(height: 40),

              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.orange,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    textStyle: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  onPressed: () => Navigator.pop(context),
                  child: const Text("Go Back"),
                ),
              )
            ],
          ),
        ),
      ),
    );
  }
}
