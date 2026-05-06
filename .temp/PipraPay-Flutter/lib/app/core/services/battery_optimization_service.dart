import 'package:permission_handler/permission_handler.dart';

class BatteryOptimizationService {
  Future<bool> isIgnored() async {
    final PermissionStatus status = await Permission.ignoreBatteryOptimizations.status;
    return status.isGranted;
  }

  Future<bool> requestIgnore() async {
    final PermissionStatus status = await Permission.ignoreBatteryOptimizations.request();
    return status.isGranted;
  }
}
