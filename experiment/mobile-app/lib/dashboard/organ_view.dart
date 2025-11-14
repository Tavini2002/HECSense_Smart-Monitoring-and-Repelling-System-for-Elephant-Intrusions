import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config.dart';

class OrganViewScreen extends StatefulWidget {
  final int organId;

  const OrganViewScreen({Key? key, required this.organId}) : super(key: key);

  @override
  _OrganViewScreenState createState() => _OrganViewScreenState();
}

class _OrganViewScreenState extends State<OrganViewScreen> {
  Map<String, dynamic>? organDetails;
  bool isLoading = true;
  bool isCheckingRequest = true; // New flag for loading state of the button
  final _storage = FlutterSecureStorage();
  bool hasRequested = false;
  String requestStatus = '';

  @override
  void initState() {
    super.initState();
    _fetchOrganDetails();
    _checkOrganRequest();
  }

  Future<void> _fetchOrganDetails() async {
    final url = '${Config.baseUrl}/organ/mobile/${widget.organId}';
    try {
      final response = await http.get(Uri.parse(url));
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          setState(() {
            organDetails = data['data'];
            isLoading = false;
          });
        } else {
          throw Exception(data['message'] ?? 'Failed to load organ details');
        }
      } else {
        throw Exception('Failed to load organ details');
      }
    } catch (error) {
      setState(() {
        isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $error')),
      );
    }
  }

  Future<void> _checkOrganRequest() async {
    final userId = await _storage.read(key: 'userId');
    if (userId == null) return;

    final url = '${Config.baseUrl}/check-organ-request/mobile';
    try {
      final response = await http.post(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'organ_id': widget.organId,
          'user_id': int.parse(userId),
        }),
      );

      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);
        if (responseData['success'] == true && responseData['exists'] == true) {
          setState(() {
            hasRequested = true;
            requestStatus = responseData['status'] ?? '';
          });
        } else {
          setState(() {
            hasRequested = false;
            requestStatus = '';
          });
        }
      } else {
        throw Exception('Failed to check organ request');
      }
    } catch (error) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $error')),
      );
    } finally {
      setState(() {
        isCheckingRequest = false; // Hide loading state once check is complete
      });
    }
  }

  Future<void> _requestOrgan() async {
    final userId = await _storage.read(key: 'userId');
    if (userId == null) return;

    final url = '${Config.baseUrl}/request-organ/mobile';
    try {
      final response = await http.post(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'organ_id': widget.organId,
          'user_id': int.parse(userId),
        }),
      );

      if (response.statusCode == 201) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Organ request created successfully')),
        );
        setState(() {
          hasRequested = true;
          requestStatus = 'pending';
        });
      } else {
        throw Exception('Failed to create organ request');
      }
    } catch (error) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $error')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Organ Details', style: TextStyle(color: Colors.white)),
        backgroundColor: Colors.redAccent,
      ),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : organDetails != null
          ? Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            // Status container at the top
            if (hasRequested)
              Column(
                children: [
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 15),
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: requestStatus == 'pending'
                          ? Colors.grey // Pending: gray
                          : requestStatus == 'approved'
                          ? Colors.green // Approved: green
                          : Color(0xFFE0115F), // Rejected: #EE4B2B
                      borderRadius: BorderRadius.circular(3),
                    ),
                    child: Text(
                      requestStatus == 'pending'
                          ? 'Request: Pending'
                          : requestStatus == 'approved'
                          ? 'Approved'
                          : 'Rejected',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const Divider(
                    thickness: 2,
                    color: Color(0xFFfaf5f5),
                    height: 20,
                  ),
                ],
              ),
            Expanded(
              child: ListView(
                children: [
                  _buildInfoCard('🫀', 'Organ Name', organDetails!['organ_name']),
                  _buildInfoCard('🩸', 'Blood Type', organDetails!['blood_type']),
                  _buildInfoCard('👤', 'Donor Name', organDetails!['donor_name']),
                  _buildInfoCard('🎂', 'Donor Age', organDetails!['donor_age'].toString()),
                  _buildInfoCard('⚧️', 'Donor Gender', organDetails!['donor_gender']),
                  _buildInfoCard('💪', 'Organ Type', organDetails!['organ_type']),
                  _buildInfoCard('🩺', 'Condition', organDetails!['organ_condition']),
                ],
              ),
            ),

            // Show loading spinner or request button based on isCheckingRequest
            if (!hasRequested)
              isCheckingRequest
                  ? const Center(
                child: CircularProgressIndicator(),
              )
                  : SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _requestOrgan,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Color(0xFFFF4444),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 15),
                  ),
                  child: const Text(
                    'Request',
                    style: TextStyle(color: Colors.white, fontSize: 18),
                  ),
                ),
              ),
          ],
        ),
      )
          : const Center(child: Text('Organ details not found')),
    );
  }

  Widget _buildInfoCard(String emoji, String label, String value) {
    return Card(
      elevation: 3,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      margin: const EdgeInsets.symmetric(vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Text(
              emoji,
              style: const TextStyle(fontSize: 28),
            ),
            const SizedBox(width: 15),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                ),
                const SizedBox(height: 5),
                Text(
                  value,
                  style: const TextStyle(fontSize: 16, color: Colors.black87),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
