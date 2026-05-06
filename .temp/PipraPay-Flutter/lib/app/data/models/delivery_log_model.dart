class DeliveryLogModel {
  const DeliveryLogModel({
    required this.time,
    required this.sender,
    required this.sim,
    required this.url,
    required this.configKey,
    required this.attempts,
    required this.status,
    required this.error,
  });

  final String time;
  final String sender;
  final String sim;
  final String url;
  final String configKey;
  final int attempts;
  final String status;
  final String error;

  Map<String, dynamic> toJson() {
    return <String, dynamic>{
      'time': time,
      'sender': sender,
      'sim': sim,
      'url': url,
      'config_key': configKey,
      'attempts': attempts,
      'status': status,
      'error': error,
    };
  }

  factory DeliveryLogModel.fromJson(Map<String, dynamic> json) {
    return DeliveryLogModel(
      time: (json['time'] ?? '').toString(),
      sender: (json['sender'] ?? '').toString(),
      sim: (json['sim'] ?? '').toString(),
      url: (json['url'] ?? '').toString(),
      configKey: (json['config_key'] ?? '').toString(),
      attempts: (json['attempts'] ?? 0) as int,
      status: (json['status'] ?? '').toString(),
      error: (json['error'] ?? '').toString(),
    );
  }
}
