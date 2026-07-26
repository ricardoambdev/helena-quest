import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:helena_quest_app/config/theme.dart';
import 'package:helena_quest_app/providers/auth_provider.dart';
import 'package:helena_quest_app/providers/team_provider.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<TeamProvider>().loadTeam();
    });
  }

  Future<void> _logout() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Sair'),
        content: const Text('Tem certeza que deseja sair?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('CANCELAR'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('SAIR'),
          ),
        ],
      ),
    );

    if (confirmed == true && mounted) {
      await context.read<AuthProvider>().logout();
      if (mounted) {
        Navigator.pushNamedAndRemoveUntil(
          context,
          '/login',
          (_) => false,
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final team = context.watch<TeamProvider>().team;
    final authTeam = context.watch<AuthProvider>().team;
    final data = team ?? authTeam;

    final name = data?['name'] as String? ?? '---';
    final colorHex = data?['color'] as String? ?? '#FF6600';
    final crest = data?['crest_url'] as String?;
    final competitionName = data?['competition_name'] as String? ?? '---';
    final score = data?['score'] as int? ?? 0;
    final ranking = data?['ranking'] as int?;
    final stagesCompleted = data?['stages_completed'] as int? ?? 0;
    final totalStages = data?['total_stages'] as int? ?? 1;
    final correctAnswers = data?['correct_answers'] as int? ?? 0;
    final wrongAnswers = data?['wrong_answers'] as int? ?? 0;

    final teamColor = AppTheme.teamColorFromHex(colorHex);

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'PERFIL',
          style: TextStyle(
            fontFamily: 'JetBrains Mono',
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            CircleAvatar(
              radius: 48,
              backgroundColor: teamColor,
              child: crest != null
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(48),
                      child: Image.network(
                        crest,
                        width: 96,
                        height: 96,
                        fit: BoxFit.cover,
                      ),
                    )
                  : Text(
                      name.isNotEmpty ? name[0].toUpperCase() : '?',
                      style: const TextStyle(
                        fontFamily: 'Inter',
                        fontSize: 36,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                      ),
                    ),
            ),
            const SizedBox(height: 16),
            Text(
              name,
              style: const TextStyle(
                fontFamily: 'Inter',
                fontSize: 24,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              competitionName,
              style: const TextStyle(
                fontFamily: 'Nunito',
                color: AppTheme.chalk,
              ),
            ),
            const SizedBox(height: 24),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _StatItem(
                      label: 'PONTOS',
                      value: score.toString(),
                    ),
                    if (ranking != null)
                      _StatItem(
                        label: 'RANKING',
                        value: '$rankingº',
                      ),
                    _StatItem(
                      label: 'ETAPAS',
                      value: '$stagesCompleted / $totalStages',
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'ESTATÍSTICAS',
                      style: TextStyle(
                        fontFamily: 'JetBrains Mono',
                        fontWeight: FontWeight.w600,
                        fontSize: 14,
                      ),
                    ),
                    const Divider(),
                    _ProgressRow(
                      label: 'Respostas corretas',
                      value: correctAnswers.toString(),
                      color: Colors.green,
                    ),
                    const SizedBox(height: 8),
                    _ProgressRow(
                      label: 'Respostas erradas',
                      value: wrongAnswers.toString(),
                      color: AppTheme.error,
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 32),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _logout,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.error,
                  foregroundColor: Colors.white,
                ),
                child: const Text('SAIR'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatItem extends StatelessWidget {
  final String label;
  final String value;

  const _StatItem({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(
          value,
          style: const TextStyle(
            fontFamily: 'Inter',
            fontSize: 22,
            fontWeight: FontWeight.w700,
            color: AppTheme.ignite,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: const TextStyle(
            fontFamily: 'JetBrains Mono',
            fontSize: 11,
            color: AppTheme.chalk,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }
}

class _ProgressRow extends StatelessWidget {
  final String label;
  final String value;
  final Color color;

  const _ProgressRow({
    required this.label,
    required this.value,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(fontFamily: 'Nunito')),
        Text(
          value,
          style: TextStyle(
            fontFamily: 'JetBrains Mono',
            fontWeight: FontWeight.w600,
            color: color,
          ),
        ),
      ],
    );
  }
}
