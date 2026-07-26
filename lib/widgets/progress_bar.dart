import 'package:flutter/material.dart';
import 'package:helena_quest_app/config/theme.dart';

class ProgressBar extends StatelessWidget {
  final int completed;
  final int total;
  final Color? color;

  const ProgressBar({
    super.key,
    required this.completed,
    required this.total,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    final progress = total > 0 ? completed / total : 0.0;
    final fillColor = color ?? AppTheme.ignite;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(3),
          child: SizedBox(
            height: 6,
            child: LinearProgressIndicator(
              value: progress.clamp(0.0, 1.0),
              backgroundColor: AppTheme.rule,
              valueColor: AlwaysStoppedAnimation(fillColor),
            ),
          ),
        ),
        const SizedBox(height: 6),
        Text(
          '${(progress * 100).toInt()}%',
          style: const TextStyle(
            fontFamily: 'JetBrains Mono',
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: AppTheme.chalk,
          ),
        ),
      ],
    );
  }
}
