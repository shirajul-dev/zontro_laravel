import 'package:get/get.dart';

import '../../../data/repositories/forwarding_config_repository.dart';
import '../controllers/forwarding_config_controller.dart';

class ForwardingConfigBinding extends Bindings {
  @override
  void dependencies() {
    Get.lazyPut<ForwardingConfigController>(
      () => ForwardingConfigController(Get.find<ForwardingConfigRepository>()),
    );
  }
}
