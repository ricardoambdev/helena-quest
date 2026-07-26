import 'package:flutter/material.dart';
import 'package:helena_quest_app/config/theme.dart';

class NumericKeyboard extends StatelessWidget {
  final void Function(int digit) onDigitPressed;
  final VoidCallback onBackspace;
  final VoidCallback onConfirm;

  const NumericKeyboard({
    super.key,
    required this.onDigitPressed,
    required this.onBackspace,
    required this.onConfirm,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Row(
          children: [
            _Key(digit: 1, onPressed: () => onDigitPressed(1)),
            _Key(digit: 2, onPressed: () => onDigitPressed(2)),
            _Key(digit: 3, onPressed: () => onDigitPressed(3)),
          ],
        ),
        Row(
          children: [
            _Key(digit: 4, onPressed: () => onDigitPressed(4)),
            _Key(digit: 5, onPressed: () => onDigitPressed(5)),
            _Key(digit: 6, onPressed: () => onDigitPressed(6)),
          ],
        ),
        Row(
          children: [
            _Key(digit: 7, onPressed: () => onDigitPressed(7)),
            _Key(digit: 8, onPressed: () => onDigitPressed(8)),
            _Key(digit: 9, onPressed: () => onDigitPressed(9)),
          ],
        ),
        Row(
          children: [
            _ActionKey(
              onPressed: onBackspace,
              child: const Icon(Icons.backspace, color: AppTheme.chalk),
            ),
            _Key(digit: 0, onPressed: () => onDigitPressed(0)),
            _ActionKey(
              onPressed: onConfirm,
              color: AppTheme.ignite,
              child: const Icon(Icons.check, color: Colors.white),
            ),
          ],
        ),
      ],
    );
  }
}

class _Key extends StatelessWidget {
  final int digit;
  final VoidCallback onPressed;

  const _Key({required this.digit, required this.onPressed});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Padding(
        padding: const EdgeInsets.all(4),
        child: SizedBox(
          height: 56,
          child: Material(
            color: AppTheme.ink,
            borderRadius: BorderRadius.circular(12),
            child: InkWell(
              onTap: onPressed,
              borderRadius: BorderRadius.circular(12),
              child: Center(
                child: Text(
                  digit.toString(),
                  style: const TextStyle(
                    fontFamily: 'JetBrains Mono',
                    fontSize: 22,
                    fontWeight: FontWeight.w600,
                    color: Colors.white,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _ActionKey extends StatelessWidget {
  final Widget child;
  final VoidCallback onPressed;
  final Color? color;

  const _ActionKey({
    required this.child,
    required this.onPressed,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Padding(
        padding: const EdgeInsets.all(4),
        child: SizedBox(
          height: 56,
          child: Material(
            color: color ?? AppTheme.paper,
            borderRadius: BorderRadius.circular(12),
            child: InkWell(
              onTap: onPressed,
              borderRadius: BorderRadius.circular(12),
              child: Center(child: child),
            ),
          ),
        ),
      ),
    );
  }
}
