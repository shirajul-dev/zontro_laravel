import 'dart:convert';

import 'package:dio/dio.dart';

import '../../core/network/dio_client.dart';

class SmsRemoteProvider {
  SmsRemoteProvider(this._client);

  final DioClient _client;

  Future<void> sendPayload({
    required String url,
    required String headersJson,
    required Map<String, dynamic> payload,
  }) async {
    final Map<String, dynamic> decodedHeaders = _parseHeaders(headersJson);

    await _client.dio.post(
      url,
      data: payload,
      options: Options(headers: decodedHeaders),
    );
  }

  Future<void> sendCompanionTestSms({
    required String token,
    required String sender,
    required String message,
    required String simSlot,
  }) async {
    final int nowMs = DateTime.now().millisecondsSinceEpoch;

    final Response<dynamic> response = await _client.dio.post(
      '/',
      data: <String, dynamic>{
        'action-companion': 'sms-transmit-bulk',
        'token': token,
        'sms_list': jsonEncode(
          <Map<String, dynamic>>[
            <String, dynamic>{
              'id': 'test-$nowMs',
              'sender': sender,
              'message': message,
              'simSlot': simSlot,
              'timestamp': nowMs.toString(),
            },
          ],
        ),
      },
      options: Options(
        contentType: Headers.formUrlEncodedContentType,
        headers: <String, String>{'Accept': 'application/json'},
      ),
    );

    final Map<String, dynamic> json = _parseJsonMap(response.data);
    final dynamic status = json['status'];
    final bool success = status == true || status?.toString() == 'true';

    if (!success) {
      final String title = (json['title'] ?? 'Request Failed').toString();
      final String message = (json['message'] ?? 'Failed to send test SMS.').toString();
      throw Exception('$title: $message');
    }
  }

  Map<String, dynamic> _parseJsonMap(dynamic source) {
    if (source is Map<String, dynamic>) {
      return source;
    }

    if (source is String) {
      final String trimmed = source.trim();
      if (trimmed.isEmpty) {
        return <String, dynamic>{};
      }

      final Object? decoded = jsonDecode(trimmed);
      if (decoded is Map<String, dynamic>) {
        return decoded;
      }
    }

    return <String, dynamic>{};
  }

  Map<String, dynamic> _parseHeaders(String source) {
    if (source.trim().isEmpty) {
      return <String, dynamic>{};
    }

    final Object? decoded = jsonDecode(source);
    if (decoded is Map<String, dynamic>) {
      return decoded;
    }

    return <String, dynamic>{};
  }
}
