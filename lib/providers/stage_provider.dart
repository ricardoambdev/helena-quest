import 'dart:io';
import 'package:flutter/foundation.dart';
import '../services/api_service.dart';
import '../services/location_service.dart';

class StageProvider extends ChangeNotifier {
  final ApiService _api;
  final LocationService _location;

  Map<String, dynamic>? _currentStage;
  Map<String, dynamic>? _currentProgress;
  List<Map<String, dynamic>> _hints = [];
  String? _error;
  bool _loading = false;

  StageProvider(this._api, this._location);

  Map<String, dynamic>? get currentStage => _currentStage;
  Map<String, dynamic>? get currentProgress => _currentProgress;
  List<Map<String, dynamic>> get hints => _hints;
  String? get error => _error;
  bool get loading => _loading;

  Future<void> loadCurrentStage() async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      final data = await _api.get('/stages/current');
      _currentStage = data['stage'] as Map<String, dynamic>?;
      _currentProgress = data['progress'] as Map<String, dynamic>?;
    } on ApiException catch (e) {
      _error = e.message;
    } catch (e) {
      _error = 'Erro ao carregar etapa atual';
    }

    _loading = false;
    notifyListeners();
  }

  Future<Map<String, dynamic>> validateQr(String stageId, String qrUuid) async {
    _error = null;
    double? lat;
    double? lng;

    final pos = await _location.getCurrentPosition();
    if (pos != null) {
      lat = pos.latitude;
      lng = pos.longitude;
    }

    try {
      final data = await _api.post('/stages/$stageId/validate-qr', body: {
        'qr_code_uuid': qrUuid,
        if (lat != null) 'latitude': lat,
        if (lng != null) 'longitude': lng,
      });
      _currentProgress = data['progress'] as Map<String, dynamic>?;
      notifyListeners();
      return data;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return {'success': false, 'message': e.message};
    }
  }

  Future<Map<String, dynamic>> sendPhoto(String stageId, File photo) async {
    _error = null;
    try {
      final data = await _api.postMultipart(
        '/stages/$stageId/send-photo',
        field: 'photo',
        file: photo,
      );
      _currentProgress = data['progress'] as Map<String, dynamic>?;
      notifyListeners();
      return data;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return {'success': false, 'message': e.message};
    }
  }

  Future<Map<String, dynamic>> submitAnswer(String stageId, String answer) async {
    _error = null;
    try {
      final data = await _api.post('/stages/$stageId/answer', body: {
        'answer': answer,
      });
      if (data['correct'] == true) {
        await loadCurrentStage();
      }
      return data;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return {'correct': false, 'fatal': true, 'message': e.message};
    }
  }

  Future<void> loadHints(String stageId) async {
    try {
      final data = await _api.get('/stages/$stageId/hints');
      _hints = (data['hints'] as List? ?? []).cast<Map<String, dynamic>>();
      notifyListeners();
    } catch (_) {}
  }

  Future<Map<String, dynamic>> buyHint(String stageId, String hintId) async {
    try {
      final data = await _api.post('/stages/$stageId/buy-hint/$hintId');
      await loadHints(stageId);
      return data;
    } on ApiException catch (e) {
      return {'success': false, 'message': e.message};
    }
  }
}
