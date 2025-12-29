import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../config.dart';
import 'mobile_session_detail_screen.dart';

const Color customOrange = Color(0xFF129166);

class MobileSessionsScreen extends StatefulWidget {
  const MobileSessionsScreen({Key? key}) : super(key: key);

  @override
  _MobileSessionsScreenState createState() => _MobileSessionsScreenState();
}

class _MobileSessionsScreenState extends State<MobileSessionsScreen> {
  bool _isLoading = true;
  List<dynamic> _sessions = [];
  int _currentPage = 1;
  bool _hasMore = true;

  @override
  void initState() {
    super.initState();
    _loadSessions();
  }

  Future<void> _loadSessions() async {
    setState(() => _isLoading = true);
    try {
      final response = await http.get(
        Uri.parse('${Config.baseUrl}/mobile/sessions?page=$_currentPage'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          if (data['data']['data'] != null) {
            _sessions = data['data']['data'] as List;
            _hasMore = data['data']['next_page_url'] != null;
          } else {
            _sessions = data['data'] as List;
            _hasMore = false;
          }
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Failed to load sessions')),
          );
        }
      }
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'running':
        return Colors.green;
      case 'completed':
        return Colors.blue;
      case 'stopped':
        return Colors.orange;
      default:
        return Colors.red;
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading && _sessions.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_sessions.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.video_library_outlined, size: 80, color: customOrange),
            const SizedBox(height: 16),
            const Text(
              'No sessions found',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 10),
            ElevatedButton(
              onPressed: _loadSessions,
              child: const Text('Retry'),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadSessions,
      child: ListView.builder(
        padding: const EdgeInsets.all(16.0),
        itemCount: _sessions.length + (_hasMore ? 1 : 0),
        itemBuilder: (context, index) {
          if (index == _sessions.length) {
            return const Center(
              child: Padding(
                padding: EdgeInsets.all(16.0),
                child: CircularProgressIndicator(),
              ),
            );
          }

          final session = _sessions[index];
          return Card(
            elevation: 2,
            margin: const EdgeInsets.only(bottom: 12),
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: _getStatusColor(session['status'] ?? ''),
                child: const Icon(Icons.video_library, color: Colors.white),
              ),
              title: Text(
                session['session_name'] ?? 'Session #${session['id']}',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              subtitle: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 4),
                  Text('Source: ${session['source_type']}'),
                  if (session['started_at'] != null)
                    Text('Started: ${session['started_at']}'),
                  if (session['total_frames'] != null)
                    Text('Frames: ${session['total_frames']}'),
                ],
              ),
              trailing: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: _getStatusColor(session['status'] ?? ''),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  session['status'] ?? 'Unknown',
                  style: const TextStyle(color: Colors.white, fontSize: 12),
                ),
              ),
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => MobileSessionDetailScreen(sessionId: session['id']),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
