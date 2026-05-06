import '../../models/delivery_log_model.dart';
import '../../providers/delivery_log_local_provider.dart';
import '../delivery_log_repository.dart';

class DeliveryLogRepositoryImpl implements DeliveryLogRepository {
  DeliveryLogRepositoryImpl(this._localProvider);

  final DeliveryLogLocalProvider _localProvider;

  @override
  Future<void> add(DeliveryLogModel log) => _localProvider.add(log);

  @override
  Future<void> clear() => _localProvider.clear();

  @override
  Future<List<DeliveryLogModel>> list() => _localProvider.list();
}
