import 'package:get_storage/get_storage.dart';

class LocalStorageService {
  LocalStorageService._(this._box);

  final GetStorage _box;

  static Future<LocalStorageService> init() async {
    await GetStorage.init();
    return LocalStorageService._(GetStorage());
  }

  T? read<T>(String key) => _box.read<T>(key);

  Future<void> write(String key, dynamic value) => _box.write(key, value);

  Future<void> remove(String key) => _box.remove(key);
}
