import 'package:get/get.dart';

import '../../../core/services/local_storage_service.dart';
import '../../../data/repositories/delivery_log_repository.dart';
import '../../../data/repositories/forwarding_config_repository.dart';
import '../../../data/repositories/sms_repository.dart';
import '../controllers/sms_runtime_controller.dart';
import '../services/sms_forward_engine.dart';

class SmsRuntimeBinding extends Bindings {
  @override
  void dependencies() {
    Get.lazyPut<SmsForwardEngine>(
      () => SmsForwardEngine(
        Get.find<SmsRepository>(),
        Get.find<ForwardingConfigRepository>(),
        Get.find<DeliveryLogRepository>(),
      ),
    );

    Get.lazyPut<SmsRuntimeController>(
      () => SmsRuntimeController(
        Get.find<SmsForwardEngine>(),
        Get.find<DeliveryLogRepository>(),
        Get.find<SmsRepository>(),
        Get.find<LocalStorageService>(),
      ),
    );
  }
}
