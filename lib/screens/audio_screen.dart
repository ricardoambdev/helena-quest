import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:helena_quest_app/providers/audio_provider.dart';
import 'package:helena_quest_app/config/theme.dart';

class AudioScreen extends StatefulWidget {
  const AudioScreen({super.key});

  @override
  State<AudioScreen> createState() => _AudioScreenState();
}

class _AudioScreenState extends State<AudioScreen> {
  bool _uploading = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AudioProvider>().loadAudios();
    });
  }

  void _toggleRecording() {
    final audio = context.read<AudioProvider>();
    if (audio.recording) {
      audio.setRecording(false);
      _simulateUpload();
    } else {
      audio.setRecording(true);
    }
  }

  Future<void> _simulateUpload() async {
    setState(() => _uploading = true);
    await Future.delayed(const Duration(seconds: 2));
    if (!mounted) return;

    final audio = context.read<AudioProvider>();
    final tempDir = Directory.systemTemp;
    final tempFile = File('${tempDir.path}/audio_temp.m4a');
    await tempFile.writeAsString('fake-audio-data');

    final success = await audio.upload(tempFile);
    if (!mounted) return;
    setState(() => _uploading = false);

    if (!success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(audio.error ?? 'Erro ao enviar áudio.'),
          backgroundColor: AppTheme.error,
        ),
      );
    }
  }

  String _formatDuration(int? seconds) {
    if (seconds == null) return '--:--';
    final m = (seconds ~/ 60).toString().padLeft(2, '0');
    final s = (seconds % 60).toString().padLeft(2, '0');
    return '$m:$s';
  }

  String _formatDateTime(String? iso) {
    if (iso == null) return '';
    try {
      final dt = DateTime.parse(iso);
      final d = '${dt.day.toString().padLeft(2, '0')}/'
          '${dt.month.toString().padLeft(2, '0')}';
      final t = '${dt.hour.toString().padLeft(2, '0')}:'
          '${dt.minute.toString().padLeft(2, '0')}';
      return '$d $t';
    } catch (_) {
      return iso;
    }
  }

  @override
  Widget build(BuildContext context) {
    final audio = context.watch<AudioProvider>();

    return Scaffold(
      backgroundColor: AppTheme.paper,
      appBar: AppBar(
        backgroundColor: AppTheme.ink,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppTheme.paper),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'ÁUDIO',
          style: TextStyle(
            fontFamily: 'JetBrains Mono',
            fontWeight: FontWeight.w500,
            color: AppTheme.paper,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: AppTheme.paper),
            onPressed: () => audio.loadAudios(),
          ),
        ],
      ),
      body: Column(
        children: [
          // Recording controls
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: audio.recording
                  ? AppTheme.error.withValues(alpha: 0.08)
                  : Colors.white,
              border: Border(
                bottom: BorderSide(color: AppTheme.rule),
              ),
            ),
            child: Column(
              children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: audio.recording
                        ? AppTheme.error
                        : AppTheme.ignite,
                  ),
                  child: IconButton(
                    icon: Icon(
                      audio.recording ? Icons.stop : Icons.mic,
                      color: Colors.white,
                      size: 36,
                    ),
                    onPressed: _uploading ? null : _toggleRecording,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  audio.recording ? 'GRAVANDO...' : 'TOQUE PARA GRAVAR',
                  style: TextStyle(
                    fontFamily: 'JetBrains Mono',
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    color: audio.recording ? AppTheme.error : AppTheme.chalk,
                    letterSpacing: 1,
                  ),
                ),
                if (_uploading) ...[
                  const SizedBox(height: 12),
                  const CircularProgressIndicator(color: AppTheme.ignite),
                  const SizedBox(height: 4),
                  const Text(
                    'ENVIANDO...',
                    style: TextStyle(
                      fontFamily: 'JetBrains Mono',
                      fontSize: 11,
                      color: AppTheme.chalk,
                    ),
                  ),
                ],
              ],
            ),
          ),
          // Audio list
          Expanded(
            child: audio.audios.isEmpty
                ? Center(
                    child: Text(
                      'Nenhum áudio enviado ainda.',
                      style: TextStyle(
                        fontFamily: 'Nunito',
                        color: AppTheme.chalk,
                        fontSize: 15,
                      ),
                    ),
                  )
                : ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: audio.audios.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 8),
                    itemBuilder: (context, index) {
                      final item = audio.audios[index];
                      final duration = item['duration'] as int?;
                      final sentAt = item['sent_at']?.toString();
                      final url = item['url']?.toString();

                      return Card(
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: AppTheme.ignite.withValues(alpha: 0.15),
                            child: IconButton(
                              icon: const Icon(
                                Icons.play_arrow,
                                color: AppTheme.ignite,
                              ),
                              onPressed: url != null ? () {} : null,
                            ),
                          ),
                          title: Text(
                            _formatDuration(duration),
                            style: const TextStyle(
                              fontFamily: 'JetBrains Mono',
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          subtitle: Text(
                            _formatDateTime(sentAt),
                            style: const TextStyle(
                              fontFamily: 'Nunito',
                              fontSize: 12,
                              color: AppTheme.chalk,
                            ),
                          ),
                          trailing: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: AppTheme.ignite.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              _formatDuration(duration),
                              style: const TextStyle(
                                fontFamily: 'JetBrains Mono',
                                fontSize: 11,
                                color: AppTheme.ignite,
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
