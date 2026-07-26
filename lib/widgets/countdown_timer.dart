import 'dart:async';
import 'package:flutter/material.dart';
import 'package:helena_quest_app/config/theme.dart';

class CountdownTimer extends StatefulWidget {
  final DateTime? targetTime;
  final VoidCallback? onExpired;

  const CountdownTimer({
    super.key,
    this.targetTime,
    this.onExpired,
  });

  @override
  State<CountdownTimer> createState() => _CountdownTimerState();
}

class _CountdownTimerState extends State<CountdownTimer> {
  Timer? _timer;
  Duration _remaining = Duration.zero;

  @override
  void initState() {
    super.initState();
    _updateRemaining();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      _updateRemaining();
    });
  }

  void _updateRemaining() {
    if (widget.targetTime == null) return;
    final diff = widget.targetTime!.difference(DateTime.now());
    if (diff.isNegative) {
      if (_remaining != Duration.zero) {
        setState(() => _remaining = Duration.zero);
        widget.onExpired?.call();
      }
      return;
    }
    setState(() => _remaining = diff);
  }

  String get _formatted {
    final h = _remaining.inHours.toString().padLeft(2, '0');
    final m = (_remaining.inMinutes % 60).toString().padLeft(2, '0');
    final s = (_remaining.inSeconds % 60).toString().padLeft(2, '0');
    return '$h:$m:$s';
  }

  Color get _color {
    if (_remaining.inSeconds < 60 && _remaining.inSeconds > 0) {
      return AppTheme.ignite;
    }
    return AppTheme.ink;
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.targetTime == null) return const SizedBox.shrink();

    return Text(
      _formatted,
      style: TextStyle(
        fontFamily: 'JetBrains Mono',
        fontSize: 32,
        fontWeight: FontWeight.w700,
        color: _color,
      ),
    );
  }
}
