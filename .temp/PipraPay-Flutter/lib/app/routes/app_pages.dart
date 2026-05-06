import 'package:get/get.dart';

import '../modules/dashboard/bindings/dashboard_binding.dart';
import '../modules/dashboard/views/dashboard_view.dart';
import '../modules/device_auth/bindings/device_auth_binding.dart';
import '../modules/device_auth/views/device_auth_view.dart';
import '../modules/device_linking/bindings/device_linking_binding.dart';
import '../modules/device_linking/views/device_linking_view.dart';
import '../modules/forwarding_config/bindings/forwarding_config_binding.dart';
import '../modules/forwarding_config/views/forwarding_config_view.dart';
import '../modules/settings/bindings/settings_binding.dart';
import '../modules/settings/views/settings_view.dart';
import '../modules/splash/bindings/splash_binding.dart';
import '../modules/splash/views/splash_view.dart';
import '../modules/sms_runtime/bindings/sms_runtime_binding.dart';
import '../modules/sms_runtime/views/sms_runtime_view.dart';
import 'app_routes.dart';

class AppPages {
  const AppPages._();

  static final List<GetPage<dynamic>> routes = <GetPage<dynamic>>[
    GetPage<SplashView>(
      name: AppRoutes.splash,
      page: SplashView.new,
      binding: SplashBinding(),
    ),
    GetPage<DeviceAuthView>(
      name: AppRoutes.deviceAuth,
      page: DeviceAuthView.new,
      binding: DeviceAuthBinding(),
    ),
    GetPage<DashboardView>(
      name: AppRoutes.dashboard,
      page: DashboardView.new,
      binding: DashboardBinding(),
    ),
    GetPage<ForwardingConfigView>(
      name: AppRoutes.forwardingConfig,
      page: ForwardingConfigView.new,
      binding: ForwardingConfigBinding(),
    ),
    GetPage<DeviceLinkingView>(
      name: AppRoutes.deviceLinking,
      page: DeviceLinkingView.new,
      binding: DeviceLinkingBinding(),
    ),
    GetPage<SmsRuntimeView>(
      name: AppRoutes.smsRuntime,
      page: SmsRuntimeView.new,
      binding: SmsRuntimeBinding(),
    ),
    GetPage<SettingsView>(
      name: AppRoutes.settings,
      page: SettingsView.new,
      binding: SettingsBinding(),
    ),
  ];
}
