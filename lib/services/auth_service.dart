import 'api_service.dart';

class AuthService {
  final ApiService _api;

  AuthService(this._api);

  Future<Map<String, dynamic>> login(String username, String password) async {
    final data = await _api.post('/auth/login', body: {
      'username': username,
      'password': password,
    }, withAuth: false);

    final token = data['token'] as String?;
    if (token != null) {
      await _api.setToken(token);
    }

    return data;
  }

  Future<void> logout() async {
    try {
      await _api.post('/auth/logout');
    } catch (_) {}
    await _api.clearToken();
  }

  Future<Map<String, dynamic>> me() async {
    return await _api.get('/auth/me');
  }

  Future<bool> checkToken() async {
    try {
      await _api.post('/auth/check');
      return true;
    } catch (_) {
      return false;
    }
  }
}
