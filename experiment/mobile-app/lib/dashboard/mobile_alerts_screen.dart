import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../config.dart';

const Color customOrange = Color(0xFF129166);

class MobileAlertsScreen extends StatefulWidget {
  const MobileAlertsScreen({Key? key}) : super(key: key);

  @override
  _MobileAlertsScreenState createState() => _MobileAlertsScreenState();
}

class _MobileAlertsScreenState extends State<MobileAlertsScreen> {
  bool _isLoading = true;
  List<dynamic> _alerts = [];
  int _currentPage = 1;
  bool _hasMore = true;
  String _selectedFilter = 'all';
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _loadAlerts();
  }

  Future<void> _loadAlerts({bool refresh = false}) async {
    if (refresh) {
      setState(() {
        _currentPage = 1;
        _hasMore = true;
        _alerts = [];
      });
    }

    setState(() => _isLoading = true);
    try {
      String url = '${Config.baseUrl}/mobile/alerts?page=$_currentPage';
      if (_selectedFilter != 'all') {
        url += '&alert_type=$_selectedFilter';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          if (data['data']['data'] != null) {
            final newAlerts = data['data']['data'] as List;
            if (refresh) {
              _alerts = newAlerts;
            } else {
              _alerts.addAll(newAlerts);
            }
            _hasMore = data['data']['next_page_url'] != null;
          } else {
            _alerts = [];
            _hasMore = false;
          }
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Failed to load alerts')),
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

  Color _getAlertTypeColor(String alertType) {
    switch (alertType.toLowerCase()) {
      case 'warning_tts':
      case 'danger_tts':
        return Colors.orange;
      case 'alarm_sound':
      case 'stop_alarm':
        return Colors.red;
      case 'zone_transition':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }

  IconData _getAlertTypeIcon(String alertType) {
    switch (alertType.toLowerCase()) {
      case 'warning_tts':
      case 'danger_tts':
        return Icons.warning;
      case 'alarm_sound':
      case 'stop_alarm':
        return Icons.notifications_active;
      case 'zone_transition':
        return Icons.compare_arrows;
      default:
        return Icons.notifications;
    }
  }

  String _formatAlertType(String alertType) {
    return alertType
        .split('_')
        .map((word) => word[0].toUpperCase() + word.substring(1))
        .join(' ');
  }

  List<dynamic> get _filteredAlerts {
    if (_searchQuery.isEmpty) {
      return _alerts;
    }
    return _alerts.where((alert) {
      final sessionName = (alert['session_name'] ?? '').toString().toLowerCase();
      final message = (alert['message'] ?? '').toString().toLowerCase();
      final query = _searchQuery.toLowerCase();
      return sessionName.contains(query) || message.contains(query);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Filter and Search Bar
        Container(
          padding: const EdgeInsets.all(16.0),
          color: Colors.white,
          child: Column(
            children: [
              // Search Bar
              TextField(
                decoration: InputDecoration(
                  hintText: 'Search alerts...',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: _searchQuery.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear),
                          onPressed: () {
                            setState(() => _searchQuery = '');
                          },
                        )
                      : null,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  filled: true,
                  fillColor: Colors.grey[100],
                ),
                onChanged: (value) {
                  setState(() => _searchQuery = value);
                },
              ),
              const SizedBox(height: 12),
              // Filter Chips
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _buildFilterChip('all', 'All', Icons.list),
                    const SizedBox(width: 8),
                    _buildFilterChip('warning_tts', 'Warning', Icons.warning),
                    const SizedBox(width: 8),
                    _buildFilterChip('alarm_sound', 'Alarm', Icons.notifications_active),
                    const SizedBox(width: 8),
                    _buildFilterChip('zone_transition', 'Zone', Icons.compare_arrows),
                  ],
                ),
              ),
            ],
          ),
        ),

        // Alerts List
        Expanded(
          child: _isLoading && _alerts.isEmpty
              ? const Center(child: CircularProgressIndicator())
              : _filteredAlerts.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.notifications_off_outlined,
                              size: 80, color: Colors.grey[400]),
                          const SizedBox(height: 16),
                          Text(
                            _searchQuery.isNotEmpty
                                ? 'No alerts found'
                                : 'No alerts available',
                            style: const TextStyle(
                                fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 10),
                          if (_searchQuery.isEmpty)
                            ElevatedButton(
                              onPressed: () => _loadAlerts(refresh: true),
                              child: const Text('Retry'),
                            ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () => _loadAlerts(refresh: true),
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16.0),
                        itemCount: _filteredAlerts.length + (_hasMore ? 1 : 0),
                        itemBuilder: (context, index) {
                          if (index == _filteredAlerts.length) {
                            if (_hasMore && !_isLoading) {
                              _currentPage++;
                              _loadAlerts();
                            }
                            return const Center(
                              child: Padding(
                                padding: EdgeInsets.all(16.0),
                                child: CircularProgressIndicator(),
                              ),
                            );
                          }

                          final alert = _filteredAlerts[index];
                          final alertType = alert['alert_type'] ?? 'unknown';
                          final alertColor = _getAlertTypeColor(alertType);
                          final alertIcon = _getAlertTypeIcon(alertType);

                          return Card(
                            elevation: 2,
                            margin: const EdgeInsets.only(bottom: 12),
                            child: Padding(
                              padding: const EdgeInsets.all(16.0),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      CircleAvatar(
                                        backgroundColor:
                                            alertColor.withOpacity(0.2),
                                        child: Icon(alertIcon,
                                            color: alertColor, size: 20),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              alert['session_name'] ??
                                                  'Session #${alert['session_id']}',
                                              style: const TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 16,
                                              ),
                                            ),
                                            const SizedBox(height: 4),
                                            Text(
                                              'Track #${alert['track_id'] ?? 'N/A'}',
                                              style: TextStyle(
                                                color: Colors.grey[600],
                                                fontSize: 12,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 12,
                                          vertical: 6,
                                        ),
                                        decoration: BoxDecoration(
                                          color: alertColor,
                                          borderRadius:
                                              BorderRadius.circular(12),
                                        ),
                                        child: Text(
                                          _formatAlertType(alertType)
                                              .toUpperCase(),
                                          style: const TextStyle(
                                            color: Colors.white,
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  if (alert['message'] != null &&
                                      alert['message'].toString().isNotEmpty) ...[
                                    const SizedBox(height: 12),
                                    Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: Colors.grey[100],
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        alert['message'],
                                        style: const TextStyle(fontSize: 14),
                                      ),
                                    ),
                                  ],
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      if (alert['distance_meters'] != null)
                                        _buildInfoItem(
                                          Icons.straighten,
                                          '${_parseDouble(alert['distance_meters']).toStringAsFixed(1)} m',
                                          Colors.blue,
                                        ),
                                      if (alert['zone_name'] != null) ...[
                                        if (alert['distance_meters'] != null)
                                          const SizedBox(width: 16),
                                        _buildInfoItem(
                                          Icons.location_on,
                                          alert['zone_name'],
                                          Colors.green,
                                        ),
                                      ],
                                    ],
                                  ),
                                  if (alert['triggered_at'] != null) ...[
                                    const SizedBox(height: 8),
                                    Text(
                                      _formatDateTime(alert['triggered_at']),
                                      style: TextStyle(
                                        color: Colors.grey[600],
                                        fontSize: 11,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ),
        ),
      ],
    );
  }

  Widget _buildFilterChip(String value, String label, IconData icon) {
    final isSelected = _selectedFilter == value;
    return FilterChip(
      label: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon,
              size: 16, color: isSelected ? Colors.white : customOrange),
          const SizedBox(width: 4),
          Text(label),
        ],
      ),
      selected: isSelected,
      onSelected: (selected) {
        setState(() {
          _selectedFilter = value;
        });
        _loadAlerts(refresh: true);
      },
      selectedColor: customOrange,
      checkmarkColor: Colors.white,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : Colors.black87,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
      ),
    );
  }

  Widget _buildInfoItem(IconData icon, String text, Color color) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 16, color: color),
        const SizedBox(width: 4),
        Text(
          text,
          style: TextStyle(
            fontSize: 12,
            color: Colors.grey[700],
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }

  String _formatDateTime(String dateTimeString) {
    try {
      final dateTime = DateTime.parse(dateTimeString);
      final now = DateTime.now();
      final difference = now.difference(dateTime);

      if (difference.inDays > 0) {
        return '${difference.inDays} day${difference.inDays > 1 ? 's' : ''} ago';
      } else if (difference.inHours > 0) {
        return '${difference.inHours} hour${difference.inHours > 1 ? 's' : ''} ago';
      } else if (difference.inMinutes > 0) {
        return '${difference.inMinutes} minute${difference.inMinutes > 1 ? 's' : ''} ago';
      } else {
        return 'Just now';
      }
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

