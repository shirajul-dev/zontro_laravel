import 'package:get/get.dart';

import '../../../data/models/device_model.dart';
import '../../../data/repositories/device_repository.dart';

class DeviceLinkingController extends GetxController {
  DeviceLinkingController(this._deviceRepository);

  final DeviceRepository _deviceRepository;

  final RxBool isLoading = false.obs;
  final RxString status = 'Ready for device linking actions.'.obs;
  final RxString latestOtp = ''.obs;
  final RxList<DeviceModel> devices = <DeviceModel>[].obs;

  Future<void> fetchDevices() async {
    await _runSafely(() async {
      final List<DeviceModel> all = await _deviceRepository.fetchDevices();
      devices.assignAll(all);
      status.value = 'Loaded ${all.length} linked device(s).';
    });
  }

  Future<void> requestOtp() async {
    await _runSafely(() async {
      final String otp = await _deviceRepository.requestConnectOtp();
      latestOtp.value = otp;
      status.value = otp.isEmpty ? 'No OTP returned.' : 'New OTP: $otp';
    });
  }

  Future<void> sendHeartbeat() async {
    await _runSafely(() async {
      await _deviceRepository.sendHeartbeat();
      status.value = 'Heartbeat sent successfully.';
    });
  }

  Future<void> _runSafely(Future<void> Function() action) async {
    try {
      isLoading.value = true;
      await action();
    } catch (error) {
      status.value = 'Operation failed: $error';
    } finally {
      isLoading.value = false;
    }
  }
}
