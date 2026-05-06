import 'package:flutter_test/flutter_test.dart';
import 'package:piprapay_flutter/app/data/models/api_response.dart';

void main() {
  group('ApiResponse', () {
    test('parses string status and payload from response key', () {
      final ApiResponse<String> parsed = ApiResponse<String>.fromJson(
        <String, dynamic>{
          'status': 'true',
          'message': 'ok',
          'response': 'value',
        },
        (Object? payload) => payload.toString(),
      );

      expect(parsed.status, isTrue);
      expect(parsed.message, 'ok');
      expect(parsed.data, 'value');
    });

    test('parses boolean status and payload from data key', () {
      final ApiResponse<int> parsed = ApiResponse<int>.fromJson(
        <String, dynamic>{
          'status': true,
          'data': 42,
        },
        (Object? payload) => payload as int,
      );

      expect(parsed.status, isTrue);
      expect(parsed.data, 42);
    });
  });
}
