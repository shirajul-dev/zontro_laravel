import 'package:flutter/widgets.dart';

import 'app/app.dart';
import 'app/core/constants/app_constants.dart';
import 'app/core/constants/app_environment.dart';
import 'app/core/dependency_injection.dart';

Future<void> bootstrap({
  required String envName,
}) async {
  WidgetsFlutterBinding.ensureInitialized();
  AppEnvironment.configure(envName: envName, baseUrl: AppConstants.panelBaseUrl);
  await DependencyInjection.init();
  runApp(const PipraPayApp());
}
