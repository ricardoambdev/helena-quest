import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'config/theme.dart';
import 'config/routes.dart';
import 'services/api_service.dart';
import 'services/auth_service.dart';
import 'services/location_service.dart';
import 'services/audio_service.dart';
import 'services/tts_service.dart';
import 'providers/auth_provider.dart';
import 'providers/stage_provider.dart';
import 'providers/team_provider.dart';
import 'providers/audio_provider.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const HelenaQuestApp());
}

class HelenaQuestApp extends StatelessWidget {
  const HelenaQuestApp({super.key});

  @override
  Widget build(BuildContext context) {
    final apiService = ApiService();
    final authService = AuthService(apiService);
    final locationService = LocationService();
    final audioService = AudioService(apiService);
    final ttsService = TtsService();

    return MultiProvider(
      providers: [
        Provider<ApiService>.value(value: apiService),
        Provider<AuthService>.value(value: authService),
        Provider<LocationService>.value(value: locationService),
        Provider<AudioService>.value(value: audioService),
        Provider<TtsService>.value(value: ttsService),
        ChangeNotifierProvider<AuthProvider>(
          create: (_) => AuthProvider(authService, apiService),
        ),
        ChangeNotifierProvider<StageProvider>(
          create: (_) => StageProvider(apiService, locationService),
        ),
        ChangeNotifierProvider<TeamProvider>(
          create: (_) => TeamProvider(apiService),
        ),
        ChangeNotifierProvider<AudioProvider>(
          create: (_) => AudioProvider(audioService),
        ),
      ],
      child: MaterialApp(
        title: 'Helena Quest',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        initialRoute: '/',
        routes: {
          '/': (_) => const SplashScreen(),
          ...AppRoutes.routes,
        },
      ),
    );
  }
}

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    final auth = context.read<AuthProvider>();
    await auth.tryAutoLogin();

    if (!mounted) return;

    final route = auth.isAuthenticated ? AppRoutes.home : AppRoutes.login;
    Navigator.pushReplacementNamed(context, route);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.ink,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              'HELENA',
              style: Theme.of(context).textTheme.displayLarge?.copyWith(
                color: AppTheme.ignite,
                fontFamily: 'Inter',
                fontWeight: FontWeight.w800,
              ),
            ),
            Text(
              'QUEST',
              style: Theme.of(context).textTheme.displayLarge?.copyWith(
                color: AppTheme.paper,
                fontFamily: 'Inter',
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 48),
            const CircularProgressIndicator(color: AppTheme.ignite),
          ],
        ),
      ),
    );
  }
}
