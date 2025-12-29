import 'package:flutter/material.dart';

const Color customOrange = Color(0xFF129166);

class MobileDetectionDetailScreen extends StatelessWidget {
  final Map<String, dynamic> detection;

  const MobileDetectionDetailScreen({Key? key, required this.detection}) : super(key: key);

  Color _getBehaviorColor(String behavior) {
    switch (behavior.toLowerCase()) {
      case 'aggressive':
        return Colors.red;
      case 'warning':
        return Colors.orange;
      case 'calm':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    final behavior = detection['behavior'] ?? 'unknown';
    final behaviorColor = _getBehaviorColor(behavior);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detection Details'),
        backgroundColor: customOrange,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Behavior Card
            Card(
              elevation: 2,
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 30,
                      backgroundColor: behaviorColor.withOpacity(0.2),
                      child: Icon(
                        Icons.visibility,
                        color: behaviorColor,
                        size: 30,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Behavior',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            behavior.toUpperCase(),
                            style: TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: behaviorColor,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Session Information
            _buildSectionCard(
              'Session Information',
              [
                _buildInfoRow('Session Name', detection['session_name'] ?? 'N/A'),
                _buildInfoRow('Session ID', detection['session_id']?.toString() ?? 'N/A'),
                _buildInfoRow('Track ID', detection['track_id']?.toString() ?? 'N/A'),
                _buildInfoRow('Frame Number', detection['frame_number']?.toString() ?? 'N/A'),
              ],
            ),
            const SizedBox(height: 16),

            // Detection Metrics
            _buildSectionCard(
              'Detection Metrics',
              [
                _buildInfoRow('Confidence', '${_parseDouble(detection['confidence']).toStringAsFixed(1)}%'),
                _buildInfoRow('Speed', '${_parseDouble(detection['speed_kmph']).toStringAsFixed(2)} km/h'),
                if (detection['aggression_score'] != null)
                  _buildInfoRow(
                    'Aggression Score',
                    _parseDouble(detection['aggression_score']).toStringAsFixed(2),
                  ),
              ],
            ),
            const SizedBox(height: 16),

            // Alert Information
            if (detection['alert_triggered'] == true)
              Card(
                elevation: 2,
                color: Colors.red[50],
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.notifications_active, color: Colors.red),
                          const SizedBox(width: 8),
                          const Text(
                            'Alert Triggered',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Colors.red,
                            ),
                          ),
                        ],
                      ),
                      if (detection['alert_type'] != null) ...[
                        const SizedBox(height: 8),
                        _buildInfoRow('Alert Type', detection['alert_type']),
                      ],
                    ],
                  ),
                ),
              ),
            if (detection['alert_triggered'] == true) const SizedBox(height: 16),

            // Timestamp
            _buildSectionCard(
              'Timing Information',
              [
                _buildInfoRow(
                  'Detected At',
                  _formatDateTime(detection['detected_at'] ?? ''),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionCard(String title, List<Widget> children) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            ...children,
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              '$label:',
              style: TextStyle(
                fontWeight: FontWeight.w500,
                color: Colors.grey[700],
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _formatDateTime(String dateTimeString) {
    if (dateTimeString.isEmpty) return 'N/A';
    try {
      final dateTime = DateTime.parse(dateTimeString);
      return '${dateTime.day}/${dateTime.month}/${dateTime.year} ${dateTime.hour.toString().padLeft(2, '0')}:${dateTime.minute.toString().padLeft(2, '0')}';
    } catch (e) {
      return dateTimeString;
    }
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

