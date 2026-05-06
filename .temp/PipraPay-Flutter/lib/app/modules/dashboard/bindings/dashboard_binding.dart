import 'package:get/get.dart';

import '../../../core/services/background_service_manager.dart';
import '../../../core/services/device_info_service.dart';
import '../../../core/services/local_storage_service.dart';
import '../../../core/services/permissions_service.dart';
import '../../../data/repositories/device_repository.dart';
import '../controllers/dashboard_controller.dart';

class DashboardBinding extends Bindings {
  @override
  void dependencies() {
    Get.lazyPut<DashboardController>(
      () => DashboardController(
        Get.find<DeviceRepository>(),
        Get.find<LocalStorageService>(),
        Get.find<PermissionsService>(),
        Get.find<BackgroundServiceManager>(),
        Get.find<DeviceInfoService>(),
      ),
    );
  }
}
