import '../../models/forwarding_config_model.dart';
import '../../providers/forwarding_config_local_provider.dart';
import '../forwarding_config_repository.dart';

class ForwardingConfigRepositoryImpl implements ForwardingConfigRepository {
  ForwardingConfigRepositoryImpl(this._localProvider);

  final ForwardingConfigLocalProvider _localProvider;

  @override
  Future<void> deleteByKey(String key) => _localProvider.deleteByKey(key);

  @override
  Future<List<ForwardingConfigModel>> getAll() => _localProvider.getAll();

  @override
  Future<void> save(ForwardingConfigModel config) => _localProvider.save(config);
}
