import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../config.dart';
import 'mobile_detection_detail_screen.dart';

const Color customOrange = Color(0xFF129166);

class MobileDetectionsScreen extends StatefulWidget {
  const MobileDetectionsScreen({Key? key}) : super(key: key);

  @override
  _MobileDetectionsScreenState createState() => _MobileDetectionsScreenState();
}

class _MobileDetectionsScreenState extends State<MobileDetectionsScreen> {
  bool _isLoading = true;
  List<dynamic> _detections = [];
  int _currentPage = 1;
  bool _hasMore = true;
  String _selectedFilter = 'all'; // all, calm, warning, aggressive
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _loadDetections();
  }

  Future<void> _loadDetections({bool refresh = false}) async {
    if (refresh) {
      setState(() {
        _currentPage = 1;
        _hasMore = true;
        _detections = [];
      });
    }

    setState(() => _isLoading = true);
    try {
      String url = '${Config.baseUrl}/mobile/detections?page=$_currentPage';
      if (_selectedFilter != 'all') {
        url += '&behavior=$_selectedFilter';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          if (data['data']['data'] != null) {
            final newDetections = data['data']['data'] as List;
            if (refresh) {
              _detections = newDetections;
            } else {
              _detections.addAll(newDetections);
            }
            _hasMore = data['data']['next_page_url'] != null;
          } else {
            _detections = [];
            _hasMore = false;
          }
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Failed to load detections')),
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

  IconData _getBehaviorIcon(String behavior) {
    switch (behavior.toLowerCase()) {
      case 'aggressive':
        return Icons.warning;
      case 'warning':
        return Icons.info;
      case 'calm':
        return Icons.check_circle;
      default:
        return Icons.help;
    }
  }

  List<dynamic> get _filteredDetections {
    if (_searchQuery.isEmpty) {
      return _detections;
    }
    return _detections.where((detection) {
      final sessionName = (detection['session_name'] ?? '').toString().toLowerCase();
      final trackId = (detection['track_id'] ?? '').toString();
      final query = _searchQuery.toLowerCase();
      return sessionName.contains(query) || trackId.contains(query);
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
                  hintText: 'Search by session or track ID...',
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
                    _buildFilterChip('calm', 'Calm', Icons.check_circle),
                    const SizedBox(width: 8),
                    _buildFilterChip('warning', 'Warning', Icons.info),
                    const SizedBox(width: 8),
                    _buildFilterChip('aggressive', 'Aggressive', Icons.warning),
                  ],
                ),
              ),
            ],
          ),
        ),
        
        // Detections List
        Expanded(
          child: _isLoading && _detections.isEmpty
              ? const Center(child: CircularProgressIndicator())
              : _filteredDetections.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.visibility_outlined, size: 80, color: Colors.grey[400]),
                          const SizedBox(height: 16),
                          Text(
                            _searchQuery.isNotEmpty
                                ? 'No detections found'
                                : 'No detections available',
                            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 10),
                          if (_searchQuery.isEmpty)
                            ElevatedButton(
                              onPressed: () => _loadDetections(refresh: true),
                              child: const Text('Retry'),
                            ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () => _loadDetections(refresh: true),
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16.0),
                        itemCount: _filteredDetections.length + (_hasMore ? 1 : 0),
                        itemBuilder: (context, index) {
                          if (index == _filteredDetections.length) {
                            if (_hasMore && !_isLoading) {
                              _currentPage++;
                              _loadDetections();
                            }
                            return const Center(
                              child: Padding(
                                padding: EdgeInsets.all(16.0),
                                child: CircularProgressIndicator(),
                              ),
                            );
                          }

                          final detection = _filteredDetections[index];
                          final behavior = detection['behavior'] ?? 'unknown';
                          final behaviorColor = _getBehaviorColor(behavior);
                          final behaviorIcon = _getBehaviorIcon(behavior);

                          return Card(
                            elevation: 2,
                            margin: const EdgeInsets.only(bottom: 12),
                            child: InkWell(
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => MobileDetectionDetailScreen(detection: detection),
                                  ),
                                );
                              },
                              child: Padding(
                                padding: const EdgeInsets.all(16.0),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        CircleAvatar(
                                          backgroundColor: behaviorColor.withOpacity(0.2),
                                          child: Icon(behaviorIcon, color: behaviorColor, size: 20),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                detection['session_name'] ?? 'Session #${detection['session_id']}',
                                                style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 16,
                                                ),
                                              ),
                                              const SizedBox(height: 4),
                                              Text(
                                                'Track #${detection['track_id']} • Frame ${detection['frame_number'] ?? 'N/A'}',
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
                                            color: behaviorColor,
                                            borderRadius: BorderRadius.circular(12),
                                          ),
                                          child: Text(
                                            behavior.toUpperCase(),
                                            style: const TextStyle(
                                              color: Colors.white,
                                              fontSize: 11,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 12),
                                    Row(
                                      children: [
                                        _buildInfoItem(
                                          Icons.speed,
                                          '${_parseDouble(detection['speed_kmph']).toStringAsFixed(1)} km/h',
                                          Colors.blue,
                                        ),
                                        const SizedBox(width: 16),
                                        _buildInfoItem(
                                          Icons.star,
                                          '${_parseDouble(detection['confidence']).toStringAsFixed(0)}%',
                                          Colors.amber,
                                        ),
                                        if (detection['alert_triggered'] == true) ...[
                                          const SizedBox(width: 16),
                                          _buildInfoItem(
                                            Icons.notifications_active,
                                            'Alert',
                                            Colors.red,
                                          ),
                                        ],
                                      ],
                                    ),
                                    if (detection['detected_at'] != null) ...[
                                      const SizedBox(height: 8),
                                      Text(
                                        _formatDateTime(detection['detected_at']),
                                        style: TextStyle(
                                          color: Colors.grey[600],
                                          fontSize: 11,
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
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
          Icon(icon, size: 16, color: isSelected ? Colors.white : customOrange),
          const SizedBox(width: 4),
          Text(label),
        ],
      ),
      selected: isSelected,
      onSelected: (selected) {
        setState(() {
          _selectedFilter = value;
        });
        _loadDetections(refresh: true);
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
