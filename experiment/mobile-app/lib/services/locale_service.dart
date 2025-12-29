import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class LocaleService extends ChangeNotifier {
  static const _storage = FlutterSecureStorage();
  static const String _languageKey = 'selected_language';
  
  Locale _locale = const Locale('en');
  
  Locale get locale => _locale;
  
  // Available languages
  static const List<Map<String, String>> supportedLanguages = [
    {'code': 'en', 'name': 'English', 'nativeName': 'English'},
    {'code': 'si', 'name': 'Sinhala', 'nativeName': 'සිංහල'},
    {'code': 'ta', 'name': 'Tamil', 'nativeName': 'தமிழ்'},
  ];
  
  LocaleService() {
    _loadLanguage();
  }
  
  Future<void> _loadLanguage() async {
    String? languageCode = await _storage.read(key: _languageKey);
    if (languageCode != null) {
      _locale = Locale(languageCode);
      notifyListeners();
    }
  }
  
  Future<void> setLanguage(String languageCode) async {
    if (supportedLanguages.any((lang) => lang['code'] == languageCode)) {
      _locale = Locale(languageCode);
      await _storage.write(key: _languageKey, value: languageCode);
      notifyListeners();
    }
  }
  
  String getLanguageName(String code) {
    return supportedLanguages.firstWhere(
      (lang) => lang['code'] == code,
      orElse: () => {'name': 'English', 'nativeName': 'English'},
    )['nativeName'] ?? 'English';
  }
  
  static String translate(String key, String languageCode) {
    final translations = _translations[languageCode] ?? _translations['en']!;
    return translations[key] ?? key;
  }
  
  static const Map<String, Map<String, String>> _translations = {
    'en': {
      // Common
      'app_name': 'Elephant Detection System',
      'welcome': 'Welcome to HECSense',
      'get_started': 'Get Started',
      'choose_language': 'Choose Your Language',
      'select_language': 'Select Language',
      'save': 'Save',
      'cancel': 'Cancel',
      'logout': 'Logout',
      'settings': 'Settings',
      'profile_info': 'Profile Info',
      'change_password': 'Change Password',
      'language': 'Language',
      'home': 'Home',
      'dashboard': 'Dashboard',
      'detections': 'Detections',
      'sessions': 'Sessions',
      'alerts': 'Alerts',
      'total_sessions': 'Total Sessions',
      'total_detections': 'Total Detections',
      'total_alerts': 'Total Alerts',
      'behavior_distribution': 'Behavior Distribution',
      'calm': 'Calm',
      'warning': 'Warning',
      'aggressive': 'Aggressive',
      'active': 'Active',
      'all_time': 'All time',
      'recent_sessions': 'Recent Sessions',
      'view_all': 'View All',
      'status': 'Status',
      'running': 'Running',
      'completed': 'Completed',
      'stopped': 'Stopped',
      'error': 'Error',
      'view': 'View',
      'search': 'Search',
      'filter': 'Filter',
      'clear': 'Clear',
      'no_data': 'No data available',
      'loading': 'Loading...',
      'failed_to_load': 'Failed to load data',
      'description': 'AI-powered monitoring to prevent human-elephant conflicts with real-time alerts and smart sensors.',
      'failed_to_load_dashboard': 'Failed to load dashboard data',
      'active_sessions': 'Active Sessions',
      'detections_label': 'Detections',
      'sessions_per_day': 'Sessions Per Day (Last 7 Days)',
      'detections_per_hour': 'Detections Per Hour (Last 24 Hours)',
      'alert_types_distribution': 'Alert Types Distribution',
      'no_alerts_data': 'No alerts data',
      'next': 'Next',
      'ai_elephant_detection': 'AI-Powered Elephant Detection',
      'elephant_detection_desc': 'Detect elephants in real-time using smart cameras and sensors. The system analyzes movement and predicts aggressive actions early to ensure safety.',
      'monitor_manage': 'Monitor & Manage',
      'monitor_manage_desc': 'Access live data, incident reports, and intelligent insights through the mobile dashboard. Analyze patterns to improve safety strategies and protect both humans and elephants.',
      'instant_alerts': 'Instant Alerts',
      'instant_alerts_desc': 'Receive immediate notifications when elephants are detected nearby. Stay informed with real-time alerts on your mobile device to take timely action.',
      'frames': 'frames',
      'session': 'Session',
      'elephant_aggression_detection': 'Elephant Aggression Detection',
      'elephant_aggression_detection_desc': 'Detect early signs of aggressive behavior using AI. The system analyzes posture, speed, trunk and ear movement to predict risk and trigger timely responses.',
      'adaptive_response_distance': 'Adaptive Response by Distance',
      'adaptive_response_distance_desc': 'HEC Sense intelligently measures how close the elephants are. From gentle alerts to loud deterrents — the system reacts smartly based on the threat level.',
      'pending_approval': 'Pending Approval',
      'waitlist_message': 'You are currently on the waitlist',
      'waitlist_description': 'Please wait for the admin to approve your account.',
      'go_back': 'GO BACK',
      'login': 'Login',
      'create_account': 'Create Account',
    },
    'si': {
      // Common
      'app_name': 'ඇත් හඳුනාගැනීමේ පද්ධතිය',
      'welcome': 'HECSense වෙත සාදරයෙන් පිළිගනිමු',
      'get_started': 'ආරම්භ කරන්න',
      'choose_language': 'ඔබගේ භාෂාව තෝරන්න',
      'select_language': 'භාෂාව තෝරන්න',
      'save': 'සුරකින්න',
      'cancel': 'අවලංගු කරන්න',
      'logout': 'ඉවත්වීම',
      'settings': 'සැකසීම්',
      'profile_info': 'පැතිකඩ තොරතුරු',
      'change_password': 'මුරපදය වෙනස් කරන්න',
      'language': 'භාෂාව',
      'home': 'මුල් පිටුව',
      'dashboard': 'උපකරණ පුවරුව',
      'detections': 'හඳුනාගැනීම්',
      'sessions': 'වාර',
      'alerts': 'අනතුරු ඇඟවීම්',
      'total_sessions': 'මුළු වාර',
      'total_detections': 'මුළු හඳුනාගැනීම්',
      'total_alerts': 'මුළු අනතුරු ඇඟවීම්',
      'behavior_distribution': 'හැසිරීම් ව්‍යාප්තිය',
      'calm': 'සන්සුන්',
      'warning': 'අනතුරු ඇඟවීම',
      'aggressive': 'ප්‍රචණ්ඩ',
      'active': 'සක්‍රිය',
      'all_time': 'සියලු කාලය',
      'recent_sessions': 'අළුත් වාර',
      'view_all': 'සියල්ල බලන්න',
      'status': 'තත්වය',
      'running': 'ක්‍රියාත්මක',
      'completed': 'සම්පූර්ණ',
      'stopped': 'නතර කළා',
      'error': 'දෝෂය',
      'view': 'දැක්ම',
      'search': 'සොයන්න',
      'filter': 'පෙරහන',
      'clear': 'සංසිද්ධි',
      'no_data': 'දත්ත නොමැත',
      'loading': 'පූරණය වෙමින්...',
      'failed_to_load': 'දත්ත පූරණය කිරීමට අපොහොසත් විය',
      'description': 'නිශ්චිත කාලීන අනතුරු ඇඟවීම් සහ ස්මාර්ට් සංවේදක සමඟ මිනිස්-ඇත් ගැටුම් වැළැක්වීම සඳහා AI මගින් බල ගන්වා ඇති නිරීක්ෂණය.',
      'failed_to_load_dashboard': 'උපකරණ පුවරුවේ දත්ත පූරණය කිරීමට අපොහොසත් විය',
      'active_sessions': 'සක්‍රිය වාර',
      'detections_label': 'හඳුනාගැනීම්',
      'sessions_per_day': 'දිනකට වාර (පසුගිය දින 7)',
      'detections_per_hour': 'පැයකට හඳුනාගැනීම් (පසුගිය පැය 24)',
      'alert_types_distribution': 'අනතුරු ඇඟවීම් වර්ග ව්‍යාප්තිය',
      'no_alerts_data': 'අනතුරු ඇඟවීම් දත්ත නොමැත',
      'next': 'ඊළඟ',
      'ai_elephant_detection': 'AI මගින් බල ගන්වා ඇති ඇත් හඳුනාගැනීම',
      'elephant_detection_desc': 'ස්මාර්ට් කැමරා සහ සංවේදක භාවිතා කරමින් නිශ්චිත කාලයේ ඇත් හඳුනාගන්න. පද්ධතිය සංචලනය විශ්ලේෂණය කරන අතර ආරක්ෂාව සුරක්ෂිත කිරීම සඳහා මුල් අවදියේ ප්‍රචණ්ඩ ක්‍රියා පුරෝකථනය කරයි.',
      'monitor_manage': 'සුපිරික්ෂාව සහ කළමනාකරණය',
      'monitor_manage_desc': 'ජංගම උපකරණ පුවරුව හරහා සජීවී දත්ත, සිදුවීම් වාර්තා, සහ බුද්ධිමත් අභිප්‍රේරණවලට ප්‍රවේශ වන්න. මිනිසුන් සහ ඇතුන් යන දෙදෙනාම ආරක්ෂා කිරීම සඳහා ආරක්ෂා උපාය මාර්ග වැඩිදියුණු කිරීමට රටා විශ්ලේෂණය කරන්න.',
      'instant_alerts': 'ක්ෂණික අනතුරු ඇඟවීම්',
      'instant_alerts_desc': 'ඇත් හඳුනාගත් විට ක්ෂණික දැනුම්දීම් ලබා ගන්න. කාලීන ක්‍රියාමාර්ග ගැනීම සඳහා ඔබගේ ජංගම උපාංගයේ නිශ්චිත කාලීන අනතුරු ඇඟවීම් සමඟ දැනුවත් වන්න.',
      'frames': 'රූප රාමු',
      'session': 'වාරය',
      'elephant_aggression_detection': 'ඇත් ප්‍රචණ්ඩත්වය හඳුනාගැනීම',
      'elephant_aggression_detection_desc': 'AI භාවිතා කරමින් ප්‍රචණ්ඩ හැසිරීමේ මුල් ලක්ෂණ හඳුනාගන්න. පද්ධතිය ස්ථානය, වේගය, ළම්බය සහ කන චලනය විශ්ලේෂණය කර අවදානම පුරෝකථනය කර කාලීන ප්‍රතිචාර අවුලුවයි.',
      'adaptive_response_distance': 'දුර අනුව අනුවර්තී ප්‍රතිචාර',
      'adaptive_response_distance_desc': 'HEC Sense ඇතුන් කොතරම් ආසන්නද යන්න බුද්ධිමත් ලෙස මනිනවා. සැලකිල්ලෙන් යුත් අනතුරු ඇඟවීම් සිට ශබ්දවත් නැවැත්වීම් දක්වා — පද්ධතිය තර්ජන මට්ටම මත පදනම්ව බුද්ධිමත් ලෙස ප්‍රතිචාර දක්වයි.',
      'pending_approval': 'අනුමත කිරීම සඳහා රඳවා සිටීම',
      'waitlist_message': 'ඔබ දැනට රැඳී සිටීමේ ලැයිස්තුවේ සිටී',
      'waitlist_description': 'කරුණාකර පරිපාලකයා ඔබගේ ගිණුම අනුමත කිරීමට රැඳී සිටින්න.',
      'go_back': 'ආපසු යන්න',
      'login': 'ඇතුළු වන්න',
      'create_account': 'ගිණුමක් සාදන්න',
    },
    'ta': {
      // Common
      'app_name': 'யானை கண்டறிதல் அமைப்பு',
      'welcome': 'HECSense வரவேற்கிறோம்',
      'get_started': 'தொடங்க',
      'choose_language': 'உங்கள் மொழியைத் தேர்ந்தெடுக்கவும்',
      'select_language': 'மொழியைத் தேர்ந்தெடு',
      'save': 'சேமி',
      'cancel': 'ரத்துசெய்',
      'logout': 'வெளியேற',
      'settings': 'அமைப்புகள்',
      'profile_info': 'சுயவிவர தகவல்',
      'change_password': 'கடவுச்சொல்லை ம变更',
      'language': 'மொழி',
      'home': 'முகப்பு',
      'dashboard': 'டாஷ்போர்டு',
      'detections': 'கண்டறிதல்கள்',
      'sessions': 'அமர்வுகள்',
      'alerts': 'எச்சரிக்கைகள்',
      'total_sessions': 'மொத்த அமர்வுகள்',
      'total_detections': 'மொத்த கண்டறிதல்கள்',
      'total_alerts': 'மொத்த எச்சரிக்கைகள்',
      'behavior_distribution': 'நடத்தை பரவல்',
      'calm': 'அமைதி',
      'warning': 'எச்சரிக்கை',
      'aggressive': 'கடுமையான',
      'active': 'செயலில்',
      'all_time': 'அனைத்து நேரம்',
      'recent_sessions': 'சமீபத்திய அமர்வுகள்',
      'view_all': 'அனைத்தையும் காண்க',
      'status': 'நிலை',
      'running': 'இயங்குகிறது',
      'completed': 'நிறைவு',
      'stopped': 'நிறுத்தப்பட்டது',
      'error': 'பிழை',
      'view': 'காண்க',
      'search': 'தேட',
      'filter': 'வடிகட்டி',
      'clear': 'அழிக்க',
      'no_data': 'தரவு கிடைக்கவில்லை',
      'loading': 'ஏற்றுகிறது...',
      'failed_to_load': 'தரவை ஏற்ற முடியவில்லை',
      'description': 'நேரடி எச்சரிக்கைகள் மற்றும் ஸ்மார்ட் சென்சார்களுடன் மனித-யானை மோதல்களைத் தடுக்க AI மூலம் இயக்கப்படும் கண்காணிப்பு.',
      'failed_to_load_dashboard': 'டாஷ்போர்டு தரவை ஏற்ற முடியவில்லை',
      'active_sessions': 'செயலில் உள்ள அமர்வுகள்',
      'detections_label': 'கண்டறிதல்கள்',
      'sessions_per_day': 'நாள் ஒன்றுக்கு அமர்வுகள் (கடந்த 7 நாட்கள்)',
      'detections_per_hour': 'மணிக்கு கண்டறிதல்கள் (கடந்த 24 மணி நேரம்)',
      'alert_types_distribution': 'எச்சரிக்கை வகைகள் பரவல்',
      'no_alerts_data': 'எச்சரிக்கை தரவு இல்லை',
      'next': 'அடுத்து',
      'ai_elephant_detection': 'AI மூலம் இயக்கப்படும் யானை கண்டறிதல்',
      'elephant_detection_desc': 'ஸ்மார்ட் கேமராக்கள் மற்றும் சென்சார்களைப் பயன்படுத்தி நேரடியாக யானைகளைக் கண்டறியுங்கள். கணினி இயக்கத்தை பகுப்பாய்வு செய்து, பாதுகாப்பை உறுதிப்படுத்த ஆரம்ப கட்டத்திலேயே ஆக்கிரமிப்பு நடவடிக்கைகளை கணிக்கிறது.',
      'monitor_manage': 'கண்காணி & நிர்வகி',
      'monitor_manage_desc': 'மொபைல் டாஷ்போர்டு வழியாக நேரடி தரவு, சம்பவ அறிக்கைகள் மற்றும் புத்திசாலித்தனமான நுண்ணறிவுகளுக்கு அணுகலைப் பெறுங்கள். மனிதர்கள் மற்றும் யானைகள் இரண்டையும் பாதுகாக்க பாதுகாப்பு ம strategies ர்களை மேம்படுத்த வடிவங்களை பகுப்பாய்வு செய்யுங்கள்.',
      'instant_alerts': 'உடனடி எச்சரிக்கைகள்',
      'instant_alerts_desc': 'யானைகள் அருகில் கண்டறியப்படும்போது உடனடி அறிவிப்புகளைப் பெறுங்கள். சரியான நேரத்தில் நடவடிக்கை எடுக்க உங்கள் மொபைல் சாதனத்தில் நேரடி எச்சரிக்கைகளுடன் தகவலறிந்திருக்கவும்.',
      'frames': 'பிரேம்கள்',
      'session': 'அமர்வு',
      'elephant_aggression_detection': 'யானை ஆக்கிரமிப்பு கண்டறிதல்',
      'elephant_aggression_detection_desc': 'AI ஐப் பயன்படுத்தி ஆக்கிரமிப்பு நடத்தையின் ஆரம்ப அறிகுறிகளைக் கண்டறியுங்கள். கணினி நிலைப்பாடு, வேகம், துதிக்கை மற்றும் காது இயக்கத்தை பகுப்பாய்வு செய்து அபாயத்தை கணிக்கிறது மற்றும் சரியான நேரத்தில் பதில்களைத் தூண்டுகிறது.',
      'adaptive_response_distance': 'தூரத்தால் தழுவிய பதில்',
      'adaptive_response_distance_desc': 'HEC Sense யானைகள் எவ்வளவு நெருக்கமாக இருக்கின்றன என்பதை அறிவுடன் அளவிடுகிறது. மென்மையான எச்சரிக்கைகள் முதல் உரத்த தடுப்புகள் வரை — கணினி அச்சுறுத்தல் நிலையை அடிப்படையாகக் கொண்டு அறிவுடன் செயல்படுகிறது.',
      'pending_approval': 'அனுமதிக்க நிலுவையில்',
      'waitlist_message': 'நீங்கள் தற்போது காத்திருப்பவர்களின் பட்டியலில் இருக்கிறீர்கள்',
      'waitlist_description': 'நிர்வாகி உங்கள் கணக்கை அனுமதிக்கும் வரை காத்திருங்கள்.',
      'go_back': 'திரும்பிச் செல்',
      'login': 'உள்நுழை',
      'create_account': 'கணக்கை உருவாக்க',
    },
  };
}

