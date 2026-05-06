import '../models/delivery_log_model.dart';

abstract class DeliveryLogRepository {
  Future<List<DeliveryLogModel>> list();

  Future<void> add(DeliveryLogModel log);

  Future<void> clear();
}
