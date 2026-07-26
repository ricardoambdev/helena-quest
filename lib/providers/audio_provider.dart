import 'dart:io';
import 'package:flutter/foundation.dart';
import '../services/api_service.dart';
import '../services/audio_service.dart';

class AudioProvider extends ChangeNotifier {
  final AudioService _audioService;

  List<Map<String, dynamic>> _audios = [];
  bool _recording = false;
  String? _error;

  AudioProvider(this._audioService);

  List<Map<String, dynamic>> get audios => _audios;
  bool get recording => _recording;
  String? get error => _error;

  void setRecording(bool value) {
    _recording = value;
    notifyListeners();
  }

  Future<bool> upload(File file) async {
    _error = null;
    try {
      await _audioService.upload(file);
      await loadAudios();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Erro ao enviar áudio';
      notifyListeners();
      return false;
    }
  }

  Future<void> loadAudios() async {
    try {
      final list = await _audioService.list();
      _audios = list;
      notifyListeners();
    } catch (_) {}
  }
}
