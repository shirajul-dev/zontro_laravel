import '../models/forwarding_config_model.dart';

abstract class ForwardingConfigRepository {
  Future<List<ForwardingConfigModel>> getAll();

  Future<void> save(ForwardingConfigModel config);

  Future<void> deleteByKey(String key);
}
