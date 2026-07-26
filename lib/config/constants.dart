class AppConstants {
  AppConstants._();

  // Mudar para URL de produção quando em deploy
  static const String baseUrl = 'http://gincana.test/api';
  static const String wsUrl = 'ws://gincana.test:8080';

  static const Duration requestTimeout = Duration(seconds: 30);
  static const Duration uploadTimeout = Duration(seconds: 120);

  static const int minAnswerDigits = 4;
  static const int maxAnswerDigits = 8;
  static const int maxPhotoSizeMB = 10;
  static const int maxAudioSizeMB = 20;

  static const double mapDefaultLat = -23.5505;
  static const double mapDefaultLng = -46.6333;
  static const double mapDefaultZoom = 15.0;
}
