import 'package:get/get.dart';

import '../../../data/repositories/device_repository.dart';
import '../controllers/device_linking_controller.dart';

class DeviceLinkingBinding extends Bindings {
  @override
  void dependencies() {
    Get.lazyPut<DeviceLinkingController>(
      () => DeviceLinkingController(Get.find<DeviceRepository>()),
    );
  }
}
