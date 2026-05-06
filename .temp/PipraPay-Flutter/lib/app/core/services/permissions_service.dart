import 'package:permission_handler/permission_handler.dart';

class PermissionsService {
  Future<bool> ensureSmsPermission() async {
    final PermissionStatus status = await Permission.sms.request();
    return status.isGranted;
  }

  Future<bool> ensureNotificationPermission() async {
    final PermissionStatus status = await Permission.notification.request();
    return status.isGranted;
  }

  Future<bool> ensureBatteryOptimizationBypass() async {
    final PermissionStatus status =
        await Permission.ignoreBatteryOptimizations.request();
    return status.isGranted;
  }
}
