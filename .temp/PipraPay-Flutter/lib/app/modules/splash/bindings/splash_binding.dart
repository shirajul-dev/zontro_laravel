import 'package:get/get.dart';

import '../../../core/services/local_storage_service.dart';
import '../controllers/splash_controller.dart';

class SplashBinding extends Bindings {
  @override
  void dependencies() {
    final LocalStorageService? localStorage =
        Get.isRegistered<LocalStorageService>() ? Get.find<LocalStorageService>() : null;
    Get.put<SplashController>(SplashController(localStorage));
  }
}
