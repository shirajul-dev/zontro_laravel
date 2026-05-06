import 'dart:math';

import 'package:flutter/widgets.dart';
import 'package:get/get.dart';

import '../../../core/utils/validators.dart';
import '../../../data/models/forwarding_config_model.dart';
import '../../../data/repositories/forwarding_config_repository.dart';

class ForwardingConfigController extends GetxController {
  ForwardingConfigController(this._repository);

  final ForwardingConfigRepository _repository;

  final RxList<ForwardingConfigModel> configs = <ForwardingConfigModel>[].obs;
  final RxBool isLoading = false.obs;
  final RxString status = ''.obs;

  final TextEditingController senderController = TextEditingController();
  final TextEditingController urlController = TextEditingController();
  final TextEditingController simSlotController = TextEditingController(text: '0');
  final TextEditingController retriesController = TextEditingController(text: '10');
  final RxBool ignoreSsl = false.obs;
  final RxBool chunkedMode = true.obs;
  final RxBool smsEnabled = true.obs;

  @override
  void onInit() {
    super.onInit();
    loadConfigs();
  }

  @override
  void onClose() {
    senderController.dispose();
    urlController.dispose();
    simSlotController.dispose();
    retriesController.dispose();
    super.onClose();
  }

  Future<void> loadConfigs() async {
    await _runSafely(() async {
      final List<ForwardingConfigModel> all = await _repository.getAll();
      configs.assignAll(all);
      status.value = 'Loaded ${all.length} config(s).';
    });
  }

  Future<void> saveConfig() async {
    final String? senderError =
        Validators.requiredField(senderController.text, field: 'Sender');
    if (senderError != null) {
      status.value = senderError;
      return;
    }

    final String? urlError = Validators.validUrl(urlController.text);
    if (urlError != null) {
      status.value = urlError;
      return;
    }

    final int simSlot = int.tryParse(simSlotController.text.trim()) ?? -1;
    if (simSlot < 0 || simSlot > 2) {
      status.value = 'SIM slot must be 0, 1 or 2.';
      return;
    }

    final int retries = int.tryParse(retriesController.text.trim()) ?? -1;
    if (retries < 0 || retries > 10) {
      status.value = 'Retries must be between 0 and 10.';
      return;
    }

    await _runSafely(() async {
      final ForwardingConfigModel model = ForwardingConfigModel(
        key: _generateKey(),
        sender: senderController.text.trim(),
        url: urlController.text.trim(),
        simSlot: simSlot,
        template: ForwardingConfigModel.defaultTemplate,
        headers: ForwardingConfigModel.defaultHeaders,
        retriesNumber: retries,
        ignoreSsl: ignoreSsl.value,
        chunkedMode: chunkedMode.value,
        isSmsEnabled: smsEnabled.value,
      );

      await _repository.save(model);
      senderController.clear();
      urlController.clear();
      simSlotController.text = '0';
      retriesController.text = '10';
      ignoreSsl.value = false;
      chunkedMode.value = true;
      smsEnabled.value = true;
      await loadConfigs();
      status.value = 'Config saved successfully.';
    });
  }

  Future<void> deleteConfig(String key) async {
    await _runSafely(() async {
      await _repository.deleteByKey(key);
      await loadConfigs();
      status.value = 'Config deleted.';
    });
  }

  Future<void> _runSafely(Future<void> Function() action) async {
    try {
      isLoading.value = true;
      await action();
    } catch (error) {
      status.value = 'Operation failed: $error';
    } finally {
      isLoading.value = false;
    }
  }

  String _generateKey() {
    final int random = Random().nextInt(900000) + 100000;
    return '${DateTime.now().millisecondsSinceEpoch}_$random';
  }
}
