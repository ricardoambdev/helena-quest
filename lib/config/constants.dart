class AppConstants {
  AppConstants._();

  // Mudar para URL de produção quando em deploy
  static const String baseUrl = 'http://127.0.0.1:8000/api';
  static const String wsUrl = 'ws://127.0.0.1:8080';

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
