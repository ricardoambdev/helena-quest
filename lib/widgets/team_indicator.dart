import 'package:flutter/material.dart';
import 'package:helena_quest_app/config/theme.dart';

enum IndicatorSize { small, medium, large }

class TeamIndicator extends StatelessWidget {
  final String name;
  final String colorHex;
  final IndicatorSize size;

  const TeamIndicator({
    super.key,
    required this.name,
    required this.colorHex,
    this.size = IndicatorSize.medium,
  });

  double get _dotSize {
    switch (size) {
      case IndicatorSize.small:
        return 12;
      case IndicatorSize.medium:
        return 16;
      case IndicatorSize.large:
        return 24;
    }
  }

  double get _fontSize {
    switch (size) {
      case IndicatorSize.small:
        return 12;
      case IndicatorSize.medium:
        return 14;
      case IndicatorSize.large:
        return 16;
    }
  }

  @override
  Widget build(BuildContext context) {
    final color = AppTheme.teamColorFromHex(colorHex);

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: _dotSize,
          height: _dotSize,
          decoration: BoxDecoration(
            color: color,
            shape: BoxShape.circle,
            border: Border.all(
              color: Colors.white,
              width: _dotSize > 16 ? 2 : 1,
            ),
          ),
        ),
        SizedBox(width: _dotSize * 0.5),
        Text(
          name,
          style: TextStyle(
            fontFamily: 'Nunito',
            fontSize: _fontSize,
            fontWeight: FontWeight.w600,
            color: AppTheme.ink,
          ),
        ),
      ],
    );
  }
}
