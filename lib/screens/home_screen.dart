import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/stage_provider.dart';
import '../config/theme.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<StageProvider>().loadCurrentStage();
    });
  }

  Future<void> _refresh() async {
    await context.read<StageProvider>().loadCurrentStage();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final stageProv = context.watch<StageProvider>();

    final team = auth.team;
    final teamName = team?['name'] as String? ?? 'Equipe';
    final teamColorHex = team?['color_hex'] as String? ?? '#FF6600';
    final teamColor = AppTheme.teamColorFromHex(teamColorHex);

    final stage = stageProv.currentStage;
    final progress = stageProv.currentProgress;

    return Scaffold(
      backgroundColor: AppTheme.paper,
      appBar: AppBar(
        title: const Text(
          'Helena Quest',
          style: TextStyle(
            fontFamily: 'Inter',
            fontWeight: FontWeight.w700,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await auth.logout();
              if (!context.mounted) return;
              Navigator.pushReplacementNamed(context, '/login');
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: teamColor,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Center(
                        child: Text(
                          teamName.isNotEmpty
                              ? teamName[0].toUpperCase()
                              : '?',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 22,
                            fontFamily: 'Inter',
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Text(
                        teamName,
                        style: Theme.of(context).textTheme.headlineLarge,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            if (stageProv.loading)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(),
                ),
              )
            else if (stage == null)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Center(
                    child: Text(
                      stageProv.error ?? 'Nenhuma etapa dispon\u00edvel',
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                  ),
                ),
              )
            else ...[
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: AppTheme.ignite.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              'Etapa ${stage['order'] ?? '?'}',
                              style: const TextStyle(
                                fontFamily: 'JetBrains Mono',
                                fontWeight: FontWeight.w500,
                                fontSize: 12,
                                color: AppTheme.ignite,
                              ),
                            ),
                          ),
                          const Spacer(),
                          if (progress != null)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: _statusColor(
                                        progress['status'] as String?)
                                    .withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                _statusLabel(progress['status'] as String?),
                                style: TextStyle(
                                  fontFamily: 'JetBrains Mono',
                                  fontWeight: FontWeight.w500,
                                  fontSize: 12,
                                  color: _statusColor(
                                      progress['status'] as String?),
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        stage['name'] as String? ?? 'Etapa',
                        style: Theme.of(context).textTheme.headlineLarge,
                      ),
                      if (stage['description'] != null) ...[
                        const SizedBox(height: 6),
                        Text(
                          stage['description'] as String,
                          style: Theme.of(context)
                              .textTheme
                              .bodyMedium
                              ?.copyWith(color: AppTheme.chalk),
                          maxLines: 3,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),

              SizedBox(
                width: double.infinity,
                height: 56,
                child: ElevatedButton.icon(
                  onPressed: () => Navigator.pushNamed(context, '/scanner'),
                  icon: const Icon(Icons.qr_code_scanner),
                  label: const Text(
                    'INICIAR / CONTINUAR',
                    style: TextStyle(fontFamily: 'JetBrains Mono', fontSize: 15),
                  ),
                ),
              ),
            ],

            const SizedBox(height: 32),

            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {},
                    icon: const Icon(Icons.map_outlined),
                    label: const Text(
                      'MAPA',
                      style: TextStyle(fontFamily: 'JetBrains Mono'),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {},
                    icon: const Icon(Icons.leaderboard_outlined),
                    label: const Text(
                      'CLASSIFICA\u00C7\u00C3O',
                      style: TextStyle(fontFamily: 'JetBrains Mono'),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Color _statusColor(String? status) {
    switch (status) {
      case 'completed':
        return Colors.green;
      case 'in_progress':
        return AppTheme.ignite;
      case 'locked':
        return AppTheme.chalk;
      default:
        return AppTheme.chalk;
    }
  }

  String _statusLabel(String? status) {
    switch (status) {
      case 'completed':
        return 'Conclu\u00EDda';
      case 'in_progress':
        return 'Em andamento';
      case 'locked':
        return 'Bloqueada';
      default:
        return 'Dispon\u00EDvel';
    }
  }
}
