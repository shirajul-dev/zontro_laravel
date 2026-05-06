class AppEnvironment {
  AppEnvironment._();

  static String name = 'prod';
  static String apiBaseUrl = 'https://example.com';

  static void configure({
    required String envName,
    required String baseUrl,
  }) {
    name = envName;
    apiBaseUrl = baseUrl;
  }
}
