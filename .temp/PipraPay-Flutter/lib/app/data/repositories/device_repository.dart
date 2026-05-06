import '../models/device_model.dart';

abstract class DeviceRepository {
  Future<List<DeviceModel>> fetchDevices();

  Future<String> requestConnectOtp();

  Future<String> connectWithOtp(String otp);

  Future<void> sendHeartbeat();
}
