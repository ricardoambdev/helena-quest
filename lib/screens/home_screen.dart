import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/stage_provider.dart';
import '../config/theme.dart';
import '../config/routes.dart';

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

  String _stageTypeLabel(String? type) {
    switch (type) {
      case 'charada': return 'CHARADA';
      case 'caca_ao_tesouro': return 'CACA AO TESOURO';
      case 'mapas_bussola': return 'MAPAS COM BUSSOLA';
      case 'enigma_final': return 'ENIGMA FINAL';
      default: return 'ETAPA';
    }
  }

  IconData _stageTypeIcon(String? type) {
    switch (type) {
      case 'charada': return Icons.psychology;
      case 'caca_ao_tesouro': return Icons.explore;
      case 'mapas_bussola': return Icons.navigation;
      case 'enigma_final': return Icons.lock;
      default: return Icons.help_outline;
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final stageProv = context.watch<StageProvider>();
    final team = auth.team;
    final teamName = team?['name'] as String? ?? 'Equipe';
    final teamColorHex = team?['color_hex'] as String? ?? '#FF6600';
    final teamColor = AppTheme.teamColorFromHex(teamColorHex);
    final crestUrl = team?['crest_url'] as String?;
    final warCryText = team?['war_cry_text'] as String?;
    final teamDesc = team?['description'] as String?;

    final stage = stageProv.currentStage;
    final progress = stageProv.currentProgress;
    final stageType = stage?['stage_type'] as String?;

    return Scaffold(
      backgroundColor: AppTheme.ink,
      appBar: AppBar(
        backgroundColor: AppTheme.ink,
        title: const Text('Helena Quest',
          style: TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w700, color: Colors.white)),
        actions: [
          IconButton(
            icon: const Icon(Icons.person, color: Colors.white),
            onPressed: () => Navigator.pushNamed(context, AppRoutes.profile),
          ),
          IconButton(
            icon: const Icon(Icons.logout, color: Colors.white),
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
            // TEAM CARD
            Card(
              color: const Color(0xFF2A2D35),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Container(
                      width: 64, height: 64,
                      decoration: BoxDecoration(
                        color: teamColor,
                        borderRadius: BorderRadius.circular(16),
                        image: crestUrl != null
                            ? DecorationImage(image: NetworkImage(crestUrl), fit: BoxFit.cover)
                            : null,
                      ),
                      child: crestUrl == null
                          ? Center(child: Text(
                              teamName.isNotEmpty ? teamName[0].toUpperCase() : '?',
                              style: const TextStyle(color: Colors.white, fontSize: 28,
                                  fontFamily: 'Inter', fontWeight: FontWeight.w700)))
                          : null,
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(teamName,
                            style: const TextStyle(color: Colors.white, fontSize: 20,
                                fontFamily: 'Inter', fontWeight: FontWeight.w700)),
                          if (teamDesc != null)
                            Text(teamDesc,
                              style: const TextStyle(color: AppTheme.chalk, fontSize: 13),
                              maxLines: 2, overflow: TextOverflow.ellipsis),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            if (warCryText != null && warCryText.isNotEmpty) ...[
              const SizedBox(height: 8),
              Card(
                color: teamColor.withValues(alpha: 0.15),
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Row(
                    children: [
                      const Icon(Icons.volume_up, color: AppTheme.ignite, size: 20),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text('"$warCryText"',
                          style: const TextStyle(color: AppTheme.paper,
                              fontFamily: 'Nunito', fontStyle: FontStyle.italic, fontSize: 15)),
                      ),
                    ],
                  ),
                ),
              ),
            ],
            const SizedBox(height: 24),

            // CURRENT STAGE
            if (stageProv.loading)
              const Center(child: Padding(
                padding: EdgeInsets.all(32), child: CircularProgressIndicator(color: AppTheme.ignite)))
            else if (stage == null)
              Card(
                color: const Color(0xFF2A2D35),
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Center(
                    child: Text(stageProv.error ?? 'Nenhuma etapa disponivel',
                      style: const TextStyle(color: AppTheme.chalk, fontSize: 16)),
                  ),
                ),
              )
            else ...[
              Card(
                color: const Color(0xFF2A2D35),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppTheme.ignite.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(_stageTypeIcon(stageType), size: 14, color: AppTheme.ignite),
                                const SizedBox(width: 4),
                                Text('ETAPA ${stage['order'] ?? '?'}',
                                  style: const TextStyle(fontFamily: 'JetBrains Mono',
                                      fontWeight: FontWeight.w500, fontSize: 12, color: AppTheme.ignite)),
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppTheme.flame.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(_stageTypeLabel(stageType),
                              style: const TextStyle(fontFamily: 'JetBrains Mono',
                                  fontSize: 10, color: AppTheme.flame, fontWeight: FontWeight.w600)),
                          ),
                          const Spacer(),
                          if (progress != null)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: _statusColor(progress['status'] as String?).withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(_statusLabel(progress['status'] as String?),
                                style: TextStyle(fontFamily: 'JetBrains Mono', fontSize: 12,
                                    color: _statusColor(progress['status'] as String?))),
                            ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(stage['name'] as String? ?? 'Etapa',
                        style: const TextStyle(color: Colors.white, fontSize: 22,
                            fontFamily: 'Inter', fontWeight: FontWeight.w700)),
                      if (stage['description'] != null) ...[
                        const SizedBox(height: 6),
                        Text(stage['description'] as String,
                          style: const TextStyle(color: AppTheme.chalk, fontSize: 14),
                          maxLines: 3, overflow: TextOverflow.ellipsis),
                      ],
                      const SizedBox(height: 16),

                      // ACTION BUTTON
                      SizedBox(
                        width: double.infinity, height: 56,
                        child: ElevatedButton.icon(
                          onPressed: () {
                            if (stageType == 'enigma_final') {
                              Navigator.pushNamed(context, AppRoutes.finalEnigma);
                            } else {
                              Navigator.pushNamed(context, AppRoutes.scanner);
                            }
                          },
                          icon: Icon(stageType == 'enigma_final' ? Icons.lock_open : Icons.qr_code_scanner),
                          label: Text(
                            stageType == 'enigma_final' ? 'ABRIR ENIGMA FINAL' : 'INICIAR / CONTINUAR',
                            style: const TextStyle(fontFamily: 'JetBrains Mono', fontSize: 14)),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],

            const SizedBox(height: 32),

            // QUICK ACTIONS
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => Navigator.pushNamed(context, AppRoutes.map),
                    icon: const Icon(Icons.map_outlined),
                    label: const Text('MAPA', style: TextStyle(fontFamily: 'JetBrains Mono')),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.chalk,
                      side: const BorderSide(color: AppTheme.chalk),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => Navigator.pushNamed(context, AppRoutes.audio),
                    icon: const Icon(Icons.mic),
                    label: const Text('AUDIO', style: TextStyle(fontFamily: 'JetBrains Mono')),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.chalk,
                      side: const BorderSide(color: AppTheme.chalk),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => Navigator.pushNamed(context, AppRoutes.photo),
                    icon: const Icon(Icons.camera_alt),
                    label: const Text('FOTO', style: TextStyle(fontFamily: 'JetBrains Mono')),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.chalk,
                      side: const BorderSide(color: AppTheme.chalk),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => Navigator.pushNamed(context, AppRoutes.stage),
                    icon: const Icon(Icons.menu_book),
                    label: const Text('NARRATIVA', style: TextStyle(fontFamily: 'JetBrains Mono')),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.chalk,
                      side: const BorderSide(color: AppTheme.chalk),
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
      case 'completed': return Colors.green;
      case 'active': return AppTheme.ignite;
      case 'locked': return AppTheme.chalk;
      default: return AppTheme.chalk;
    }
  }
  String _statusLabel(String? status) {
    switch (status) {
      case 'completed': return 'Concluida';
      case 'active': return 'Em andamento';
      case 'locked': return 'Bloqueada';
      default: return 'Disponivel';
    }
  }
}
