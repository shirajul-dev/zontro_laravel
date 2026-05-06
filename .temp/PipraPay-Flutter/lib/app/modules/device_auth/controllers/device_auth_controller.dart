import 'package:flutter/widgets.dart';
import 'package:get/get.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/local_storage_service.dart';
import '../../../data/repositories/device_repository.dart';
import '../../../routes/app_routes.dart';

class DeviceAuthController extends GetxController {
  DeviceAuthController(this._deviceRepository, this._localStorage);

  final DeviceRepository _deviceRepository;
  final LocalStorageService _localStorage;

  final TextEditingController otpController = TextEditingController();
  final RxBool isSubmitting = false.obs;
  final RxString status =
      'Enter the one-time password from your dashboard to connect this device.'
          .obs;

  String get panelUrl => AppConstants.panelBaseUrl;

  Future<void> authenticate() async {
    final String otp = otpController.text.trim();
    if (otp.isEmpty) {
      status.value = 'One-time password is required.';
      return;
    }

    try {
      isSubmitting.value = true;
      final String token = await _deviceRepository.connectWithOtp(otp);

      if (token.isEmpty) {
        status.value = 'Authentication failed. Please try again.';
        return;
      }

      await _localStorage.write(StorageKeys.companionToken, token);
      status.value = 'Device connected successfully.';
      Get.offAllNamed(AppRoutes.dashboard);
    } catch (error) {
      status.value = 'Authentication failed: $error';
    } finally {
      isSubmitting.value = false;
    }
  }

  @override
  void onClose() {
    otpController.dispose();
    super.onClose();
  }
}
