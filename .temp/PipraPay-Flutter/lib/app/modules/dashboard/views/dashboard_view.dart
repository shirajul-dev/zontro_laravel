import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/constants/app_environment.dart';
import '../../../routes/app_routes.dart';
import '../controllers/dashboard_controller.dart';

class DashboardView extends GetView<DashboardController> {
  const DashboardView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('PipraPay Flutter'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: <Widget>[
            Text(
              AppConstants.appName,
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            const SizedBox(height: 8),
            Text(
              'Env: ${AppEnvironment.name}',
              style: Theme.of(context).textTheme.bodyMedium,
            ),
            Text(
              'Base URL: ${AppEnvironment.apiBaseUrl}',
              style: Theme.of(context).textTheme.bodyMedium,
            ),
            const SizedBox(height: 20),
            Obx(
              () {
                final bool loading = controller.isLoading.value;
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: <Widget>[
                    Text(
                      controller.status.value,
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    const SizedBox(height: 10),
                    Text('Devices: ${controller.deviceCount.value}'),
                    Text(
                      'SMS Permission: '
                      '${controller.smsPermissionGranted.value ? 'Granted' : 'Not Granted'}',
                    ),
                    Text(
                      'Background Runtime: '
                      '${controller.backgroundRuntimeReady.value ? 'Ready' : 'Not Ready'}',
                    ),
                    if (controller.deviceSummary.value.isNotEmpty)
                      Text('Device: ${controller.deviceSummary.value}'),
                    const SizedBox(height: 16),
                    Wrap(
                      spacing: 10,
                      runSpacing: 10,
                      children: <Widget>[
                        ElevatedButton(
                          onPressed: loading ? null : controller.loadDevices,
                          child: const Text('Load Devices'),
                        ),
                        ElevatedButton(
                          onPressed: loading ? null : controller.ensureSmsPermission,
                          child: const Text('Grant SMS Permission'),
                        ),
                        ElevatedButton(
                          onPressed:
                              loading ? null : controller.initializeBackgroundRuntime,
                          child: const Text('Init Background Runtime'),
                        ),
                        ElevatedButton(
                          onPressed: loading ? null : controller.loadDeviceInfoSummary,
                          child: const Text('Load Device Info'),
                        ),
                        ElevatedButton(
                          onPressed: loading
                              ? null
                              : () => Get.toNamed(AppRoutes.forwardingConfig),
                          child: const Text('Open Config Module'),
                        ),
                        ElevatedButton(
                          onPressed: loading
                              ? null
                              : () => Get.toNamed(AppRoutes.smsRuntime),
                          child: const Text('Open SMS Runtime'),
                        ),
                        ElevatedButton(
                          onPressed: loading
                              ? null
                              : () => Get.toNamed(AppRoutes.deviceLinking),
                          child: const Text('Open Device Linking'),
                        ),
                        ElevatedButton(
                          onPressed: loading
                              ? null
                              : () => Get.toNamed(AppRoutes.settings),
                          child: const Text('Open Settings'),
                        ),
                        OutlinedButton(
                          onPressed: loading ? null : controller.logout,
                          child: const Text('Logout'),
                        ),
                      ],
                    ),
                    if (loading) ...<Widget>[
                      const SizedBox(height: 16),
                      const LinearProgressIndicator(),
                    ],
                  ],
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
