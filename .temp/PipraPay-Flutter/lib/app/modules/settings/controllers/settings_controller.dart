import 'package:flutter/widgets.dart';
import 'package:get/get.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/services/local_storage_service.dart';

class SettingsController extends GetxController {
  SettingsController(this._storage);

  static const String _nameKey = 'user_name';

  final LocalStorageService _storage;
  final TextEditingController nameController = TextEditingController();

  final RxString status = ''.obs;

  @override
  void onInit() {
    super.onInit();
    final String initial = _storage.read<String>(_nameKey) ?? '';
    nameController.text = initial;
  }

  @override
  void onClose() {
    nameController.dispose();
    super.onClose();
  }

  Future<void> saveName() async {
    final String name = nameController.text.trim();
    await _storage.write(_nameKey, name);
    status.value = name.isEmpty ? 'Name cleared.' : 'Name saved.';
  }

  Future<void> openUrl(String url) async {
    final Uri uri = Uri.parse(url);
    final bool launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
    status.value = launched ? 'Opened support link.' : 'Could not open link.';
  }
}
