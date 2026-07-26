import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:helena_quest_app/providers/stage_provider.dart';
import 'package:helena_quest_app/config/theme.dart';

class AnswerScreen extends StatefulWidget {
  const AnswerScreen({super.key});

  @override
  State<AnswerScreen> createState() => _AnswerScreenState();
}

class _AnswerScreenState extends State<AnswerScreen>
    with SingleTickerProviderStateMixin {
  final _controller = TextEditingController();
  final _focusNode = FocusNode();
  bool _submitting = false;
  String? _errorMessage;
  late AnimationController _shakeController;
  late Animation<double> _shakeAnimation;

  @override
  void initState() {
    super.initState();
    _shakeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );
    _shakeAnimation = Tween<double>(begin: 0, end: 4).animate(
      CurvedAnimation(parent: _shakeController, curve: Curves.elasticIn),
    );
    _focusNode.requestFocus();
  }

  @override
  void dispose() {
    _controller.dispose();
    _focusNode.dispose();
    _shakeController.dispose();
    super.dispose();
  }

  void _onDigit(String digit) {
    if (_controller.text.length < 8) {
      _controller.text += digit;
      _controller.selection = TextSelection.fromPosition(
        TextPosition(offset: _controller.text.length),
      );
    }
  }

  void _onBackspace() {
    if (_controller.text.isNotEmpty) {
      _controller.text =
          _controller.text.substring(0, _controller.text.length - 1);
      _controller.selection = TextSelection.fromPosition(
        TextPosition(offset: _controller.text.length),
      );
    }
  }

  Future<void> _submit() async {
    final answer = _controller.text.trim();
    if (answer.length < 4 || answer.length > 8) {
      setState(() => _errorMessage = 'A resposta deve ter entre 4 e 8 dígitos.');
      _shakeController.forward(from: 0);
      return;
    }

    setState(() {
      _submitting = true;
      _errorMessage = null;
    });

    final stage = context.read<StageProvider>();
    final stageId = stage.currentStage?['id']?.toString();
    if (stageId == null) {
      setState(() {
        _submitting = false;
        _errorMessage = 'Nenhuma etapa ativa.';
      });
      return;
    }

    final result = await stage.submitAnswer(stageId, answer);

    if (!mounted) return;
    setState(() => _submitting = false);

    if (result['correct'] == true) {
      Navigator.pushReplacementNamed(context, '/result', arguments: result);
    } else {
      setState(() {
        _errorMessage = result['message'] ?? 'Resposta incorreta. Tente novamente.';
      });
      _shakeController.forward(from: 0);
    }
  }

  @override
  Widget build(BuildContext context) {
    final stage = context.watch<StageProvider>();
    final currentStage = stage.currentStage;
    final stageName = currentStage?['name']?.toString() ?? 'Etapa';
    final stageOrder = currentStage?['order']?.toString() ?? '-';

    return Scaffold(
      backgroundColor: AppTheme.paper,
      appBar: AppBar(
        backgroundColor: AppTheme.ink,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppTheme.paper),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          stageName.toUpperCase(),
          style: const TextStyle(
            fontFamily: 'JetBrains Mono',
            fontWeight: FontWeight.w500,
            color: AppTheme.paper,
          ),
        ),
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 16),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            decoration: BoxDecoration(
              color: AppTheme.ignite,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              '#$stageOrder',
              style: const TextStyle(
                fontFamily: 'JetBrains Mono',
                fontWeight: FontWeight.w700,
                color: Colors.white,
                fontSize: 14,
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          const SizedBox(height: 32),
          Text(
            'DIGITE A RESPOSTA',
            style: Theme.of(context).textTheme.labelLarge?.copyWith(
                  color: AppTheme.chalk,
                  letterSpacing: 2,
                ),
          ),
          const SizedBox(height: 16),
          AnimatedBuilder(
            animation: _shakeAnimation,
            builder: (context, child) {
              return Transform.translate(
                offset: Offset(_shakeAnimation.value, 0),
                child: child,
              );
            },
            child: Container(
              margin: const EdgeInsets.symmetric(horizontal: 40),
              padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 24),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: _errorMessage != null ? AppTheme.error : AppTheme.rule,
                  width: 2,
                ),
                boxShadow: [
                  BoxShadow(
                    color: AppTheme.ink.withValues(alpha: 0.05),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Text(
                _controller.text.isEmpty
                    ? '• ' * 4
                    : _controller.text,
                style: const TextStyle(
                  fontFamily: 'JetBrains Mono',
                  fontSize: 36,
                  fontWeight: FontWeight.w700,
                  color: AppTheme.ignite,
                  letterSpacing: 8,
                ),
                textAlign: TextAlign.center,
              ),
            ),
          ),
          if (_errorMessage != null) ...[
            const SizedBox(height: 8),
            Text(
              _errorMessage!,
              style: const TextStyle(
                fontFamily: 'Nunito',
                color: AppTheme.error,
                fontSize: 13,
              ),
            ),
          ],
          const SizedBox(height: 8),
          Text(
            '${_controller.text.length}/8 dígitos',
            style: const TextStyle(
              fontFamily: 'JetBrains Mono',
              color: AppTheme.chalk,
              fontSize: 12,
            ),
          ),
          const Spacer(),
          _buildNumericKeypad(),
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: (_submitting || _controller.text.length < 4)
                    ? null
                    : _submit,
                child: _submitting
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Text('CONFIRMAR'),
              ),
            ),
          ),
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildNumericKeypad() {
    const keys = [
      ['1', '2', '3'],
      ['4', '5', '6'],
      ['7', '8', '9'],
      ['', '0', 'backspace'],
    ];

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Column(
        children: keys.map((row) {
          return Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: row.map((key) {
              if (key.isEmpty) {
                return const SizedBox(width: 72, height: 56);
              }
              if (key == 'backspace') {
                return _KeypadButton(
                  onTap: _onBackspace,
                  child: const Icon(Icons.backspace_outlined, size: 22),
                );
              }
              return _KeypadButton(
                onTap: () => _onDigit(key),
                child: Text(
                  key,
                  style: const TextStyle(
                    fontFamily: 'JetBrains Mono',
                    fontSize: 24,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.ink,
                  ),
                ),
              );
            }).toList(),
          );
        }).toList(),
      ),
    );
  }
}

class _KeypadButton extends StatelessWidget {
  final Widget child;
  final VoidCallback onTap;

  const _KeypadButton({required this.child, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(4),
      width: 72,
      height: 56,
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        elevation: 0,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(12),
          splashColor: AppTheme.ignite.withValues(alpha: 0.15),
          child: Center(child: child),
        ),
      ),
    );
  }
}
