import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/device_auth_controller.dart';

class DeviceAuthView extends GetView<DeviceAuthController> {
  const DeviceAuthView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Device Authentication')),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 560),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(
                  'Connect Your Device',
                  style: Theme.of(context).textTheme.headlineSmall,
                ),
                const SizedBox(height: 10),
                Text(
                  'Panel URL',
                  style: Theme.of(context).textTheme.titleSmall,
                ),
                const SizedBox(height: 8),
                TextFormField(
                  initialValue: controller.panelUrl,
                  readOnly: true,
                  decoration: const InputDecoration(
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'One-Time Password',
                  style: Theme.of(context).textTheme.titleSmall,
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: controller.otpController,
                  keyboardType: TextInputType.number,
                  textInputAction: TextInputAction.done,
                  onSubmitted: (_) => controller.authenticate(),
                  decoration: const InputDecoration(
                    hintText: 'Enter OTP from dashboard',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),
                Obx(
                  () => Text(
                    controller.status.value,
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ),
                const SizedBox(height: 20),
                Obx(
                  () => SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: controller.isSubmitting.value
                          ? null
                          : controller.authenticate,
                      child: Text(
                        controller.isSubmitting.value
                            ? 'Connecting...'
                            : 'Authenticate & Connect',
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
