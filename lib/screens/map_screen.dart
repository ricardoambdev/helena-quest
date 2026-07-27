import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';
import 'package:helena_quest_app/config/theme.dart';
import 'package:helena_quest_app/config/constants.dart';
import 'package:helena_quest_app/providers/team_provider.dart';

class MapScreen extends StatefulWidget {
  final List<Map<String, dynamic>>? teams;

  const MapScreen({super.key, this.teams});

  @override
  State<MapScreen> createState() => _MapScreenState();
}

class _MapScreenState extends State<MapScreen> {
  final MapController _mapController = MapController();
  List<Marker> _markers = [];

  @override
  void initState() {
    super.initState();
    _buildMarkers();
  }

  void _buildMarkers() {
    final teams = widget.teams;
    if (teams != null) {
      _markers = teams.map((t) {
        final lat = (t['latitude'] as num?)?.toDouble() ?? 0;
        final lng = (t['longitude'] as num?)?.toDouble() ?? 0;
        final name = t['name'] as String? ?? 'Time';
        return Marker(
          point: LatLng(lat, lng),
          child: GestureDetector(
            onTap: () => _showTeamInfo(name),
            child: const Icon(Icons.location_on, color: AppTheme.ignite, size: 36),
          ),
        );
      }).toList();
    }
  }

  void _showTeamInfo(String name) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(name), duration: const Duration(seconds: 2)),
    );
  }

  void _atualizar() async {
    final teamProv = context.read<TeamProvider>();
    await teamProv.loadProgress('current');
    final teams = teamProv.progress;
    if (teams.isNotEmpty) {
      setState(() {
        _markers = teams.map((t) {
          final lat = (t['latitude'] as num?)?.toDouble() ?? 0;
          final lng = (t['longitude'] as num?)?.toDouble() ?? 0;
          final name = t['name'] as String? ?? 'Time';
          return Marker(
            point: LatLng(lat, lng),
            child: GestureDetector(
              onTap: () => _showTeamInfo(name),
              child: const Icon(Icons.location_on, color: AppTheme.ignite, size: 36),
            ),
          );
        }).toList();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'MAPA',
          style: TextStyle(
            fontFamily: 'JetBrains Mono',
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
      ),
      body: FlutterMap(
        mapController: _mapController,
        options: MapOptions(
          initialCenter: LatLng(
            AppConstants.mapDefaultLat,
            AppConstants.mapDefaultLng,
          ),
          initialZoom: AppConstants.mapDefaultZoom,
          interactionOptions: const InteractionOptions(
            flags: InteractiveFlag.all,
          ),
        ),
        children: [
          TileLayer(
            urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            userAgentPackageName: 'com.helenaquest.app',
          ),
          MarkerLayer(markers: _markers),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _atualizar,
        backgroundColor: AppTheme.ignite,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.refresh),
        label: const Text(
          'ATUALIZAR',
          style: TextStyle(
            fontFamily: 'JetBrains Mono',
            fontWeight: FontWeight.w500,
          ),
        ),
      ),
    );
  }
}
