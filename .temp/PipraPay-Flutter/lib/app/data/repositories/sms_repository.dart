import '../models/sms_payload_model.dart';

abstract class SmsRepository {
  Future<void> sendPayload({
    required String url,
    required String headersJson,
    required SmsPayloadModel payload,
  });

  Future<void> sendCompanionTestSms({
    required String token,
    required String sender,
    required String message,
    required String simSlot,
  });
}
