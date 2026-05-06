import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/device_linking_controller.dart';

class DeviceLinkingView extends GetView<DeviceLinkingController> {
  const DeviceLinkingView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Device Linking')),
      body: Obx(
        () => Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              Text(controller.status.value),
              const SizedBox(height: 8),
              if (controller.latestOtp.value.isNotEmpty)
                Text('Current OTP: ${controller.latestOtp.value}'),
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: <Widget>[
                  ElevatedButton(
                    onPressed:
                        controller.isLoading.value ? null : controller.requestOtp,
                    child: const Text('Request OTP'),
                  ),
                  ElevatedButton(
                    onPressed:
                        controller.isLoading.value ? null : controller.fetchDevices,
                    child: const Text('Fetch Devices'),
                  ),
                  ElevatedButton(
                    onPressed: controller.isLoading.value
                        ? null
                        : controller.sendHeartbeat,
                    child: const Text('Send Heartbeat'),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Expanded(
                child: ListView.builder(
                  itemCount: controller.devices.length,
                  itemBuilder: (BuildContext context, int index) {
                    final device = controller.devices[index];
                    return Card(
                      child: ListTile(
                        title: Text(device.name.isEmpty ? device.deviceId : device.name),
                        subtitle: Text(
                          '${device.model} | Android ${device.androidLevel} | ${device.status}',
                        ),
                      ),
                    );
                  },
                ),
              ),
              if (controller.isLoading.value) const LinearProgressIndicator(),
            ],
          ),
        ),
      ),
    );
  }
}
