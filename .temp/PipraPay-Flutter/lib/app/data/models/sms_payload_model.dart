class SmsPayloadModel {
  const SmsPayloadModel({
    required this.from,
    required this.text,
    required this.sentStamp,
    required this.receivedStamp,
    required this.sim,
  });

  final String from;
  final String text;
  final int sentStamp;
  final int receivedStamp;
  final String sim;

  Map<String, dynamic> toJson() {
    return <String, dynamic>{
      'from': from,
      'text': text,
      'sentStamp': sentStamp,
      'receivedStamp': receivedStamp,
      'sim': sim,
    };
  }
}
