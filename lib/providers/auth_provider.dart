import 'package:flutter/foundation.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';

enum AuthStatus { uninitialized, authenticated, unauthenticated }

class AuthProvider extends ChangeNotifier {
  final AuthService _authService;
  final ApiService _apiService;

  AuthStatus _status = AuthStatus.uninitialized;
  Map<String, dynamic>? _team;
  String? _error;

  AuthProvider(this._authService, this._apiService);

  AuthStatus get status => _status;
  Map<String, dynamic>? get team => _team;
  String? get error => _error;
  bool get isAuthenticated => _status == AuthStatus.authenticated;

  Future<void> tryAutoLogin() async {
    final hasToken = await _apiService.hasToken();
    if (!hasToken) {
      _status = AuthStatus.unauthenticated;
      notifyListeners();
      return;
    }

    final valid = await _authService.checkToken();
    if (valid) {
      _status = AuthStatus.authenticated;
      await _loadTeam();
    } else {
      await _apiService.clearToken();
      _status = AuthStatus.unauthenticated;
    }
    notifyListeners();
  }

  Future<bool> login(String username, String password) async {
    _error = null;
    try {
      final data = await _authService.login(username, password);
      _team = data['team'] as Map<String, dynamic>?;
      _status = AuthStatus.authenticated;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _status = AuthStatus.unauthenticated;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Erro de conexão. Verifique sua internet.';
      _status = AuthStatus.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await _authService.logout();
    _team = null;
    _status = AuthStatus.unauthenticated;
    notifyListeners();
  }

  Future<void> _loadTeam() async {
    try {
      final data = await _authService.me();
      _team = data['team'] as Map<String, dynamic>?;
    } catch (_) {}
  }
}
