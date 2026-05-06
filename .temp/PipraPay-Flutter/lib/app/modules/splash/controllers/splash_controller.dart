import 'dart:async';

import 'package:get/get.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/local_storage_service.dart';
import '../../../routes/app_routes.dart';

class SplashController extends GetxController {
  SplashController(this._localStorage);

  final LocalStorageService? _localStorage;
  Timer? _navigationTimer;

  @override
  void onReady() {
    super.onReady();
    final String? token = _localStorage?.read<String>(StorageKeys.companionToken);
    final String nextRoute =
        (token != null && token.isNotEmpty) ? AppRoutes.dashboard : AppRoutes.deviceAuth;

    _navigationTimer = Timer(
      const Duration(milliseconds: 900),
      () => Get.offAllNamed(nextRoute),
    );
  }

  @override
  void onClose() {
    _navigationTimer?.cancel();
    super.onClose();
  }
}
