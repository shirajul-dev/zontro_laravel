import 'package:flutter_test/flutter_test.dart';
import 'package:piprapay_flutter/app/data/models/device_model.dart';

void main() {
  group('DeviceModel', () {
    test('creates model from json and serializes back', () {
      const Map<String, dynamic> input = <String, dynamic>{
        'id': '123456',
        'status': 'used',
        'name': 'Office Phone',
        'model': 'Pixel 7',
        'android_level': '34',
        'created_date': '2026-01-01 10:00:00',
        'updated_date': '2026-01-01 10:05:00',
        'last_sync': '2026-01-01 10:06:00',
      };

      final DeviceModel model = DeviceModel.fromJson(input);
      final Map<String, dynamic> output = model.toJson();

      expect(output['id'], '123456');
      expect(output['status'], 'used');
      expect(output['model'], 'Pixel 7');
      expect(output['android_level'], '34');
    });
  });
}
