import 'package:geolocator/geolocator.dart';

/// A rough position for "near me" lists, or null.
///
/// Never throws and never blocks for long: a farmer who refuses location, has
/// GPS off, or is indoors with no fix still gets results by region. Coarse
/// accuracy is enough to sort by kilometres and is easier on the battery.
class LocationService {
  static Future<({double lat, double lng})?> approximate() async {
    try {
      if (!await Geolocator.isLocationServiceEnabled()) return null;

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        return null;
      }

      // A cached fix is instant, but the web plugin throws "not supported"
      // here, which must not cost us the fresh fix below.
      Position? last;
      try {
        last = await Geolocator.getLastKnownPosition();
      } catch (_) {}
      final position =
          last ??
          await Geolocator.getCurrentPosition(
            locationSettings: const LocationSettings(
              accuracy: LocationAccuracy.low,
              timeLimit: Duration(seconds: 10),
            ),
          );
      return (lat: position.latitude, lng: position.longitude);
    } catch (_) {
      return null;
    }
  }
}
