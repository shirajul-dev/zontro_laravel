import 'dart:math';

import 'package:dio/dio.dart';

import '../../constants/app_environment.dart';
import '../../services/request_signature_service.dart';

class SigningInterceptor extends Interceptor {
  SigningInterceptor(this._signatureService);

  final RequestSignatureService _signatureService;

  @override
  void onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) {
    final int timestamp = DateTime.now().millisecondsSinceEpoch;
    final String requestId = _buildRequestId();
    final String secret = const String.fromEnvironment(
      'MOBILE_API_SECRET',
      defaultValue: '',
    );

    options.headers['X-App-Env'] = AppEnvironment.name;
    options.headers['X-Request-Id'] = requestId;
    options.headers['X-Timestamp'] = timestamp.toString();

    if (secret.isNotEmpty) {
      final String signature = _signatureService.buildSignature(
        method: options.method,
        path: options.path,
        timestamp: timestamp,
        payload: options.data,
        secret: secret,
      );
      options.headers['X-Signature'] = signature;
    }

    handler.next(options);
  }

  String _buildRequestId() {
    const String chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    final Random random = Random();
    return List<String>.generate(
      16,
      (_) => chars[random.nextInt(chars.length)],
    ).join();
  }
}
