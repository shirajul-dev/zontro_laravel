import 'package:flutter_test/flutter_test.dart';
import 'package:piprapay_flutter/app/data/models/forwarding_config_model.dart';

void main() {
  group('ForwardingConfigModel', () {
    test('serializes and deserializes values', () {
      const ForwardingConfigModel model = ForwardingConfigModel(
        key: 'k1',
        sender: 'NAGAD',
        url: 'https://example.com/hook',
        simSlot: 1,
        template: ForwardingConfigModel.defaultTemplate,
        headers: ForwardingConfigModel.defaultHeaders,
        retriesNumber: 10,
        ignoreSsl: false,
        chunkedMode: true,
        isSmsEnabled: true,
      );

      final Map<String, dynamic> map = model.toJson();
      final ForwardingConfigModel parsed = ForwardingConfigModel.fromJson(map);

      expect(parsed.key, 'k1');
      expect(parsed.sender, 'NAGAD');
      expect(parsed.simSlot, 1);
      expect(parsed.retriesNumber, 10);
    });

    test('renders template placeholders', () {
      const ForwardingConfigModel model = ForwardingConfigModel(
        key: 'k2',
        sender: '*',
        url: 'https://example.com/hook',
        simSlot: 0,
        template: ForwardingConfigModel.defaultTemplate,
        headers: ForwardingConfigModel.defaultHeaders,
        retriesNumber: 10,
        ignoreSsl: false,
        chunkedMode: true,
        isSmsEnabled: true,
      );

      final String payload = model.prepareMessage(
        from: '01711111111',
        text: 'payment received',
        sim: 'sim1',
        sentStamp: 100,
        receivedStamp: 200,
      );

      expect(payload.contains('%from%'), isFalse);
      expect(payload.contains('01711111111'), isTrue);
      expect(payload.contains('payment received'), isTrue);
      expect(payload.contains('sim1'), isTrue);
    });
  });
}
