import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/constants.dart';

class ApiException implements Exception {
  final int statusCode;
  final String message;
  ApiException(this.statusCode, this.message);

  @override
  String toString() => message;
}

class ApiService {
  static const String _tokenKey = 'auth_token';
  String? _token;

  Future<void> setToken(String token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  Future<void> clearToken() async {
    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }

  Future<String?> loadToken() async {
    if (_token != null) return _token;
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);
    return _token;
  }

  Future<bool> hasToken() async {
    final t = await loadToken();
    return t != null && t.isNotEmpty;
  }

  Map<String, String> _headers({bool withAuth = true}) {
    final h = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (withAuth && _token != null) {
      h['Authorization'] = 'Bearer $_token';
    }
    return h;
  }

  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, String>? query,
    bool withAuth = true,
  }) async {
    final uri = Uri.parse('${AppConstants.baseUrl}$path')
        .replace(queryParameters: query);
    final response = await http
        .get(uri, headers: _headers(withAuth: withAuth))
        .timeout(AppConstants.requestTimeout);
    return _handle(response);
  }

  Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? body,
    bool withAuth = true,
  }) async {
    final uri = Uri.parse('${AppConstants.baseUrl}$path');
    final response = await http
        .post(uri, headers: _headers(withAuth: withAuth), body: jsonEncode(body))
        .timeout(AppConstants.requestTimeout);
    return _handle(response);
  }

  Future<Map<String, dynamic>> postMultipart(
    String path, {
    required String field,
    required File file,
    Map<String, String>? fields,
    bool withAuth = true,
  }) async {
    final uri = Uri.parse('${AppConstants.baseUrl}$path');
    final request = http.MultipartRequest('POST', uri);
    request.headers.addAll(_headers(withAuth: withAuth));
    request.files.add(await http.MultipartFile.fromPath(field, file.path));
    if (fields != null) request.fields.addAll(fields);
    final streamed = await request.send().timeout(AppConstants.uploadTimeout);
    final response = await http.Response.fromStream(streamed);
    return _handle(response);
  }

  Map<String, dynamic> _handle(http.Response response) {
    final body = response.body.isNotEmpty
        ? jsonDecode(response.body) as Map<String, dynamic>
        : <String, dynamic>{};

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    }

    final msg = body['message'] as String? ?? 'Erro na requisição';
    throw ApiException(response.statusCode, msg);
  }
}
