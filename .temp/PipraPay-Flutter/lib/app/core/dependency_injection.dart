import 'package:get/get.dart';
import 'package:device_info_plus/device_info_plus.dart';

import '../data/providers/delivery_log_local_provider.dart';
import '../data/providers/device_remote_provider.dart';
import '../data/providers/forwarding_config_local_provider.dart';
import '../data/providers/sms_remote_provider.dart';
import '../data/repositories/delivery_log_repository.dart';
import '../data/repositories/device_repository.dart';
import '../data/repositories/forwarding_config_repository.dart';
import '../data/repositories/impl/delivery_log_repository_impl.dart';
import '../data/repositories/impl/device_repository_impl.dart';
import '../data/repositories/impl/forwarding_config_repository_impl.dart';
import '../data/repositories/impl/sms_repository_impl.dart';
import '../data/repositories/sms_repository.dart';
import 'network/dio_client.dart';
import 'network/interceptors/signing_interceptor.dart';
import 'services/local_storage_service.dart';
import 'services/battery_optimization_service.dart';
import 'services/background_service_manager.dart';
import 'services/device_info_service.dart';
import 'services/permissions_service.dart';
import 'services/request_signature_service.dart';
import 'services/secure_storage_service.dart';

class DependencyInjection {
  const DependencyInjection._();

  static Future<void> init() async {
    await Get.putAsync<LocalStorageService>(LocalStorageService.init);
    Get.put<SecureStorageService>(SecureStorageService());
    Get.put<PermissionsService>(PermissionsService());
    Get.put<DeviceInfoPlugin>(DeviceInfoPlugin());
    Get.put<BatteryOptimizationService>(BatteryOptimizationService());
    Get.put<BackgroundServiceManager>(BackgroundServiceManager());
    Get.put<DeviceInfoService>(DeviceInfoService(Get.find<DeviceInfoPlugin>()));
    Get.put<RequestSignatureService>(const RequestSignatureService());

    final DioClient dioClient = Get.put<DioClient>(DioClient());
    final LocalStorageService localStorage = Get.find<LocalStorageService>();

    dioClient.dio.interceptors.add(
      SigningInterceptor(Get.find<RequestSignatureService>()),
    );

    Get.put<DeviceRemoteProvider>(DeviceRemoteProvider(dioClient));
    Get.put<ForwardingConfigLocalProvider>(
      ForwardingConfigLocalProvider(localStorage),
    );
    Get.put<DeliveryLogLocalProvider>(DeliveryLogLocalProvider(localStorage));
    Get.put<SmsRemoteProvider>(SmsRemoteProvider(dioClient));

    Get.put<DeviceRepository>(
      DeviceRepositoryImpl(
        Get.find<DeviceRemoteProvider>(),
        Get.find<DeviceInfoPlugin>(),
      ),
    );
    Get.put<ForwardingConfigRepository>(
      ForwardingConfigRepositoryImpl(Get.find<ForwardingConfigLocalProvider>()),
    );
    Get.put<DeliveryLogRepository>(
      DeliveryLogRepositoryImpl(Get.find<DeliveryLogLocalProvider>()),
    );
    Get.put<SmsRepository>(SmsRepositoryImpl(Get.find<SmsRemoteProvider>()));
  }
}
