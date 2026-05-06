import 'dart:convert';

import 'package:crypto/crypto.dart';

class RequestSignatureService {
  const RequestSignatureService();

  String buildSignature({
    required String method,
    required String path,
    required int timestamp,
    required Object? payload,
    required String secret,
  }) {
    final String body = payload == null ? '' : jsonEncode(payload);
    final String canonical = '${method.toUpperCase()}|$path|$timestamp|$body';
    final Hmac hmac = Hmac(sha256, utf8.encode(secret));
    final Digest digest = hmac.convert(utf8.encode(canonical));
    return digest.toString();
  }
}
