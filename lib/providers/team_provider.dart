import 'package:flutter/foundation.dart';
import '../services/api_service.dart';

class TeamProvider extends ChangeNotifier {
  final ApiService _api;

  Map<String, dynamic>? _team;
  Map<String, dynamic>? _ranking;
  List<Map<String, dynamic>> _progress = [];

  TeamProvider(this._api);

  Map<String, dynamic>? get team => _team;
  Map<String, dynamic>? get ranking => _ranking;
  List<Map<String, dynamic>> get progress => _progress;

  Future<void> loadTeam() async {
    try {
      final data = await _api.get('/auth/me');
      _team = data['team'] as Map<String, dynamic>?;
      notifyListeners();
    } catch (_) {}
  }

  Future<void> loadRanking(String competitionId) async {
    try {
      final data = await _api.get('/public/ranking/$competitionId', withAuth: false);
      _ranking = data;
      notifyListeners();
    } catch (_) {}
  }

  Future<void> loadProgress(String competitionId) async {
    try {
      final data = await _api.get('/public/progress/$competitionId', withAuth: false);
      _progress = (data['teams'] as List? ?? []).cast<Map<String, dynamic>>();
      notifyListeners();
    } catch (_) {}
  }
}
