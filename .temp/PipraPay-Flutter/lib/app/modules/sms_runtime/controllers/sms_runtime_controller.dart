import 'package:get/get.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/local_storage_service.dart';
import '../../../data/models/delivery_log_model.dart';
import '../../../data/repositories/delivery_log_repository.dart';
import '../../../data/repositories/sms_repository.dart';
import '../services/sms_forward_engine.dart';

class SmsRuntimeController extends GetxController {
  SmsRuntimeController(
    this._engine,
    this._deliveryLogRepository,
    this._smsRepository,
    this._localStorage,
  );

  final SmsForwardEngine _engine;
  final DeliveryLogRepository _deliveryLogRepository;
  final SmsRepository _smsRepository;
  final LocalStorageService _localStorage;

  final RxBool isForwarding = false.obs;
  final RxString status = 'Ready to simulate incoming SMS.'.obs;
  final RxList<DeliveryLogModel> logs = <DeliveryLogModel>[].obs;

  @override
  void onInit() {
    super.onInit();
    loadLogs();
  }

  Future<void> simulateForward() async {
    try {
      isForwarding.value = true;
      final int matched = await _engine.forwardIncomingSms(
        sender: 'NAGAD',
        content: 'Cash In BDT 100 to Merchant',
        sim: 'sim1',
        sentStamp: DateTime.now().millisecondsSinceEpoch,
      );
      status.value = 'Forwarded using $matched matching configuration(s).';
      await loadLogs();
    } catch (error) {
      status.value = 'Forwarding failed: $error';
      await loadLogs();
    } finally {
      isForwarding.value = false;
    }
  }

  Future<void> sendBackendTestSms() async {
    final String? token = _localStorage.read<String>(StorageKeys.companionToken);
    if (token == null || token.isEmpty) {
      status.value = 'Device token missing. Please connect device again.';
      return;
    }

    try {
      isForwarding.value = true;
      await _smsRepository.sendCompanionTestSms(
        token: token,
        sender: 'bkash',
        message:
            'Cash In Tk 125 from 01712345678 successful. Fee Tk 0.00. Balance Tk 5000.00. TrxID TEST12345 at 20/04/2026 10:30:00',
        simSlot: '1',
      );
      status.value = 'Test SMS sent to backend successfully.';
    } catch (error) {
      status.value = 'Backend test SMS failed: $error';
    } finally {
      isForwarding.value = false;
    }
  }

  Future<void> loadLogs() async {
    final List<DeliveryLogModel> allLogs = await _deliveryLogRepository.list();
    logs.assignAll(allLogs);
  }

  Future<void> clearLogs() async {
    await _deliveryLogRepository.clear();
    logs.clear();
    status.value = 'Delivery logs cleared.';
  }
}
