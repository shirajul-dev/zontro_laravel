import '../../models/sms_payload_model.dart';
import '../../providers/sms_remote_provider.dart';
import '../sms_repository.dart';

class SmsRepositoryImpl implements SmsRepository {
  SmsRepositoryImpl(this._remoteProvider);

  final SmsRemoteProvider _remoteProvider;

  @override
  Future<void> sendPayload({
    required String url,
    required String headersJson,
    required SmsPayloadModel payload,
  }) {
    return _remoteProvider.sendPayload(
      url: url,
      headersJson: headersJson,
      payload: payload.toJson(),
    );
  }

  @override
  Future<void> sendCompanionTestSms({
    required String token,
    required String sender,
    required String message,
    required String simSlot,
  }) {
    return _remoteProvider.sendCompanionTestSms(
      token: token,
      sender: sender,
      message: message,
      simSlot: simSlot,
    );
  }
}
