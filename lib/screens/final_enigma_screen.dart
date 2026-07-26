import 'dart:async';
import 'package:flutter/material.dart';
import 'package:helena_quest_app/config/theme.dart';
import 'package:helena_quest_app/services/api_service.dart';

class FinalEnigmaScreen extends StatefulWidget {
  final ApiService apiService;

  const FinalEnigmaScreen({super.key, required this.apiService});

  @override
  State<FinalEnigmaScreen> createState() => _FinalEnigmaScreenState();
}

class _FinalEnigmaScreenState extends State<FinalEnigmaScreen> {
  bool _loading = true;
  Map<String, dynamic>? _status;
  List<Map<String, dynamic>> _attempts = [];
  String? _error;
  String? _successMessage;
  final _guessController = TextEditingController();
  Timer? _cooldownTimer;
  Duration _remaining = Duration.zero;

  @override
  void initState() {
    super.initState();
    _loadAll();
  }

  Future<void> _loadAll() async {
    setState(() => _loading = true);
    try {
      final results = await Future.wait([
        widget.apiService.get('/final-enigma/status'),
        widget.apiService.get('/final-enigma/attempts'),
      ]);
      setState(() {
        _status = results[0] as Map<String, dynamic>?;
        _attempts = ((results[1] as Map)['attempts'] as List? ?? [])
            .map((e) => e as Map<String, dynamic>)
            .toList();
        _error = null;
        _loading = false;
      });
      _checkCooldown();
    } on ApiException catch (e) {
      setState(() {
        _error = e.message;
        _loading = false;
      });
    } catch (_) {
      setState(() {
        _error = 'Erro ao carregar enigma final.';
        _loading = false;
      });
    }
  }

  void _checkCooldown() {
    _cooldownTimer?.cancel();
    if (_status?['locked'] == true && _status?['next_available_at'] != null) {
      final next = DateTime.parse(_status!['next_available_at'] as String);
      _remaining = next.difference(DateTime.now());
      if (_remaining.isNegative) {
        _status!['locked'] = false;
        return;
      }
      _cooldownTimer = Timer.periodic(const Duration(seconds: 1), (_) {
        if (!mounted) return;
        setState(() {
          _remaining = _remaining - const Duration(seconds: 1);
          if (_remaining.isNegative) {
            _cooldownTimer?.cancel();
            _status!['locked'] = false;
            _loadAll();
          }
        });
      });
    }
  }

  Future<void> _guess() async {
    final word = _guessController.text.trim();
    if (word.isEmpty) return;

    try {
      final data = await widget.apiService.post('/final-enigma/guess', body: {
        'word': word,
      });
      setState(() {
        _error = null;
        if (data['correct'] == true) {
          _successMessage = data['message'] as String?;
          _guessController.clear();
        } else {
          _error = data['message'] as String?;
        }
      });
      _loadAll();
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    }
  }

  Future<void> _scanLetter() async {
    final scanned = await Navigator.pushNamed(context, '/final-enigma/scan');
    if (scanned is String) {
      try {
        await widget.apiService.post('/final-enigma/validate-letter/$scanned');
        _loadAll();
      } on ApiException catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(e.message, style: const TextStyle(fontFamily: 'Nunito')),
              backgroundColor: AppTheme.error,
            ),
          );
        }
      }
    }
  }

  String _lettersDisplay() {
    final list = _status?['letters_unlocked'] as List<dynamic>? ?? <dynamic>[];
    final total = _status?['required_letters_count'] as int? ?? list.length;
    return list.join(' ').padRight(total * 2 - 1, '_');
  }

  @override
  void dispose() {
    _guessController.dispose();
    _cooldownTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator(color: AppTheme.ignite)),
      );
    }

    final enabled = _status?['enabled'] == true;
    final locked = _status?['locked'] == true;
    final solved = (_status?['correct_attempts'] as int? ?? 0) > 0;
    final attemptsMade = _status?['attempts_made'] as int? ?? 0;
    final maxAttempts = _status?['max_attempts'] as int? ?? 3;
    final lettersCount = (_status?['letters_unlocked'] as List?)?.length ?? 0;
    final requiredLetters = _status?['required_letters_count'] as int? ?? 0;
    final allLettersCollected = lettersCount >= requiredLetters;

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text('ENIGMA FINAL'),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (!enabled) ...[
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.error.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppTheme.error),
                ),
                child: const Text(
                  'Enigma ainda nao disponivel.',
                  textAlign: TextAlign.center,
                ),
              ),
            ] else if (solved) ...[
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: const Color(0xFF22C55E).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFF22C55E)),
                ),
                child: Column(
                  children: [
                    const Icon(Icons.emoji_events, size: 48, color: Color(0xFFFFB800)),
                    const SizedBox(height: 12),
                    Text(
                      _successMessage ?? 'PARABENS! Gincana finalizada!',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF22C55E),
                      ),
                    ),
                  ],
                ),
              ),
            ] else ...[
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Tentativas:', style: TextStyle(fontWeight: FontWeight.w600)),
                  Text(
                    '$attemptsMade / $maxAttempts',
                    style: TextStyle(
                      fontFamily: 'JetBrains Mono',
                      fontWeight: FontWeight.w700,
                      fontSize: 18,
                      color: attemptsMade >= maxAttempts ? AppTheme.error : AppTheme.ink,
                    ),
                  ),
                ],
              ),
              if (locked) ...[
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppTheme.error.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.lock, size: 16, color: AppTheme.error),
                      const SizedBox(width: 8),
                      Text(
                        'Aguarde ${_remaining.inMinutes}:${(_remaining.inSeconds % 60).toString().padLeft(2, '0')}',
                        style: const TextStyle(
                          fontFamily: 'JetBrains Mono',
                          fontWeight: FontWeight.w600,
                          color: AppTheme.error,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 24),
              Center(
                child: Text(
                  _lettersDisplay(),
                  style: const TextStyle(
                    fontFamily: 'JetBrains Mono',
                    fontSize: 32,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 6,
                    color: AppTheme.ink,
                  ),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                '$lettersCount / $requiredLetters letras coletadas',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontFamily: 'Nunito',
                  fontSize: 13,
                  color: AppTheme.chalk,
                ),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: _scanLetter,
                  icon: const Icon(Icons.qr_code_scanner, size: 20),
                  label: const Text('ESCANEAR QR PARA COLETAR LETRA'),
                ),
              ),
              if (allLettersCollected) ...[
                const SizedBox(height: 24),
                TextField(
                  controller: _guessController,
                  decoration: const InputDecoration(
                    labelText: 'Palpite',
                    hintText: 'Digite a palavra',
                  ),
                  style: const TextStyle(
                    fontFamily: 'JetBrains Mono',
                    fontSize: 20,
                  ),
                  textAlign: TextAlign.center,
                  textCapitalization: TextCapitalization.characters,
                  enabled: !locked,
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: locked ? null : _guess,
                    child: const Text('TENTAR'),
                  ),
                ),
              ],
            ],
            if (_error != null) ...[
              const SizedBox(height: 12),
              Text(
                _error!,
                style: const TextStyle(color: AppTheme.error),
                textAlign: TextAlign.center,
              ),
            ],
            if (_attempts.isNotEmpty) ...[
              const SizedBox(height: 24),
              const Text(
                'TENTATIVAS ANTERIORES',
                style: TextStyle(
                  fontFamily: 'JetBrains Mono',
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
              const SizedBox(height: 8),
              ..._attempts.map((a) {
                final correct = a['correct'] == true;
                return Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  margin: const EdgeInsets.only(bottom: 8),
                  decoration: BoxDecoration(
                    color: AppTheme.paper,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: correct ? const Color(0xFF22C55E) : AppTheme.rule,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        correct ? Icons.check_circle : Icons.cancel,
                        size: 18,
                        color: correct ? const Color(0xFF22C55E) : AppTheme.error,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        '#${a['attempt_number']} ${a['guessed_word'] ?? ''}',
                        style: TextStyle(
                          fontFamily: 'JetBrains Mono',
                          fontSize: 14,
                          decoration: correct ? TextDecoration.lineThrough : null,
                        ),
                      ),
                      const Spacer(),
                      if (correct)
                        const Text(
                          'CORRETO!',
                          style: TextStyle(
                            color: Color(0xFF22C55E),
                            fontWeight: FontWeight.w700,
                            fontSize: 12,
                          ),
                        ),
                    ],
                  ),
                );
              }),
            ],
          ],
        ),
      ),
    );
  }
}
