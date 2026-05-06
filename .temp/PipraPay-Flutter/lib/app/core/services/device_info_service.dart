import 'package:device_info_plus/device_info_plus.dart';

class DeviceInfoService {
  DeviceInfoService(this._deviceInfo);

  final DeviceInfoPlugin _deviceInfo;

  Future<Map<String, String>> readMetadata() async {
    final AndroidDeviceInfo info = await _deviceInfo.androidInfo;
    return <String, String>{
      'model': info.model,
      'brand': info.brand,
      'version': info.version.release,
      'apiLevel': info.version.sdkInt.toString(),
      'device': info.device,
      'manufacturer': info.manufacturer,
    };
  }
}
