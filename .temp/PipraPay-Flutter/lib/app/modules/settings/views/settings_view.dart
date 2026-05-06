import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/settings_controller.dart';

class SettingsView extends GetView<SettingsController> {
  const SettingsView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Settings')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Obx(
          () => Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              TextField(
                controller: controller.nameController,
                decoration: const InputDecoration(
                  labelText: 'User Name',
                ),
              ),
              const SizedBox(height: 12),
              ElevatedButton(
                onPressed: controller.saveName,
                child: const Text('Save Name'),
              ),
              const SizedBox(height: 20),
              const Text('Support'),
              const SizedBox(height: 8),
              Wrap(
                spacing: 10,
                runSpacing: 10,
                children: <Widget>[
                  ElevatedButton(
                    onPressed: () => controller.openUrl('https://piprapay.com'),
                    child: const Text('About'),
                  ),
                  ElevatedButton(
                    onPressed: () =>
                        controller.openUrl('https://piprapay.com/#faq'),
                    child: const Text('FAQ'),
                  ),
                  ElevatedButton(
                    onPressed: () =>
                        controller.openUrl('https://www.facebook.com/piprapay'),
                    child: const Text('Live Support'),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Text(controller.status.value),
            ],
          ),
        ),
      ),
    );
  }
}
