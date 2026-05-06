import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/forwarding_config_controller.dart';

class ForwardingConfigView extends GetView<ForwardingConfigController> {
  const ForwardingConfigView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Forwarding Configs')),
      body: Obx(
        () => Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: <Widget>[
              TextField(
                controller: controller.senderController,
                decoration: const InputDecoration(
                  labelText: 'Sender',
                  hintText: 'Example: NAGAD or *',
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: controller.urlController,
                decoration: const InputDecoration(
                  labelText: 'Webhook URL',
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: controller.simSlotController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'SIM Slot (0 any, 1, 2)',
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: controller.retriesController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Retries (0-10)',
                ),
              ),
              const SizedBox(height: 8),
              SwitchListTile(
                value: controller.ignoreSsl.value,
                title: const Text('Ignore SSL Verification'),
                onChanged: (bool value) => controller.ignoreSsl.value = value,
              ),
              SwitchListTile(
                value: controller.chunkedMode.value,
                title: const Text('Use Chunked Mode'),
                onChanged: (bool value) => controller.chunkedMode.value = value,
              ),
              SwitchListTile(
                value: controller.smsEnabled.value,
                title: const Text('SMS Forwarding Enabled'),
                onChanged: (bool value) => controller.smsEnabled.value = value,
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: controller.isLoading.value
                      ? null
                      : controller.saveConfig,
                  child: const Text('Save Config'),
                ),
              ),
              const SizedBox(height: 12),
              Align(
                alignment: Alignment.centerLeft,
                child: Text(controller.status.value),
              ),
              const SizedBox(height: 16),
              Expanded(
                child: ListView.builder(
                  itemCount: controller.configs.length,
                  itemBuilder: (BuildContext context, int index) {
                    final config = controller.configs[index];
                    return Card(
                      child: ListTile(
                        title: Text(config.sender),
                        subtitle: Text(
                          '${config.url}\nSIM: ${config.simSlot} | Retries: ${config.retriesNumber} | SSL Ignore: ${config.ignoreSsl} | Chunked: ${config.chunkedMode}',
                        ),
                        isThreeLine: true,
                        trailing: IconButton(
                          icon: const Icon(Icons.delete_outline),
                          onPressed: controller.isLoading.value
                              ? null
                              : () => controller.deleteConfig(config.key),
                        ),
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
