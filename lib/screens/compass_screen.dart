import 'dart:math';
import 'package:flutter/material.dart';
import '../config/theme.dart';

class CompassScreen extends StatefulWidget {
  const CompassScreen({super.key});

  @override
  State<CompassScreen> createState() => _CompassScreenState();
}

class _CompassScreenState extends State<CompassScreen> with WidgetsBindingObserver {
  double _heading = 0;
  double _targetBearing = 0;
  bool _hasTarget = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _simulateCompass();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  void _simulateCompass() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (!mounted) return;
      setState(() => _heading = (_heading + Random().nextDouble() * 4 - 2) % 360);
      _simulateCompass();
    });
  }

  void _setTarget(double bearing) {
    setState(() {
      _targetBearing = bearing;
      _hasTarget = true;
    });
  }

  String _cardinal(double degrees) {
    if (degrees < 22.5 || degrees >= 337.5) return 'N';
    if (degrees < 67.5) return 'NE';
    if (degrees < 112.5) return 'L';
    if (degrees < 157.5) return 'SE';
    if (degrees < 202.5) return 'S';
    if (degrees < 247.5) return 'SO';
    if (degrees < 292.5) return 'O';
    return 'NO';
  }

  @override
  Widget build(BuildContext context) {
    final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
    final direction = args?['direction'] as String?;
    final steps = args?['steps'] as int?;
    final landmarks = args?['landmarks'] as String?;

    return Scaffold(
      backgroundColor: AppTheme.ink,
      appBar: AppBar(
        backgroundColor: AppTheme.ink,
        title: const Text('BUSSOLA',
          style: TextStyle(fontFamily: 'JetBrains Mono', fontWeight: FontWeight.w600, color: Colors.white)),
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Direction info
            if (direction != null || steps != null || landmarks != null)
              Container(
                margin: const EdgeInsets.all(16),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFF2A2D35),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Column(
                  children: [
                    if (direction != null)
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text('DIRECAO: ', style: TextStyle(color: AppTheme.chalk, fontSize: 14, fontFamily: 'JetBrains Mono')),
                          Text(direction, style: TextStyle(color: AppTheme.ignite, fontSize: 24, fontFamily: 'Inter', fontWeight: FontWeight.w800)),
                        ],
                      ),
                    if (steps != null) ...[
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text('PASSOS: ', style: TextStyle(color: AppTheme.chalk, fontSize: 14, fontFamily: 'JetBrains Mono')),
                          Text('$steps', style: TextStyle(color: AppTheme.paper, fontSize: 24, fontFamily: 'Inter', fontWeight: FontWeight.w800)),
                        ],
                      ),
                    ],
                    if (landmarks != null) ...[
                      const SizedBox(height: 8),
                      Text(landmarks, style: const TextStyle(color: AppTheme.chalk, fontSize: 13), textAlign: TextAlign.center),
                    ],
                  ],
                ),
              ),

            const Spacer(),

            // COMPASS
            Center(
              child: SizedBox(
                width: 280, height: 280,
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    // Compass ring
                    CustomPaint(
                      size: const Size(280, 280),
                      painter: _CompassPainter(_heading, _targetBearing, _hasTarget),
                    ),
                    // Current direction text
                    Text(
                      _cardinal(_heading),
                      style: const TextStyle(
                        color: Colors.white, fontSize: 48,
                        fontFamily: 'Inter', fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),
            Text(
              '${_heading.toStringAsFixed(1)}°',
              style: const TextStyle(
                color: AppTheme.chalk, fontSize: 16, fontFamily: 'JetBrains Mono'),
            ),
            if (_hasTarget) ...[
              const SizedBox(height: 4),
              Text(
                'Alvo: ${_targetBearing.toStringAsFixed(0)}° (${_cardinal(_targetBearing)})',
                style: const TextStyle(
                  color: AppTheme.ignite, fontSize: 14, fontFamily: 'JetBrains Mono'),
              ),
            ],

            const SizedBox(height: 12),

            // Target buttons
            Wrap(
              spacing: 8, runSpacing: 8,
              alignment: WrapAlignment.center,
              children: ['N','NE','L','SE','S','SO','O','NO'].map((d) {
                final bearings = {'N': 0.0, 'NE': 45.0, 'L': 90.0, 'SE': 135.0,
                    'S': 180.0, 'SO': 225.0, 'O': 270.0, 'NO': 315.0};
                return SizedBox(
                  width: 60,
                  child: OutlinedButton(
                    onPressed: () => _setTarget(bearings[d]!),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: direction == d ? AppTheme.ignite : AppTheme.chalk,
                      side: BorderSide(color: direction == d ? AppTheme.ignite : AppTheme.chalk),
                    ),
                    child: Text(d, style: const TextStyle(fontFamily: 'JetBrains Mono', fontWeight: FontWeight.w600)),
                  ),
                );
              }).toList(),
            ),

            const Spacer(),

            Padding(
              padding: const EdgeInsets.all(16),
              child: SizedBox(
                width: double.infinity, height: 56,
                child: ElevatedButton.icon(
                  onPressed: () => Navigator.pushNamed(context, '/answer'),
                  icon: const Icon(Icons.edit),
                  label: const Text('INSERIR NUMEROS', style: TextStyle(fontFamily: 'JetBrains Mono')),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CompassPainter extends CustomPainter {
  final double heading;
  final double targetBearing;
  final bool hasTarget;

  _CompassPainter(this.heading, this.targetBearing, this.hasTarget);

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = size.width / 2 - 8;

    // Outer ring
    final ringPaint = Paint()
      ..color = const Color(0xFF2A2D35)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3;
    canvas.drawCircle(center, radius, ringPaint);

    // Ticks
    for (int i = 0; i < 72; i++) {
      final angle = (i * 5 - heading) * pi / 180;
      final isMain = i % 18 == 0;
      final isMid = i % 9 == 0;
      final inner = radius - (isMain ? 20 : isMid ? 14 : 8);
      final outer = radius - 4;
      final paint = Paint()
        ..color = isMain ? AppTheme.ignite : AppTheme.chalk
        ..strokeWidth = isMain ? 2.5 : 1.5;
      canvas.drawLine(
        Offset(center.dx + cos(angle) * inner, center.dy + sin(angle) * inner),
        Offset(center.dx + cos(angle) * outer, center.dy + sin(angle) * outer),
        paint,
      );
    }

    // Cardinal labels
    final cardinals = {'N': 0.0, 'L': 90.0, 'S': 180.0, 'O': 270.0};
    cardinals.forEach((label, deg) {
      final angle = (deg - heading) * pi / 180;
      final x = center.dx + cos(angle) * (radius - 32);
      final y = center.dy + sin(angle) * (radius - 32);
      final tp = TextPainter(
        text: TextSpan(
          text: label,
          style: TextStyle(
            color: label == 'N' ? AppTheme.ignite : AppTheme.chalk,
            fontSize: 16, fontFamily: 'Inter', fontWeight: FontWeight.w700),
        ),
        textDirection: TextDirection.ltr,
      );
      tp.layout();
      tp.paint(canvas, Offset(x - tp.width / 2, y - tp.height / 2));
    });

    // Target indicator
    if (hasTarget) {
      final targetAngle = (targetBearing - heading) * pi / 180;
      final tx = center.dx + cos(targetAngle) * (radius - 16);
      final ty = center.dy + sin(targetAngle) * (radius - 16);

      final targetPaint = Paint()
        ..color = AppTheme.ignite.withValues(alpha: 0.8)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2.5;
      canvas.drawCircle(Offset(tx, ty), 10, targetPaint);
      // Cross
      canvas.drawLine(Offset(tx - 6, ty), Offset(tx + 6, ty), targetPaint);
      canvas.drawLine(Offset(tx, ty - 6), Offset(tx, ty + 6), targetPaint);
    }
  }

  @override
  bool shouldRepaint(covariant _CompassPainter old) => old.heading != heading;
}
