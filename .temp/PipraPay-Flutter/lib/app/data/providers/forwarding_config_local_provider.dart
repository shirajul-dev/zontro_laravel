import '../models/forwarding_config_model.dart';
import '../../core/services/local_storage_service.dart';

class ForwardingConfigLocalProvider {
  ForwardingConfigLocalProvider(this._storage);

  static const String _configsKey = 'forwarding_configs';

  final LocalStorageService _storage;

  Future<List<ForwardingConfigModel>> getAll() async {
    final List<dynamic> raw = _storage.read<List<dynamic>>(_configsKey) ?? <dynamic>[];
    return raw
        .map(
          (dynamic item) =>
              ForwardingConfigModel.fromJson(item as Map<String, dynamic>),
        )
        .toList();
  }

  Future<void> save(ForwardingConfigModel config) async {
    final List<ForwardingConfigModel> all = await getAll();

    final int index = all.indexWhere((ForwardingConfigModel c) => c.key == config.key);
    if (index >= 0) {
      all[index] = config;
    } else {
      all.add(config);
    }

    await _storage.write(
      _configsKey,
      all.map((ForwardingConfigModel e) => e.toJson()).toList(),
    );
  }

  Future<void> deleteByKey(String key) async {
    final List<ForwardingConfigModel> all = await getAll();
    all.removeWhere((ForwardingConfigModel c) => c.key == key);
    await _storage.write(
      _configsKey,
      all.map((ForwardingConfigModel e) => e.toJson()).toList(),
    );
  }
}
