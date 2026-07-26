import 'package:geolocator/geolocator.dart';

class LocationService {
  Future<bool> requestPermission() async {
    final status = await Geolocator.requestPermission();
    return status == LocationPermission.always ||
        status == LocationPermission.whileInUse;
  }

  Future<Position?> getCurrentPosition() async {
    try {
      return await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          distanceFilter: 5,
        ),
      );
    } catch (_) {
      return null;
    }
  }

  Future<double> distanceTo(
    double lat1, double lng1,
    double lat2, double lng2,
  ) async {
    return Geolocator.distanceBetween(lat1, lng1, lat2, lng2);
  }
}
