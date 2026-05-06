import 'package:flutter/material.dart';
import 'package:get/get.dart';

import 'core/constants/app_constants.dart';
import 'routes/app_pages.dart';
import 'routes/app_routes.dart';

class PipraPayApp extends StatelessWidget {
  const PipraPayApp({super.key});

  @override
  Widget build(BuildContext context) {
    return GetMaterialApp(
      title: AppConstants.appName,
      debugShowCheckedModeBanner: false,
      initialRoute: AppRoutes.splash,
      getPages: AppPages.routes,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0F766E),
          brightness: Brightness.light,
        ),
      ),
    );
  }
}
