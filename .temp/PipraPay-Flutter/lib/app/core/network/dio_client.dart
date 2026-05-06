import 'package:dio/dio.dart';

import '../constants/app_environment.dart';

class DioClient {
  DioClient()
      : dio = Dio(
          BaseOptions(
            baseUrl: AppEnvironment.apiBaseUrl,
            connectTimeout: const Duration(seconds: 30),
            receiveTimeout: const Duration(seconds: 30),
            sendTimeout: const Duration(seconds: 30),
            headers: const <String, String>{
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
          ),
        );

  final Dio dio;
}
