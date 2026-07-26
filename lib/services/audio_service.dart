import 'dart:io';
import 'package:audioplayers/audioplayers.dart';
import 'api_service.dart';

class AudioService {
  final ApiService _api;
  final AudioPlayer _player = AudioPlayer();

  AudioService(this._api);

  Future<Map<String, dynamic>> upload(File file) async {
    return await _api.postMultipart(
      '/audios',
      field: 'audio',
      file: file,
    );
  }

  Future<List<Map<String, dynamic>>> list() async {
    final data = await _api.get('/audios');
    return (data['audios'] as List? ?? [])
        .cast<Map<String, dynamic>>();
  }

  Future<void> playUrl(String url) async {
    await _player.stop();
    await _player.play(UrlSource(url));
  }

  Future<void> stopPlayback() async {
    await _player.stop();
  }

  void dispose() {
    _player.dispose();
  }
}
