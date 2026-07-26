import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/stage_provider.dart';
import '../services/tts_service.dart';
import '../config/theme.dart';

class StageScreen extends StatefulWidget {
  final TtsService? ttsService;

  const StageScreen({super.key, this.ttsService});

  @override
  State<StageScreen> createState() => _StageScreenState();
}

class _StageScreenState extends State<StageScreen> {
  TtsService get _tts => widget.ttsService ?? TtsService();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _autoPlayTts();
    });
  }

  void _autoPlayTts() {
    final stage = context.read<StageProvider>().currentStage;
    final text = stage?['narrative_text'] as String?;
    if (text != null && text.isNotEmpty) {
      _tts.speak(text);
    }
  }

  @override
  void dispose() {
    _tts.stop();
    super.dispose();
  }

  void _openHintsSheet() {
    final stageProv = context.read<StageProvider>();
    final stageId = stageProv.currentStage?['id'] as String?;
    if (stageId == null) return;

    stageProv.loadHints(stageId);

    showModalBottomSheet(
      context: context,
      backgroundColor: AppTheme.paper,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return Consumer<StageProvider>(
          builder: (_, sp, _) {
            final hints = sp.hints;
            return Padding(
              padding: const EdgeInsets.fromLTRB(24, 12, 24, 32),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: AppTheme.rule,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'DICAS',
                    style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 16),
                  if (hints.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 16),
                      child: Text('Nenhuma dica dispon\u00EDvel.'),
                    )
                  else
                    ...hints.map(
                      (hint) => _HintTile(
                        hint: hint,
                        stageId: stageId,
                      ),
                    ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final stageProv = context.watch<StageProvider>();
    final stage = stageProv.currentStage;

    return Scaffold(
      backgroundColor: AppTheme.paper,
      body: SafeArea(
        child: stageProv.loading
            ? const Center(child: CircularProgressIndicator())
            : stage == null
                ? Center(
                    child: Text(
                      stageProv.error ?? 'Etapa n\u00E3o encontrada',
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                  )
                : SingleChildScrollView(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 14,
                                vertical: 6,
                              ),
                              decoration: BoxDecoration(
                                color: AppTheme.ignite,
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                'ETAPA ${stage['order'] ?? '?'}',
                                style: const TextStyle(
                                  fontFamily: 'JetBrains Mono',
                                  fontWeight: FontWeight.w700,
                                  fontSize: 16,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                            const Spacer(),
                            TextButton(
                              onPressed: () => Navigator.pop(context),
                              child: const Text(
                                'VOLTAR AO MAPA',
                                style: TextStyle(
                                  fontFamily: 'JetBrains Mono',
                                  fontSize: 12,
                                  color: AppTheme.chalk,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 20),

                        Text(
                          stage['name'] as String? ?? '',
                          style: Theme.of(context).textTheme.displayMedium,
                        ),
                        const SizedBox(height: 16),

                        if (stage['image_url'] != null)
                          Padding(
                            padding: const EdgeInsets.only(bottom: 20),
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(12),
                              child: Image.network(
                                stage['image_url'] as String,
                                width: double.infinity,
                                height: 220,
                                fit: BoxFit.cover,
                                errorBuilder: (_, _, _) => Container(
                                  height: 220,
                                  color: AppTheme.rule,
                                  child: const Center(
                                    child: Icon(
                                      Icons.image,
                                      color: AppTheme.chalk,
                                      size: 48,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),

                        Text(
                          stage['narrative_text'] as String? ?? '',
                          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                            fontSize: 18,
                            height: 1.6,
                          ),
                        ),
                        const SizedBox(height: 32),

                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton.icon(
                            onPressed: () =>
                                Navigator.pushNamed(context, '/photo'),
                            icon: const Icon(Icons.camera_alt),
                            label: const Text(
                              'TIRAR FOTO',
                              style: TextStyle(fontFamily: 'JetBrains Mono'),
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton.icon(
                            onPressed: () =>
                                Navigator.pushNamed(context, '/answer'),
                            icon: const Icon(Icons.edit),
                            label: const Text(
                              'RESPONDER',
                              style: TextStyle(fontFamily: 'JetBrains Mono'),
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        SizedBox(
                          width: double.infinity,
                          child: OutlinedButton.icon(
                            onPressed: _openHintsSheet,
                            icon: const Icon(Icons.lightbulb_outline),
                            label: const Text(
                              'DICAS',
                              style: TextStyle(fontFamily: 'JetBrains Mono'),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
      ),
    );
  }
}

class _HintTile extends StatefulWidget {
  final Map<String, dynamic> hint;
  final String stageId;

  const _HintTile({required this.hint, required this.stageId});

  @override
  State<_HintTile> createState() => _HintTileState();
}

class _HintTileState extends State<_HintTile> {
  bool _buying = false;

  @override
  Widget build(BuildContext context) {
    final hint = widget.hint;
    final isLocked = hint['locked'] == true;
    final text = hint['text'] as String?;
    final price = hint['price'] as int?;

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    'Dica',
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                ),
                if (isLocked && price != null)
                  Text(
                    '$price pts',
                    style: const TextStyle(
                      fontFamily: 'JetBrains Mono',
                      fontSize: 13,
                      color: AppTheme.chalk,
                    ),
                  ),
              ],
            ),
            if (isLocked) ...[
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _buying
                      ? null
                      : () async {
                          setState(() => _buying = true);
                          final stageProv = context.read<StageProvider>();
                          await stageProv.buyHint(
                            widget.stageId,
                            hint['id'] as String,
                          );
                          if (!mounted) return;
                          setState(() => _buying = false);
                        },
                  child: _buying
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text(
                          'COMPRAR',
                          style: TextStyle(fontFamily: 'JetBrains Mono'),
                        ),
                ),
              ),
            ],
            if (!isLocked && text != null)
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Text(
                  text,
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
              ),
          ],
        ),
      ),
    );
  }
}
