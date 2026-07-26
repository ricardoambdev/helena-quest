import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
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
  // ignore: unused_field
  GoogleMapController? _mapController;
  Set<Marker> _markers = {};

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
          markerId: MarkerId(t['id']?.toString() ?? name),
          position: LatLng(lat, lng),
          infoWindow: InfoWindow(title: name),
          icon: BitmapDescriptor.defaultMarkerWithHue(
            BitmapDescriptor.hueOrange,
          ),
        );
      }).toSet();
    }
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
            markerId: MarkerId(t['id']?.toString() ?? name),
            position: LatLng(lat, lng),
            infoWindow: InfoWindow(title: name),
            icon: BitmapDescriptor.defaultMarkerWithHue(
              BitmapDescriptor.hueOrange,
            ),
          );
        }).toSet();
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
      body: GoogleMap(
        initialCameraPosition: CameraPosition(
          target: LatLng(
            AppConstants.mapDefaultLat,
            AppConstants.mapDefaultLng,
          ),
          zoom: AppConstants.mapDefaultZoom,
        ),
        markers: _markers,
        onMapCreated: (controller) {
          _mapController = controller;
        },
        myLocationEnabled: true,
        myLocationButtonEnabled: true,
        mapType: MapType.normal,
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
