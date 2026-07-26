import 'package:flutter/material.dart';
import 'package:helena_quest_app/config/theme.dart';

class ResultScreen extends StatelessWidget {
  const ResultScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final args =
        ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
    final correct = args?['correct'] == true;
    final message = args?['message']?.toString();
    final secretNumber = args?['secret_number']?.toString();
    final nextHint = args?['next_hint']?.toString();
    final score = args?['score'];
    final scoreText = score != null ? score.toString() : '0';

    return Scaffold(
      backgroundColor: correct ? AppTheme.ignite : AppTheme.ink,
      body: SafeArea(
        child: Column(
          children: [
            const Spacer(flex: 2),
            // Icon
            AnimatedContainer(
              duration: const Duration(milliseconds: 300),
              width: 120,
              height: 120,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: correct
                    ? Colors.white.withValues(alpha: 0.2)
                    : AppTheme.error.withValues(alpha: 0.3),
              ),
              child: Icon(
                correct ? Icons.check : Icons.close,
                size: 64,
                color: correct ? Colors.white : AppTheme.error,
              ),
            ),
            const SizedBox(height: 24),
            // Result text
            Text(
              correct ? 'CORRETO!' : 'INCORRETO',
              style: const TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w800,
                fontSize: 28,
                color: Colors.white,
                letterSpacing: 2,
              ),
            ),
            if (message != null) ...[
              const SizedBox(height: 8),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 32),
                child: Text(
                  message,
                  style: TextStyle(
                    fontFamily: 'Nunito',
                    fontSize: 16,
                    color: Colors.white.withValues(alpha: 0.85),
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
            ],
            const SizedBox(height: 32),
            // Score
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.star, color: Colors.white, size: 20),
                  const SizedBox(width: 8),
                  Text(
                    '+$scoreText pts',
                    style: const TextStyle(
                      fontFamily: 'JetBrains Mono',
                      fontWeight: FontWeight.w700,
                      fontSize: 22,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            ),
            if (correct && secretNumber != null) ...[
              const SizedBox(height: 24),
              Text(
                'NÚMERO SECRETO',
                style: TextStyle(
                  fontFamily: 'JetBrains Mono',
                  fontSize: 11,
                  letterSpacing: 2,
                  color: Colors.white.withValues(alpha: 0.6),
                ),
              ),
              const SizedBox(height: 6),
              Text(
                secretNumber,
                style: const TextStyle(
                  fontFamily: 'JetBrains Mono',
                  fontWeight: FontWeight.w800,
                  fontSize: 32,
                  color: Colors.white,
                  letterSpacing: 6,
                ),
              ),
            ],
            if (nextHint != null) ...[
              const SizedBox(height: 24),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 32),
                child: Text(
                  nextHint,
                  style: TextStyle(
                    fontFamily: 'Nunito',
                    fontSize: 14,
                    color: Colors.white.withValues(alpha: 0.7),
                    fontStyle: FontStyle.italic,
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
            ],
            const Spacer(flex: 2),
            // Buttons
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => Navigator.pushReplacementNamed(
                    context,
                    '/stage',
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: correct ? Colors.white : AppTheme.ignite,
                    foregroundColor: correct ? AppTheme.ink : Colors.white,
                  ),
                  child: Text(
                    correct ? 'PRÓXIMA ETAPA' : 'TENTAR NOVAMENTE',
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: TextButton(
                onPressed: () =>
                    Navigator.pushNamedAndRemoveUntil(context, '/home', (_) => false),
                child: Text(
                  'VOLTAR AO INÍCIO',
                  style: TextStyle(
                    fontFamily: 'JetBrains Mono',
                    color: Colors.white.withValues(alpha: 0.8),
                  ),
                ),
              ),
            ),
            const Spacer(flex: 1),
          ],
        ),
      ),
    );
  }
}
