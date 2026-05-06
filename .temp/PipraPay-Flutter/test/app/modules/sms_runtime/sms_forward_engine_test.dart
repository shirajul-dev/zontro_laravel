import 'package:flutter_test/flutter_test.dart';
import 'package:piprapay_flutter/app/data/models/delivery_log_model.dart';
import 'package:piprapay_flutter/app/data/models/forwarding_config_model.dart';
import 'package:piprapay_flutter/app/data/models/sms_payload_model.dart';
import 'package:piprapay_flutter/app/data/repositories/delivery_log_repository.dart';
import 'package:piprapay_flutter/app/data/repositories/forwarding_config_repository.dart';
import 'package:piprapay_flutter/app/data/repositories/sms_repository.dart';
import 'package:piprapay_flutter/app/modules/sms_runtime/services/sms_forward_engine.dart';

class _FakeForwardingConfigRepository implements ForwardingConfigRepository {
  _FakeForwardingConfigRepository(this._configs);

  final List<ForwardingConfigModel> _configs;

  @override
  Future<void> deleteByKey(String key) async {}

  @override
  Future<List<ForwardingConfigModel>> getAll() async => _configs;

  @override
  Future<void> save(ForwardingConfigModel config) async {}
}

class _FakeSmsRepository implements SmsRepository {
  final List<Map<String, dynamic>> calls = <Map<String, dynamic>>[];

  @override
  Future<void> sendCompanionTestSms({
    required String token,
    required String sender,
    required String message,
    required String simSlot,
  }) async {}

  @override
  Future<void> sendPayload({
    required String url,
    required String headersJson,
    required SmsPayloadModel payload,
  }) async {
    calls.add(<String, dynamic>{
      'url': url,
      'headers': headersJson,
      'payload': payload.toJson(),
    });
  }
}

class _FakeDeliveryLogRepository implements DeliveryLogRepository {
  final List<DeliveryLogModel> items = <DeliveryLogModel>[];

  @override
  Future<void> add(DeliveryLogModel log) async {
    items.add(log);
  }

  @override
  Future<void> clear() async {
    items.clear();
  }

  @override
  Future<List<DeliveryLogModel>> list() async => items;
}

void main() {
  test('forwards only matching sender or wildcard configs', () async {
    final List<ForwardingConfigModel> configs = <ForwardingConfigModel>[
      const ForwardingConfigModel(
        key: '1',
        sender: 'NAGAD',
        url: 'https://example.com/a',
        simSlot: 0,
        template: ForwardingConfigModel.defaultTemplate,
        headers: ForwardingConfigModel.defaultHeaders,
        retriesNumber: 1,
        ignoreSsl: false,
        chunkedMode: true,
        isSmsEnabled: true,
      ),
      const ForwardingConfigModel(
        key: '2',
        sender: '*',
        url: 'https://example.com/b',
        simSlot: 0,
        template: ForwardingConfigModel.defaultTemplate,
        headers: ForwardingConfigModel.defaultHeaders,
        retriesNumber: 1,
        ignoreSsl: false,
        chunkedMode: true,
        isSmsEnabled: true,
      ),
      const ForwardingConfigModel(
        key: '3',
        sender: 'BKASH',
        url: 'https://example.com/c',
        simSlot: 0,
        template: ForwardingConfigModel.defaultTemplate,
        headers: ForwardingConfigModel.defaultHeaders,
        retriesNumber: 1,
        ignoreSsl: false,
        chunkedMode: true,
        isSmsEnabled: true,
      ),
    ];

    final _FakeSmsRepository smsRepo = _FakeSmsRepository();
    final _FakeDeliveryLogRepository logRepo = _FakeDeliveryLogRepository();
    final SmsForwardEngine engine = SmsForwardEngine(
      smsRepo,
      _FakeForwardingConfigRepository(configs),
      logRepo,
    );

    final int count = await engine.forwardIncomingSms(
      sender: 'NAGAD',
      content: 'test',
      sim: 'sim1',
      sentStamp: 123,
    );

    expect(count, 2);
    expect(smsRepo.calls.length, 2);
    expect(logRepo.items.length, 2);
    expect(logRepo.items.every((DeliveryLogModel e) => e.status == 'success'), isTrue);
  });
}
