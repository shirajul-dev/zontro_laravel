import '../../core/utils/template_engine.dart';

class ForwardingConfigModel {
  const ForwardingConfigModel({
    required this.key,
    required this.sender,
    required this.url,
    required this.simSlot,
    required this.template,
    required this.headers,
    required this.retriesNumber,
    required this.ignoreSsl,
    required this.chunkedMode,
    required this.isSmsEnabled,
  });

  final String key;
  final String sender;
  final String url;
  final int simSlot;
  final String template;
  final String headers;
  final int retriesNumber;
  final bool ignoreSsl;
  final bool chunkedMode;
  final bool isSmsEnabled;

  static const String defaultTemplate =
      '{\n  "from":"%from%",\n  "text":"%text%",\n  "sentStamp":%sentStamp%,\n  "receivedStamp":%receivedStamp%,\n  "sim":"%sim%"\n}';

  static const String defaultHeaders = '{"User-agent":"mh-piprapay-api-key"}';

  factory ForwardingConfigModel.fromJson(Map<String, dynamic> json) {
    return ForwardingConfigModel(
      key: (json['key'] ?? '').toString(),
      sender: (json['sender'] ?? '').toString(),
      url: (json['url'] ?? '').toString(),
      simSlot: (json['sim_slot'] ?? 0) as int,
      template: (json['template'] ?? defaultTemplate).toString(),
      headers: (json['headers'] ?? defaultHeaders).toString(),
      retriesNumber: (json['retries_number'] ?? 10) as int,
      ignoreSsl: (json['ignore_ssl'] ?? false) as bool,
      chunkedMode: (json['chunked_mode'] ?? true) as bool,
      isSmsEnabled: (json['is_sms_enabled'] ?? true) as bool,
    );
  }

  Map<String, dynamic> toJson() {
    return <String, dynamic>{
      'key': key,
      'sender': sender,
      'url': url,
      'sim_slot': simSlot,
      'template': template,
      'headers': headers,
      'retries_number': retriesNumber,
      'ignore_ssl': ignoreSsl,
      'chunked_mode': chunkedMode,
      'is_sms_enabled': isSmsEnabled,
    };
  }

  String prepareMessage({
    required String from,
    required String text,
    required String sim,
    required int sentStamp,
    required int receivedStamp,
  }) {
    return TemplateEngine.render(
      template,
      <String, String>{
        'from': from,
        'text': _escapeJson(text),
        'sentStamp': sentStamp.toString(),
        'receivedStamp': receivedStamp.toString(),
        'sim': sim,
      },
    );
  }

  String _escapeJson(String value) {
    return value
        .replaceAll(r'\\', r'\\\\')
        .replaceAll('"', r'\"')
        .replaceAll('\n', r'\n')
        .replaceAll('\r', r'\r')
        .replaceAll('\t', r'\t');
  }
}
