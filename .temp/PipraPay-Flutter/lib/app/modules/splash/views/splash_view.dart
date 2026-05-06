import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/splash_controller.dart';

class SplashView extends GetView<SplashController> {
  const SplashView({super.key});

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            Icon(Icons.shield, size: 54),
            SizedBox(height: 12),
            Text(
              'PipraPay',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.w700),
            ),
            SizedBox(height: 8),
            Text('Preparing secure runtime...'),
            CircularProgressIndicator(
              color: Colors.tealAccent,
            ),
          ],
        ),
      ),
    );
  }
}
