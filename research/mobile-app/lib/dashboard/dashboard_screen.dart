import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:fl_chart/fl_chart.dart';
import 'package:provider/provider.dart';
import '../config.dart';
import 'profile_info.dart';
import 'change_password.dart';
import '../main.dart';
import 'mobile_detections_screen.dart';
import 'mobile_sessions_screen.dart';
import 'mobile_alerts_screen.dart';
import '../services/locale_service.dart';
import '../screens/language_selection_screen.dart';

const Color customOrange = Color(0xFF129166); // HEC-Sense brand color

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({Key? key}) : super(key: key);

  @override
  _DashboardScreenState createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final _storage = const FlutterSecureStorage();
  int _selectedIndex = 0;
  bool _isLoading = true;
  Map<String, dynamic>? _dashboardData;

  @override
  void initState() {
    super.initState();
    _loadDashboardData();
  }

  Future<void> _loadDashboardData() async {
    if (!mounted) return;
    setState(() => _isLoading = true);
    try {
      final response = await http.get(
        Uri.parse('${Config.baseUrl}/mobile/dashboard/stats'),
        headers: {'Accept': 'application/json'},
      );

      if (!mounted) return;
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (mounted) {
          setState(() {
            _dashboardData = data['data'];
            _isLoading = false;
          });
        }
      } else {
        if (mounted) {
          final localeService = Provider.of<LocaleService>(context, listen: false);
          final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
          setState(() => _isLoading = false);
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(t('failed_to_load_dashboard'))),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        final localeService = Provider.of<LocaleService>(context, listen: false);
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('${t('error')}: $e')),
        );
      }
    }
  }

  Future<void> _logout(BuildContext context) async {
    await _storage.deleteAll();
    if (mounted) {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (context) => const MyApp()),
        (route) => false,
      );
    }
  }

  void _handleMenuSelection(String choice, BuildContext context) {
    final localeService = Provider.of<LocaleService>(context, listen: false);
    final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
    
    if (choice == t('profile_info')) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const ProfileInfoScreen()),
      );
    } else if (choice == t('change_password')) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const ChangePasswordScreen()),
      );
    } else if (choice == t('language')) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const LanguageSelectionScreen(isFirstTime: false)),
      );
    } else if (choice == t('logout')) {
      _logout(context);
    }
  }

  void _onTabSelected(int index) {
    setState(() => _selectedIndex = index);
  }

  List<String> _getScreenTitles(BuildContext context) {
    final localeService = Provider.of<LocaleService>(context, listen: false);
    final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
    
    return [
      t('dashboard'),
      t('detections'),
      t('sessions'),
      t('alerts'),
    ];
  }

  @override
  Widget build(BuildContext context) {
    final List<Widget> screens = [
      _buildDashboardScreen(),
      const MobileDetectionsScreen(),
      const MobileSessionsScreen(),
      const MobileAlertsScreen(),
    ];

    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        final screenTitles = _getScreenTitles(context);
        
        return Scaffold(
      appBar: AppBar(
        title: Text(
          screenTitles[_selectedIndex],
          style: const TextStyle(color: Colors.white),
        ),
        backgroundColor: customOrange,
        automaticallyImplyLeading: false,
        actions: [
          if (_selectedIndex == 0)
            IconButton(
              icon: const Icon(Icons.refresh, color: Colors.white),
              onPressed: _loadDashboardData,
            ),
          PopupMenuButton<String>(
            onSelected: (choice) => _handleMenuSelection(choice, context),
            icon: const Icon(Icons.person, color: Colors.white),
            itemBuilder: (BuildContext context) => [
              PopupMenuItem<String>(value: t('profile_info'), child: Text(t('profile_info'))),
              PopupMenuItem<String>(value: t('change_password'), child: Text(t('change_password'))),
              PopupMenuItem<String>(value: t('language'), child: Text(t('language'))),
              PopupMenuItem<String>(value: t('logout'), child: Text(t('logout'))),
            ],
          ),
        ],
      ),
      body: _isLoading && _selectedIndex == 0
          ? const Center(child: CircularProgressIndicator())
          : screens[_selectedIndex],
      bottomNavigationBar: BottomNavigationBar(
        items: [
          BottomNavigationBarItem(icon: const Icon(Icons.dashboard_outlined), label: t('dashboard')),
          BottomNavigationBarItem(icon: const Icon(Icons.visibility_outlined), label: t('detections')),
          BottomNavigationBarItem(icon: const Icon(Icons.video_library_outlined), label: t('sessions')),
          BottomNavigationBarItem(icon: const Icon(Icons.notifications_active_outlined), label: t('alerts')),
        ],
        currentIndex: _selectedIndex,
        selectedItemColor: Colors.white,
        unselectedItemColor: Colors.white70,
        backgroundColor: customOrange,
        onTap: _onTabSelected,
        type: BottomNavigationBarType.fixed,
      ),
        );
      },
    );
  }

  Widget _buildDashboardScreen() {
    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        
        if (_dashboardData == null) {
          return Center(child: Text(t('no_data')));
        }

        return RefreshIndicator(
          onRefresh: _loadDashboardData,
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Statistics Cards
                _buildStatsCards(context),
                const SizedBox(height: 24),
                
                // Behavior Distribution Chart
                _buildBehaviorChart(context),
                const SizedBox(height: 24),
                
                // Sessions Per Day Chart
                _buildSessionsPerDayChart(context),
                const SizedBox(height: 24),
                
                // Detections Per Hour Chart
                _buildDetectionsPerHourChart(context),
                const SizedBox(height: 24),
                
                // Alert Types Chart
                _buildAlertTypesChart(context),
                const SizedBox(height: 24),
                
                // Recent Sessions
                _buildRecentSessions(context),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildStatsCards(BuildContext context) {
    final stats = _dashboardData!;
    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        
        return Column(
          children: [
            Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    t('total_sessions'),
                    stats['total_sessions'].toString(),
                    Icons.video_library,
                    Colors.blue,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildStatCard(
                    t('detections'),
                    stats['total_detections'].toString(),
                    Icons.visibility,
                    Colors.green,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    t('active_sessions'),
                    stats['active_sessions'].toString(),
                    Icons.play_circle,
                    Colors.orange,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildStatCard(
                    t('total_alerts'),
                    stats['total_alerts'].toString(),
                    Icons.notifications,
                    Colors.red,
                  ),
                ),
              ],
            ),
          ],
        );
      },
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

  Widget _buildBehaviorChart(BuildContext context) {
    final behaviorStats = _dashboardData!['behavior_stats'] as Map<String, dynamic>;
    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        
        return Card(
          elevation: 2,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  t('behavior_distribution'),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  height: 250,
                  child: _buildBehaviorPieChart(behaviorStats, context),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildBehaviorPieChart(Map<String, dynamic> stats, BuildContext context) {
    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        final calm = _parseDouble(stats['calm']);
        final warning = _parseDouble(stats['warning']);
        final aggressive = _parseDouble(stats['aggressive']);
        final total = calm + warning + aggressive;

        if (total == 0) {
          return Center(child: Text(t('no_data')));
        }

        return Row(
          children: [
            Expanded(
              flex: 2,
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
            Expanded(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _buildLegendItem(t('calm'), calm.toInt(), const Color(0xFF4BC0C0)),
                  const SizedBox(height: 12),
                  _buildLegendItem(t('warning'), warning.toInt(), const Color(0xFFFFCE56)),
                  const SizedBox(height: 12),
                  _buildLegendItem(t('aggressive'), aggressive.toInt(), const Color(0xFFFF6384)),
                ],
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildLegendItem(String label, int value, Color color) {
    return Row(
      children: [
        Container(width: 16, height: 16, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
              Text('$value', style: TextStyle(fontSize: 10, color: Colors.grey[600])),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildSessionsPerDayChart(BuildContext context) {
    final sessionsPerDay = _dashboardData!['sessions_per_day'] as List;
    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        
        return Card(
          elevation: 2,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  t('sessions_per_day'),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  height: 250,
                  child: _buildBarChart(sessionsPerDay, 'count', 'date', context),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildDetectionsPerHourChart(BuildContext context) {
    final detectionsPerHour = _dashboardData!['detections_per_hour'] as List;
    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        
        return Card(
          elevation: 2,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  t('detections_per_hour'),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  height: 250,
                  child: _buildBarChart(detectionsPerHour, 'count', 'hour', context),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildBarChart(List data, String valueKey, String labelKey, BuildContext context) {
    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        
        if (data.isEmpty) {
          return Center(child: Text(t('no_data')));
        }

        final barGroups = data.asMap().entries.map((entry) {
      final index = entry.key;
      final item = entry.value;
      final value = _parseDouble(item[valueKey]);
      return BarChartGroupData(
        x: index,
        barRods: [
          BarChartRodData(
            toY: value,
            color: customOrange,
            width: 20,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(4)),
          ),
        ],
      );
        }).toList();

        final maxValue = data.map((e) => _parseDouble(e[valueKey])).reduce((a, b) => a > b ? a : b);

        return BarChart(
      BarChartData(
        barGroups: barGroups,
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
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 30,
              getTitlesWidget: (value, meta) {
                if (value.toInt() >= 0 && value.toInt() < data.length) {
                  final label = data[value.toInt()][labelKey].toString();
                  // Show every nth label to avoid crowding
                  if (data.length <= 7 || value.toInt() % 2 == 0) {
                    return Padding(
                      padding: const EdgeInsets.only(top: 8.0),
                      child: Text(
                        label.length > 10 ? label.substring(0, 10) : label,
                        style: const TextStyle(fontSize: 10),
                        textAlign: TextAlign.center,
                      ),
                    );
                  }
                }
                return const Text('');
              },
            ),
          ),
          topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          ),
          gridData: FlGridData(show: true, drawVerticalLine: false),
          borderData: FlBorderData(show: true),
          maxY: maxValue * 1.1,
          alignment: BarChartAlignment.spaceAround,
        ),
        );
      },
    );
  }

  Widget _buildAlertTypesChart(BuildContext context) {
    final alertTypes = _dashboardData!['alert_types'] as Map<String, dynamic>;
    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        
        return Card(
          elevation: 2,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  t('alert_types_distribution'),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  height: 250,
                  child: _buildAlertPieChart(alertTypes, context),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildAlertPieChart(Map<String, dynamic> alertTypes, BuildContext context) {
    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        
        if (alertTypes.isEmpty) {
          return Center(child: Text(t('no_alerts_data')));
        }

        final entries = alertTypes.entries.toList();
        final colors = [
          const Color(0xFFFF6384),
          const Color(0xFF36A2EB),
          const Color(0xFFFFCE56),
          const Color(0xFF4BC0C0),
          const Color(0xFF9966FF),
        ];
        
        final total = entries.fold<double>(0, (sum, entry) => sum + (entry.value as num).toDouble());

        if (total == 0) {
          return Center(child: Text(t('no_alerts_data')));
        }

        return Row(
          children: [
            Expanded(
              flex: 2,
              child: PieChart(
                PieChartData(
                  sections: entries.asMap().entries.map((entry) {
                    final index = entry.key;
                    final type = entry.value.key;
                    final count = _parseDouble(entry.value.value);
                    return PieChartSectionData(
                      value: count,
                      title: '${((count / total) * 100).toStringAsFixed(0)}%',
                      color: colors[index % colors.length],
                      radius: 80,
                      titleStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white),
                    );
                  }).toList(),
                  sectionsSpace: 2,
                  centerSpaceRadius: 40,
                ),
              ),
            ),
            Expanded(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: entries.asMap().entries.map((entry) {
                  final index = entry.key;
                  final type = entry.value.key;
                  final count = entry.value.value;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 12.0),
                    child: _buildLegendItem(
                      type.toString().replaceAll('_', ' '),
                      _parseInt(entry.value.value),
                      colors[index % colors.length],
                    ),
                  );
                }).toList(),
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildRecentSessions(BuildContext context) {
    final recentSessions = _dashboardData!['recent_sessions'] as List;
    return Consumer<LocaleService>(
      builder: (context, localeService, child) {
        final t = (String key) => LocaleService.translate(key, localeService.locale.languageCode);
        
        return Card(
          elevation: 2,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      t('recent_sessions'),
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    TextButton(
                      onPressed: () {
                        setState(() => _selectedIndex = 2);
                      },
                      child: Text(t('view_all')),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: recentSessions.length > 5 ? 5 : recentSessions.length,
                  itemBuilder: (context, index) {
                    final session = recentSessions[index];
                    return ListTile(
                      leading: const Icon(Icons.video_library, color: customOrange),
                      title: Text(
                        session['session_name'] ?? '${t('session')} #${session['id']}',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text(
                        '${session['source_type']} • ${session['status']}',
                      ),
                      trailing: Text(
                        session['total_frames'] != null 
                            ? '${session['total_frames']} ${t('frames')}' 
                            : '',
                        style: const TextStyle(fontSize: 12),
                      ),
                      onTap: () {
                        // Navigate to session detail
                      },
                    );
                  },
                ),
              ],
            ),
          ),
        );
      },
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

  int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is double) return value.toInt();
    if (value is String) {
      return int.tryParse(value) ?? 0;
    }
    return 0;
  }
}
