import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import 'package:helena_quest_app/providers/stage_provider.dart';
import 'package:helena_quest_app/config/theme.dart';

class PhotoScreen extends StatefulWidget {
  const PhotoScreen({super.key});

  @override
  State<PhotoScreen> createState() => _PhotoScreenState();
}

class _PhotoScreenState extends State<PhotoScreen> {
  File? _photo;
  bool _uploading = false;

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final xfile = await picker.pickImage(source: ImageSource.camera);
    if (xfile != null) {
      setState(() => _photo = File(xfile.path));
    }
  }

  Future<void> _sendPhoto() async {
    if (_photo == null) return;
    setState(() => _uploading = true);

    final stage = context.read<StageProvider>();
    final stageId = stage.currentStage?['id']?.toString();
    if (stageId == null) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Nenhuma etapa ativa.')),
        );
      }
      setState(() => _uploading = false);
      return;
    }

    final result = await stage.sendPhoto(stageId, _photo!);

    if (!mounted) return;
    setState(() => _uploading = false);

    if (result['success'] == true || result['success'] == null) {
      Navigator.pushReplacementNamed(context, '/answer');
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Erro ao enviar foto.'),
          backgroundColor: AppTheme.error,
        ),
      );
    }
  }

  @override
  void initState() {
    super.initState();
    _pickImage();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.ink,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppTheme.paper),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'FOTO',
          style: TextStyle(
            fontFamily: 'JetBrains Mono',
            fontWeight: FontWeight.w500,
            color: AppTheme.paper,
          ),
        ),
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (_photo != null)
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.file(
                    _photo!,
                    height: 360,
                    width: double.infinity,
                    fit: BoxFit.cover,
                  ),
                )
              else
                Container(
                  height: 360,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppTheme.chalk, width: 2),
                  ),
                  child: const Center(
                    child: CircularProgressIndicator(color: AppTheme.ignite),
                  ),
                ),
              const SizedBox(height: 32),
              if (_uploading)
                const Column(
                  children: [
                    CircularProgressIndicator(color: AppTheme.ignite),
                    SizedBox(height: 12),
                    Text(
                      'ENVIANDO...',
                      style: TextStyle(
                        fontFamily: 'JetBrains Mono',
                        color: AppTheme.chalk,
                      ),
                    ),
                  ],
                )
              else ...[
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _photo != null ? _sendPhoto : null,
                    icon: const Icon(Icons.cloud_upload),
                    label: const Text('ENVIAR FOTO'),
                  ),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: _pickImage,
                    icon: const Icon(Icons.camera_alt),
                    label: const Text('TIRAR NOVAMENTE'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.paper,
                      side: const BorderSide(color: AppTheme.paper),
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
