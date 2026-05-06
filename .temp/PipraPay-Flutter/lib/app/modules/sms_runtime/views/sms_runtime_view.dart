import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/sms_runtime_controller.dart';

class SmsRuntimeView extends GetView<SmsRuntimeController> {
  const SmsRuntimeView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('SMS Runtime')),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Obx(
          () => Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: <Widget>[
              Text(
                controller.status.value,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: controller.isForwarding.value
                    ? null
                    : controller.simulateForward,
                child: const Text('Simulate Incoming SMS'),
              ),
              const SizedBox(height: 8),
              ElevatedButton(
                onPressed: controller.isForwarding.value
                    ? null
                    : controller.sendBackendTestSms,
                child: const Text('Send Test SMS To Server'),
              ),
              const SizedBox(height: 8),
              ElevatedButton(
                onPressed:
                    controller.isForwarding.value ? null : controller.clearLogs,
                child: const Text('Clear Delivery Logs'),
              ),
              if (controller.isForwarding.value) ...<Widget>[
                const SizedBox(height: 16),
                const LinearProgressIndicator(),
              ],
              const SizedBox(height: 20),
              const Text(
                'Delivery Logs',
                style: TextStyle(fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 8),
              Expanded(
                child: ListView.builder(
                  itemCount: controller.logs.length,
                  itemBuilder: (BuildContext context, int index) {
                    final log = controller.logs[index];
                    return Card(
                      child: ListTile(
                        title: Text('${log.status.toUpperCase()} • ${log.sender}'),
                        subtitle: Text(
                          '${log.time}\n${log.url}\nAttempts: ${log.attempts}${log.error.isEmpty ? '' : '\nError: ${log.error}'}',
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
