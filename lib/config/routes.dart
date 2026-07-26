import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../screens/login_screen.dart';
import '../screens/home_screen.dart';
import '../screens/scanner_screen.dart';
import '../screens/stage_screen.dart';
import '../screens/photo_screen.dart';
import '../screens/answer_screen.dart';
import '../screens/result_screen.dart';
import '../screens/audio_screen.dart';
import '../screens/map_screen.dart';
import '../screens/final_enigma_screen.dart';
import '../screens/final_enigma_scan_screen.dart';
import '../screens/profile_screen.dart';

class AppRoutes {
  AppRoutes._();

  static const String login = '/login';
  static const String home = '/home';
  static const String scanner = '/scanner';
  static const String stage = '/stage';
  static const String photo = '/photo';
  static const String answer = '/answer';
  static const String result = '/result';
  static const String audio = '/audio';
  static const String map = '/map';
  static const String finalEnigma = '/final-enigma';
  static const String finalEnigmaScan = '/final-enigma/scan';
  static const String profile = '/profile';

  static Map<String, WidgetBuilder> get routes => {
    login: (_) => const LoginScreen(),
    home: (_) => const HomeScreen(),
    scanner: (_) => const ScannerScreen(),
    stage: (_) => const StageScreen(),
    photo: (_) => const PhotoScreen(),
    answer: (_) => const AnswerScreen(),
    result: (_) => const ResultScreen(),
    audio: (_) => const AudioScreen(),
    map: (_) => const MapScreen(),
    finalEnigma: (ctx) => FinalEnigmaScreen(apiService: ctx.read<ApiService>()),
    finalEnigmaScan: (_) => const FinalEnigmaScanScreen(),
    profile: (_) => const ProfileScreen(),
  };
}
