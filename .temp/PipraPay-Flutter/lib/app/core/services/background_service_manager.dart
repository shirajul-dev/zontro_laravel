import 'package:workmanager/workmanager.dart';

@pragma('vm:entry-point')
void pipraPayBackgroundDispatcher() {
  Workmanager().executeTask((String task, Map<String, dynamic>? inputData) async {
    return Future<bool>.value(true);
  });
}

class BackgroundServiceManager {
  bool _initialized = false;

  bool get isInitialized => _initialized;

  Future<void> initialize() async {
    if (_initialized) {
      return;
    }

    await Workmanager().initialize(
      pipraPayBackgroundDispatcher,
    );

    _initialized = true;
  }

  Future<void> registerHeartbeatTask() async {
    await initialize();

    await Workmanager().registerPeriodicTask(
      'piprapay-heartbeat-task-id',
      'piprapayHeartbeatTask',
      frequency: const Duration(minutes: 15),
      constraints: Constraints(networkType: NetworkType.connected),
      existingWorkPolicy: ExistingPeriodicWorkPolicy.replace,
    );
  }
}
