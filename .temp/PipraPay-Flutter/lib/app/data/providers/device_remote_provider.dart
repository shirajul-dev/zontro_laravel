import 'dart:convert';

import 'package:dio/dio.dart';

import '../../core/network/dio_client.dart';
import '../models/api_response.dart';
import '../models/device_model.dart';

class DeviceRemoteProvider {
  DeviceRemoteProvider(this._client);

  final DioClient _client;

  Future<List<DeviceModel>> fetchDevices() async {
    final Response<dynamic> response = await _client.dio.post(
      '/admin/dashboard',
      data: <String, dynamic>{'action': 'device-list'},
    );

    final Map<String, dynamic> json = _parseJsonMap(response.data);
    final ApiResponse<List<DeviceModel>> parsed = ApiResponse<List<DeviceModel>>.fromJson(
      json,
      (Object? payload) {
        final List<dynamic> rows = payload as List<dynamic>? ?? <dynamic>[];
        return rows
            .map((dynamic item) => DeviceModel.fromJson(item as Map<String, dynamic>))
            .toList();
      },
    );

    return parsed.data ?? <DeviceModel>[];
  } 

  Future<String> requestConnectOtp() async {
    final Response<dynamic> response = await _client.dio.post(
      '/admin/dashboard',
      data: <String, dynamic>{'action': 'device-connect-info'},
    );

    final Map<String, dynamic> json = _parseJsonMap(response.data);
    return (json['otp'] ?? '').toString();
  }

  Future<String> connectWithOtp({
    required String otp,
    required String name,
    required String model,
    required String androidLevel,
    required String appVersion,
  }) async {
    final Response<dynamic> response = await _client.dio.post(
      '/',
      data: <String, dynamic>{
        'action-companion': 'login',
        'onetimepassword': otp,
        'name': name,
        'model': model,
        'android_level': androidLevel,
        'app_version': appVersion,
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
      throw Exception((json['message'] ?? 'Authentication failed.').toString());
    }

    final String token = (json['token'] ?? '').toString();
    if (token.isEmpty) {
      throw Exception('Authentication token was not returned by server.');
    }

    return token;
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

      try {
        final Object? decoded = jsonDecode(trimmed);
        if (decoded is Map<String, dynamic>) {
          return decoded;
        }
      } catch (_) {
        return <String, dynamic>{};
      }
    }

    return <String, dynamic>{};
  }

  Future<void> sendHeartbeat({
    required String model,
    required String brand,
    required String version,
    required String apiLevel,
  }) async {
    await _client.dio.post(
      '/mobile/heartbeat',
      data: <String, dynamic>{
        'check': 'i_am_active',
        'd_model': model,
        'd_brand': brand,
        'd_version': version,
        'd_api_level': apiLevel,
      },
    );
  }
}
