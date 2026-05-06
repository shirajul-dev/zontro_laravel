import '../../../data/models/delivery_log_model.dart';
import '../../../data/models/forwarding_config_model.dart';
import '../../../data/models/sms_payload_model.dart';
import '../../../data/repositories/delivery_log_repository.dart';
import '../../../data/repositories/forwarding_config_repository.dart';
import '../../../data/repositories/sms_repository.dart';

class SmsForwardEngine {
  SmsForwardEngine(
    this._smsRepository,
    this._configRepository,
    this._deliveryLogRepository,
  );

  final SmsRepository _smsRepository;
  final ForwardingConfigRepository _configRepository;
  final DeliveryLogRepository _deliveryLogRepository;

  Future<int> forwardIncomingSms({
    required String sender,
    required String content,
    required String sim,
    required int sentStamp,
  }) async {
    final List<ForwardingConfigModel> allConfigs = await _configRepository.getAll();

    final List<ForwardingConfigModel> matched = allConfigs.where((ForwardingConfigModel config) {
      final bool senderMatch = config.sender == sender || config.sender == '*';
      final bool simMatch = config.simSlot == 0 || 'sim${config.simSlot}' == sim;
      return config.isSmsEnabled && senderMatch && simMatch;
    }).toList();

    for (final ForwardingConfigModel config in matched) {
      await _deliverWithRetry(
        config: config,
        payload: SmsPayloadModel(
          from: sender,
          text: content,
          sentStamp: sentStamp,
          receivedStamp: DateTime.now().millisecondsSinceEpoch,
          sim: sim,
        ),
      );
    }

    return matched.length;
  }

  Future<void> _deliverWithRetry({
    required ForwardingConfigModel config,
    required SmsPayloadModel payload,
  }) async {
    int attempt = 0;

    while (attempt <= config.retriesNumber) {
      try {
        await _smsRepository.sendPayload(
          url: config.url,
          headersJson: config.headers,
          payload: payload,
        );

        await _deliveryLogRepository.add(
          DeliveryLogModel(
            time: DateTime.now().toIso8601String(),
            sender: payload.from,
            sim: payload.sim,
            url: config.url,
            configKey: config.key,
            attempts: attempt + 1,
            status: 'success',
            error: '',
          ),
        );
        return;
      } catch (error) {
        if (attempt >= config.retriesNumber) {
          await _deliveryLogRepository.add(
            DeliveryLogModel(
              time: DateTime.now().toIso8601String(),
              sender: payload.from,
              sim: payload.sim,
              url: config.url,
              configKey: config.key,
              attempts: attempt + 1,
              status: 'failed',
              error: error.toString(),
            ),
          );
          rethrow;
        }

        final int delayMs = (500 * (1 << attempt)).clamp(500, 30000);
        await Future<void>.delayed(Duration(milliseconds: delayMs));
      }

      attempt++;
    }
  }
}
