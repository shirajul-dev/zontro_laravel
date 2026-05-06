import 'package:device_info_plus/device_info_plus.dart';

import '../../models/device_model.dart';
import '../../providers/device_remote_provider.dart';
import '../device_repository.dart';

class DeviceRepositoryImpl implements DeviceRepository {
  DeviceRepositoryImpl(this._remoteProvider, this._deviceInfo);

  final DeviceRemoteProvider _remoteProvider;
  final DeviceInfoPlugin _deviceInfo;

  @override
  Future<List<DeviceModel>> fetchDevices() => _remoteProvider.fetchDevices();

  @override
  Future<String> requestConnectOtp() => _remoteProvider.requestConnectOtp();

  @override
  Future<String> connectWithOtp(String otp) async {
    final AndroidDeviceInfo info = await _deviceInfo.androidInfo;

    return _remoteProvider.connectWithOtp(
      otp: otp,
      name: info.device,
      model: info.model,
      androidLevel: info.version.sdkInt.toString(),
      appVersion: 'piprapay_flutter',
    );
  }

  @override
  Future<void> sendHeartbeat() async {
    final AndroidDeviceInfo info = await _deviceInfo.androidInfo;

    await _remoteProvider.sendHeartbeat(
      model: info.model,
      brand: info.brand,
      version: info.version.release,
      apiLevel: info.version.sdkInt.toString(),
    );
  }
}
