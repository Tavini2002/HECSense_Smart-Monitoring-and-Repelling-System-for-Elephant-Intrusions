import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../config.dart';
import 'package:fl_chart/fl_chart.dart';

const Color customOrange = Color(0xFF129166);

class MobileSessionDetailScreen extends StatefulWidget {
  final int sessionId;

  const MobileSessionDetailScreen({Key? key, required this.sessionId}) : super(key: key);

  @override
  _MobileSessionDetailScreenState createState() => _MobileSessionDetailScreenState();
}

class _MobileSessionDetailScreenState extends State<MobileSessionDetailScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _sessionData;

  @override
  void initState() {
    super.initState();
    _loadSessionData();
  }

  Future<void> _loadSessionData() async {
    setState(() => _isLoading = true);
    try {
      final response = await http.get(
        Uri.parse('${Config.baseUrl}/mobile/sessions/${widget.sessionId}'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          _sessionData = data['data'];
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Failed to load session data')),
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
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Session Details'),
          backgroundColor: customOrange,
        ),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_sessionData == null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Session Details'),
          backgroundColor: customOrange,
        ),
        body: const Center(child: Text('Session not found')),
      );
    }

    final session = _sessionData!['session'];
    final stats = _sessionData!['stats'];
    final detectionsByTrack = _sessionData!['detections_by_track'] as List;
    final timelineData = _sessionData!['timeline_data'] as List;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Session Details'),
        backgroundColor: customOrange,
      ),
      body: RefreshIndicator(
        onRefresh: _loadSessionData,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Session Info Card
              _buildSessionInfoCard(session),
              const SizedBox(height: 24),
              
              // Statistics Cards
              _buildStatsCards(stats),
              const SizedBox(height: 24),
              
              // Behavior Chart
              _buildBehaviorChart(stats),
              const SizedBox(height: 24),
              
              // Timeline Chart
              if (timelineData.isNotEmpty) ...[
                _buildTimelineChart(timelineData),
                const SizedBox(height: 24),
              ],
              
              // Elephants Tracked
              _buildElephantsTracked(detectionsByTrack),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSessionInfoCard(Map<String, dynamic> session) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    session['session_name'] ?? 'Session #${session['id']}',
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                ),
                Container(
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
              ],
            ),
            const SizedBox(height: 16),
            _buildInfoRow('Source Type', session['source_type'] ?? 'N/A'),
            _buildInfoRow('Started At', session['started_at'] ?? 'N/A'),
            if (session['ended_at'] != null)
              _buildInfoRow('Ended At', session['ended_at']),
            if (session['total_frames'] != null)
              _buildInfoRow('Total Frames', session['total_frames'].toString()),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              '$label:',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }

  Widget _buildStatsCards(Map<String, dynamic> stats) {
    return Row(
      children: [
        Expanded(
          child: _buildStatCard('Total', stats['total_detections'].toString(), Icons.visibility, Colors.blue),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildStatCard('Calm', stats['calm_count'].toString(), Icons.check_circle, Colors.green),
        ),
      ],
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Icon(icon, color: color, size: 32),
            const SizedBox(height: 8),
            Text(
              value,
              style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              title,
              style: TextStyle(fontSize: 12, color: Colors.grey[600]),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBehaviorChart(Map<String, dynamic> stats) {
    final calm = _parseDouble(stats['calm_count']);
    final warning = _parseDouble(stats['warning_count']);
    final aggressive = _parseDouble(stats['aggressive_count']);
    final total = calm + warning + aggressive;

    if (total == 0) {
      return Card(
        elevation: 2,
        child: const Padding(
          padding: EdgeInsets.all(16.0),
          child: Center(child: Text('No behavior data available')),
        ),
      );
    }

    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Behavior Distribution',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            SizedBox(
              height: 250,
              child: PieChart(
                PieChartData(
                  sections: [
                    PieChartSectionData(
                      value: calm,
                      title: '${((calm / total) * 100).toStringAsFixed(0)}%',
                      color: const Color(0xFF4BC0C0),
                      radius: 80,
                      titleStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    PieChartSectionData(
                      value: warning,
                      title: '${((warning / total) * 100).toStringAsFixed(0)}%',
                      color: const Color(0xFFFFCE56),
                      radius: 80,
                      titleStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    PieChartSectionData(
                      value: aggressive,
                      title: '${((aggressive / total) * 100).toStringAsFixed(0)}%',
                      color: const Color(0xFFFF6384),
                      radius: 80,
                      titleStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                  ],
                  sectionsSpace: 2,
                  centerSpaceRadius: 40,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTimelineChart(List timelineData) {
    final maxValue = timelineData.map((e) => _parseDouble(e['count'])).reduce((a, b) => a > b ? a : b);

    final spots = timelineData.asMap().entries.map((entry) {
      return FlSpot(entry.key.toDouble(), _parseDouble(entry.value['count']));
    }).toList();

    final aggressiveSpots = timelineData.asMap().entries.map((entry) {
      return FlSpot(entry.key.toDouble(), _parseDouble(entry.value['aggressive_count']));
    }).toList();

    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Timeline',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            SizedBox(
              height: 250,
              child: LineChart(
                LineChartData(
                  gridData: FlGridData(show: true),
                  titlesData: FlTitlesData(
                    leftTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        reservedSize: 40,
                        getTitlesWidget: (value, meta) {
                          return Text(
                            value.toInt().toString(),
                            style: const TextStyle(fontSize: 10),
                          );
                        },
                      ),
                    ),
                    bottomTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                    topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                    rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  ),
                  borderData: FlBorderData(show: true),
                  lineBarsData: [
                    LineChartBarData(
                      spots: spots,
                      isCurved: true,
                      color: const Color(0xFF36A2EB),
                      barWidth: 3,
                      dotData: const FlDotData(show: false),
                      belowBarData: BarAreaData(show: true, color: const Color(0xFF36A2EB).withOpacity(0.3)),
                    ),
                    LineChartBarData(
                      spots: aggressiveSpots,
                      isCurved: true,
                      color: const Color(0xFFFF6384),
                      barWidth: 3,
                      dotData: const FlDotData(show: false),
                      belowBarData: BarAreaData(show: true, color: const Color(0xFFFF6384).withOpacity(0.3)),
                    ),
                  ],
                  maxY: maxValue * 1.1,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildElephantsTracked(List detectionsByTrack) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Elephants Tracked',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            ListView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: detectionsByTrack.length,
              itemBuilder: (context, index) {
                final track = detectionsByTrack[index];
                return ListTile(
                  leading: CircleAvatar(
                    backgroundColor: customOrange,
                    child: Text(
                      '#${track['track_id']}',
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                    ),
                  ),
                  title: Text('Track #${track['track_id']}'),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Detections: ${track['count']}'),
                      if (track['avg_speed'] != null)
                        Text('Avg Speed: ${_parseDouble(track['avg_speed']).toStringAsFixed(2)} km/h'),
                      if (track['max_speed'] != null)
                        Text('Max Speed: ${_parseDouble(track['max_speed']).toStringAsFixed(2)} km/h'),
                    ],
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) {
      return double.tryParse(value) ?? 0.0;
    }
    return 0.0;
  }
}

