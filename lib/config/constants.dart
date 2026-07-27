class AppConstants {
  AppConstants._();

  static String get baseUrl {
    const fromDefine = String.fromEnvironment('API_URL');
    if (fromDefine.isNotEmpty) return fromDefine;
    return 'https://washstand-obstinate-demotion.ngrok-free.dev/api';
  }

  static String get wsUrl {
    const fromDefine = String.fromEnvironment('WS_URL');
    if (fromDefine.isNotEmpty) return fromDefine;
    return 'wss://washstand-obstinate-demotion.ngrok-free.dev:8080';
  }

  static const Duration requestTimeout = Duration(seconds: 30);
  static const Duration uploadTimeout = Duration(seconds: 120);

  static const int minAnswerDigits = 4;
  static const int maxAnswerDigits = 8;
  static const int maxPhotoSizeMB = 10;
  static const int maxAudioSizeMB = 20;

  static const double mapDefaultLat = -21.9965;
  static const double mapDefaultLng = -47.4265;
  static const double mapDefaultZoom = 15.0;
}
