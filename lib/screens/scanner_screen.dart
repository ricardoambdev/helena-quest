import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:provider/provider.dart';
import '../providers/stage_provider.dart';
import '../config/theme.dart';

class ScannerScreen extends StatefulWidget {
  const ScannerScreen({super.key});

  @override
  State<ScannerScreen> createState() => _ScannerScreenState();
}

class _ScannerScreenState extends State<ScannerScreen> {
  final MobileScannerController _controller = MobileScannerController();
  bool _processing = false;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) async {
    if (_processing) return;

    final barcode = capture.barcodes.firstOrNull;
    if (barcode?.rawValue == null) return;

    setState(() => _processing = true);

    final stageProv = context.read<StageProvider>();
    final stageId = stageProv.currentStage?['id'] as String?;
    if (stageId == null) {
      _showError('Nenhuma etapa ativa');
      setState(() => _processing = false);
      return;
    }

    final result = await stageProv.validateQr(stageId, barcode!.rawValue!);

    if (!mounted) return;

    if (result['success'] == true) {
      Navigator.pushNamed(context, '/stage');
    } else {
      _showError(result['message'] as String? ?? 'QR Code inv\u00E1lido');
      setState(() => _processing = false);
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg, style: const TextStyle(fontFamily: 'Nunito')),
        backgroundColor: AppTheme.error,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.ink,
      body: Stack(
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: _onDetect,
          ),

          Center(
            child: Container(
              width: 250,
              height: 250,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppTheme.ignite, width: 3),
              ),
            ),
          ),

          Positioned(
            top: MediaQuery.of(context).padding.top + 8,
            left: 16,
            child: SizedBox(
              height: 44,
              child: ElevatedButton.icon(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.arrow_back, size: 20),
                label: const Text(
                  'VOLTAR',
                  style: TextStyle(fontFamily: 'JetBrains Mono', fontSize: 13),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.ink.withValues(alpha: 0.75),
                  foregroundColor: Colors.white,
                  elevation: 0,
                  side: const BorderSide(color: AppTheme.rule, width: 0.5),
                ),
              ),
            ),
          ),

          if (_processing)
            Container(
              color: Colors.black54,
              child: const Center(
                child: CircularProgressIndicator(color: AppTheme.ignite),
              ),
            ),
        ],
      ),
    );
  }
}
