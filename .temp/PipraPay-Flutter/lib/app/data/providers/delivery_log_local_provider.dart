import '../../core/services/local_storage_service.dart';
import '../models/delivery_log_model.dart';

class DeliveryLogLocalProvider {
  DeliveryLogLocalProvider(this._storage);

  static const String _key = 'delivery_logs';
  static const int _limit = 200;

  final LocalStorageService _storage;

  Future<List<DeliveryLogModel>> list() async {
    final List<dynamic> raw = _storage.read<List<dynamic>>(_key) ?? <dynamic>[];
    return raw
        .map((dynamic item) => DeliveryLogModel.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  Future<void> add(DeliveryLogModel log) async {
    final List<DeliveryLogModel> logs = await list();
    logs.insert(0, log);
    if (logs.length > _limit) {
      logs.removeRange(_limit, logs.length);
    }
    await _storage.write(
      _key,
      logs.map((DeliveryLogModel e) => e.toJson()).toList(),
    );
  }

  Future<void> clear() async {
    await _storage.remove(_key);
  }
}
