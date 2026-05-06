import 'package:get/get.dart';

import '../../../core/services/local_storage_service.dart';
import '../../../data/repositories/device_repository.dart';
import '../controllers/device_auth_controller.dart';

class DeviceAuthBinding extends Bindings {
  @override
  void dependencies() {
    Get.lazyPut<DeviceAuthController>(
      () => DeviceAuthController(
        Get.find<DeviceRepository>(),
        Get.find<LocalStorageService>(),
      ),
    );
  }
}
