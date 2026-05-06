import 'package:get/get.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/background_service_manager.dart';
import '../../../core/services/device_info_service.dart';
import '../../../core/services/local_storage_service.dart';
import '../../../core/services/permissions_service.dart';
import '../../../data/models/device_model.dart';
import '../../../data/repositories/device_repository.dart';
import '../../../routes/app_routes.dart';

class DashboardController extends GetxController {
  DashboardController(
    this._deviceRepository,
    this._localStorage,
    this._permissionsService,
    this._backgroundServiceManager,
    this._deviceInfoService,
  );

  final DeviceRepository _deviceRepository;
  final LocalStorageService _localStorage;
  final PermissionsService _permissionsService;
  final BackgroundServiceManager _backgroundServiceManager;
  final DeviceInfoService _deviceInfoService;

  final RxString status = 'Phase 2 data layer is wired'.obs;
  final RxBool isLoading = false.obs;
  final RxInt deviceCount = 0.obs;
  final RxBool smsPermissionGranted = false.obs;
  final RxBool backgroundRuntimeReady = false.obs;
  final RxString deviceSummary = ''.obs;

  Future<void> loadDevices() async {
    await _runSafely(() async {
      final List<DeviceModel> devices = await _deviceRepository.fetchDevices();
      deviceCount.value = devices.length;
      status.value = 'Loaded ${devices.length} devices from backend.';
    });
  }

  Future<void> ensureSmsPermission() async {
    await _runSafely(() async {
      smsPermissionGranted.value = await _permissionsService.ensureSmsPermission();
      status.value = smsPermissionGranted.value
          ? 'SMS permission granted.'
          : 'SMS permission denied.';
    });
  }

  Future<void> logout() async {
    await _localStorage.remove(StorageKeys.companionToken);
    Get.offAllNamed(AppRoutes.deviceAuth);
  }

  Future<void> initializeBackgroundRuntime() async {
    await _runSafely(() async {
      await _backgroundServiceManager.registerHeartbeatTask();
      backgroundRuntimeReady.value = true;
      status.value = 'Background runtime initialized with heartbeat task.';
    });
  }

  Future<void> loadDeviceInfoSummary() async {
    await _runSafely(() async {
      final Map<String, String> info = await _deviceInfoService.readMetadata();
      deviceSummary.value =
          '${info['brand']} ${info['model']} (Android ${info['version']} / API ${info['apiLevel']})';
      status.value = 'Device metadata loaded.';
    });
  }

  Future<void> _runSafely(Future<void> Function() action) async {
    try {
      isLoading.value = true;
      await action();
    } catch (error) {
      status.value = 'Request failed: $error';
    } finally {
      isLoading.value = false;
    }
  }
}
