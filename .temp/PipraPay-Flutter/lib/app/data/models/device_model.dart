class DeviceModel {
  const DeviceModel({
    required this.deviceId,
    required this.status,
    required this.name,
    required this.model,
    required this.androidLevel,
    required this.createdDate,
    required this.updatedDate,
    required this.lastSync,
  });

  final String deviceId;
  final String status;
  final String name;
  final String model;
  final String androidLevel;
  final String createdDate;
  final String updatedDate;
  final String lastSync;

  factory DeviceModel.fromJson(Map<String, dynamic> json) {
    return DeviceModel(
      deviceId: (json['id'] ?? json['device_id'] ?? '').toString(),
      status: (json['status'] ?? '').toString(),
      name: (json['name'] ?? '').toString(),
      model: (json['model'] ?? '').toString(),
      androidLevel: (json['android_level'] ?? '').toString(),
      createdDate: (json['created_date'] ?? '').toString(),
      updatedDate: (json['updated_date'] ?? '').toString(),
      lastSync: (json['last_sync'] ?? '').toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return <String, dynamic>{
      'id': deviceId,
      'status': status,
      'name': name,
      'model': model,
      'android_level': androidLevel,
      'created_date': createdDate,
      'updated_date': updatedDate,
      'last_sync': lastSync,
    };
  }
}
