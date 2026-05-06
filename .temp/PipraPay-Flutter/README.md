# PipraPay Flutter

Flutter migration app for PipraPay Java V2 parity and Laravel v3 integration.

## Run Commands

Default (prod entry):

```bash
flutter run
```

Development:

```bash
flutter run -t lib/main_dev.dart --dart-define=API_BASE_URL=http://10.0.2.2:8000
```

Staging:

```bash
flutter run -t lib/main_staging.dart --dart-define=API_BASE_URL=https://staging-api.piprapay.com
```

Production:

```bash
flutter run -t lib/main_prod.dart --dart-define=API_BASE_URL=https://api.piprapay.com
```

## Current Implemented Modules

- Splash bootstrap
- Dashboard actions
- Forwarding config (local persistence)
- SMS runtime simulation and retry dispatch
- Device linking actions (OTP, list, heartbeat)
- Settings (name persistence and support links)
