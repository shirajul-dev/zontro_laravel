# PipraPay Flutter Migration Master Plan (Java V2 -> Flutter GetX, Backend V3)

Date: 2026-04-20
Workspace: PipraPay-main
Target App Folder: PipraPay-Flutter
Source App Folder: PipraPay-V2-App-main
Backend Folder: laravel-app

## 1) Java App (V2) - Full Logic and Functional Summary

This section documents the current Android Java app behavior to preserve logic, validation, configuration rules, and operational flow exactly during Flutter migration.

### 1.1 Technical architecture currently used in Java app

- UI layer is Activity-centric (single major screen in MainActivity with settings and config management).
- SMS interception uses BroadcastReceiver.
- Background continuity uses Foreground Service + Boot Completed receiver.
- Outbound delivery uses WorkManager with retry policy.
- Transport layer is custom HTTP with HttpURLConnection and custom TLS socket handling.
- Periodic heartbeat uses Volley POST loop (every 30 minutes when background task is enabled).
- Local persistence is SharedPreferences (JSON blobs for forwarding configs).

### 1.2 Critical Android components and responsibilities

- AndroidManifest.xml
  - Permissions: INTERNET, ACCESS_NETWORK_STATE, RECEIVE_SMS, FOREGROUND_SERVICE, RECEIVE_BOOT_COMPLETED, VIBRATE, REQUEST_IGNORE_BATTERY_OPTIMIZATIONS.
  - Service: SmsReceiverService.
  - Receiver: BootCompletedReceiver.
  - Activity: MainActivity.

- SmsBroadcastReceiver.java
  - Receives incoming SMS from system broadcast.
  - Reconstructs SMS text from PDUs.
  - Loads all forwarding configs from local storage.
  - Sender filter logic:
    - Match exact sender OR wildcard sender (asterisk value from app resource).
  - Validates SMS forwarding enable flag per config.
  - Detects SIM slot using multiple vendor-specific keys from broadcast bundle.
  - Applies SIM-specific config filtering.
  - Builds final payload from template placeholders and enqueues RequestWorker.

- RequestWorker.java
  - Reads URL, message text, headers, SSL mode, chunked mode, max retries from WorkManager input.
  - Applies retry boundary using getRunAttemptCount.
  - Calls Request.execute and maps outcomes to success, retry, failure.

- Request.java
  - Uses HttpURLConnection (or HttpsURLConnection).
  - Applies JSON headers from config.
  - Supports custom SSL behavior with ignoreSsl switch (via TLSSocketFactory).
  - Supports chunked streaming mode and fixed-length mode.
  - Retry condition: non-2xx response OR IO failure.

- ForwardingConfig.java
  - Canonical config model with JSON persistence.
  - Fields include sender, url, sim_slot, template, headers, retries_number, ignore_ssl, chunked_mode, is_sms_enabled.
  - Default payload template currently used:
    {
      "from":"%from%",
      "text":"%text%",
      "sentStamp":%sentStamp%,
      "receivedStamp":%receivedStamp%,
      "sim":"%sim%"
    }
  - Default headers currently used:
    {"User-agent":"mh-piprapay-api-key"}
  - Placeholder replacement + JSON-safe text escaping done in prepareMessage.

- SmsReceiverService.java
  - Registers SMS receiver at runtime.
  - Runs as foreground service with persistent notification.
  - Keeps SMS forwarding alive under OS background restrictions.

- MainActivity.java
  - Controls permissions and battery optimization request flow.
  - Starts and stops foreground service via UI toggle.
  - Stores user display name and settings in SharedPreferences.
  - Starts periodic heartbeat to configured URLs (check=i_am_active + device metadata).
  - Opens support URLs and app settings shortcuts.

### 1.3 Effective app features currently present in Java V2

- SMS auto-capture and forwarding by rule.
- Multi-config support with independent sender and SIM filtering.
- Background persistent forwarding.
- Boot auto-restart support.
- Retry-based resilient delivery.
- Optional SSL ignore and chunked mode options per config.
- Heartbeat ping to configured endpoints.

### 1.4 Validation and rule behavior that must be preserved exactly

- Only process SMS when config sender matches exact sender or wildcard mode.
- Respect per-config enable/disable switch.
- Respect per-config SIM slot condition.
- Enforce max retry count per config.
- Preserve placeholder contract in template engine.
- Preserve headers JSON parsing and request header injection logic.
- Preserve retry trigger behavior for non-2xx and network IO failures.

## 2) Java App <-> Laravel V3 Backend Logic Mapping

This section maps existing app behavior to current backend capabilities.

### 2.1 Device management (backend implemented)

Backend native admin action handlers currently available:

- action=device-list
- action=device-connect-info
- action=device-delete
- action=device-bulk-action

Current behavior found in backend:

- Device list shows used devices and supports status/date filters.
- device-connect-info generates OTP and creates/updates processing record.
- Device deletion and bulk deletion are available with permission gates.

Relevant backend files:

- laravel-app/app/Http/Controllers/Admin/NativeAdminActionController.php
- laravel-app/app/Services/Admin/DeviceAdminActionService.php

### 2.2 SMS data section (backend implemented)

Backend native admin action handlers currently available:

- action=sms-data-list
- action=sms-data-create
- action=sms-data-info-byID
- action=sms-data-edit
- action=sms-data-delete
- action=sms-data-bulk-action

This confirms backend has the administrative layer for SMS records lifecycle in panel.

### 2.3 Current app-backend gap (important)

- Java V2 app forwards SMS to configurable webhook URL(s), not strongly tied to a formal device registration API contract in-app.
- Laravel has device OTP generation and device registry in panel, but Java app logic does not yet fully complete a formal pair-confirm lifecycle as a first-class flow.
- Flutter migration should unify this by introducing explicit connect/register/heartbeat/sync API contracts and strict device identity handling.

## 3) Flutter Target Architecture (GetX Professional Pattern)

Planned project layout for PipraPay-Flutter:

- app/
  - app.dart
  - routes/
    - app_pages.dart
    - app_routes.dart
  - core/
    - constants/
    - errors/
    - network/
      - dio_client.dart
      - interceptors/
    - services/
      - permissions_service.dart
      - battery_optimization_service.dart
      - background_service_manager.dart
      - device_info_service.dart
      - secure_storage_service.dart
    - utils/
      - validators.dart
      - template_engine.dart
      - sim_slot_detector.dart
  - data/
    - models/
    - providers/
    - repositories/
  - modules/
    - splash/
    - onboarding/
    - auth/
    - dashboard/
    - forwarding_config/
    - sms_runtime/
    - device_linking/
    - settings/
    - support/

### 3.1 State management and coding standards

- GetX for route, DI, and state.
- Repository pattern over providers.
- Strict typed request/response models.
- Consistent error object and fallback states.
- Lint and formatting with CI checks.

### 3.2 Platform and plugin strategy

- telephony or sms_advanced for SMS receive pipeline.
- workmanager + foreground service plugin for resilient background execution.
- permission_handler for runtime permission flow.
- device_info_plus for device metadata.
- get_storage or hive for local config persistence (plus secure storage for sensitive values).
- dio for HTTP client with interceptors and retry strategy wrappers.

## 4) App-to-Backend Integration Strategy

### 4.1 Required connection contract (to finalize with backend)

- Device registration and pairing endpoints.
- OTP verify/confirm endpoint.
- Heartbeat endpoint with device health metadata.
- SMS ingest endpoint with payload format and auth signature.
- Config sync endpoint (optional but recommended for centralized control).

### 4.2 Preserved payload/template behavior

Flutter must preserve Java template placeholders exactly:

- %from%
- %text%
- %sentStamp%
- %receivedStamp%
- %sim%

Headers JSON behavior and retry behavior must remain equivalent.

## 5) Full Phase-by-Phase Flutter Development Plan (Checklist)

## Phase 0 - Discovery and Contract Freeze

- [ ] Freeze Java V2 behavior matrix (all config switches, template behavior, retry behavior).
- [ ] Freeze backend endpoint contracts for device, heartbeat, sms ingest.
- [ ] Finalize auth strategy between app and Laravel (token or signed key approach).
- [ ] Document error-code policy and retry-safe responses.

## Phase 1 - Flutter Foundation Setup (PipraPay-Flutter)

- [x] Initialize Flutter project with production profile setup.
- [x] Add GetX route and DI scaffold.
- [x] Build app/core with network, storage, utils, constants.
- [x] Configure flavors/environments (dev/staging/prod base URLs).
- [x] Add base UI theme system and responsive tokens.

## Phase 2 - Core Domain Models and Repositories

- [x] Implement typed models for forwarding config, device, sms payload, api responses.
- [x] Implement repository interfaces and concrete data providers.
- [x] Add robust serialization/deserialization tests.

## Phase 3 - Permissions and Runtime Services

- [x] Implement RECEIVE_SMS permission flow with UX parity and fallback.
- [x] Implement battery optimization request flow and manual settings fallback.
- [x] Implement foreground service controls and startup guards.
- [ ] Implement boot/start persistence behavior equivalent to Java app.

## Phase 4 - Forwarding Configuration Module

- [x] Build add/edit/delete/list config screens.
- [x] Preserve wildcard sender logic and SIM slot logic.
- [x] Preserve default template and headers behavior.
- [x] Preserve retries, SSL-ignore, chunked-mode settings.
- [x] Add validation messages aligned with Java behavior.

## Phase 5 - SMS Receive and Forward Engine

- [ ] Implement SMS receiver pipeline.
- [ ] Implement SIM detection logic with multi-key fallback strategy.
- [x] Implement template substitution and JSON escaping parity.
- [x] Implement request dispatch with retries and backoff parity.
- [x] Add delivery status telemetry and local audit logs.

## Phase 6 - Device Linking and Backend Integration

- [x] Implement device connect flow using backend OTP and registry logic.
- [ ] Implement device identity persistence and reconnect handling.
- [x] Implement heartbeat loop with metadata (model, brand, OS, api level).
- [x] Implement secure request signing/auth headers.
- [ ] Validate all integration cases against laravel-app device/sms actions.

## Phase 7 - Dashboard and Settings UX

- [x] Build dashboard with service state, permission state, and quick actions.
- [x] Build settings for user name, support links, clear data, app info.
- [x] Build connection health indicators and error-state recovery actions.

## Phase 8 - QA, Hardening, and Compliance

- [x] Unit tests for template engine, retry policy, serialization.
- [ ] Integration tests for API flows and heartbeat.
- [ ] Background reliability tests across Android versions and OEM variants.
- [ ] Security review for SSL behavior and sensitive storage.
- [ ] Performance and battery-impact benchmark.

## Phase 9 - Release and Rollout

- [ ] Prepare migration guide from Java V2 to Flutter app.
- [ ] Stage rollout with internal testing users.
- [ ] Monitor webhook success rate and device online metrics.
- [ ] Execute phased production rollout.

## 6) Backend Alignment Tasks Needed for Smooth Flutter Integration

- [ ] Confirm mobile-facing auth mechanism for device and sms ingest APIs.
- [ ] Add or finalize dedicated mobile API routes if admin-action routes are insufficient for mobile runtime.
- [ ] Add webhook request verification to prevent unauthorized SMS ingestion.
- [ ] Add device lifecycle events (register, verified, active, suspended, revoked).
- [ ] Add centralized config sync APIs if panel-managed configs are required.

## 7) Definition of Done for 100% Functional Parity

- [ ] Every Java V2 forwarding rule and validation reproduced in Flutter.
- [ ] SMS forwarding succeeds under same scenarios and constraints.
- [ ] Background behavior remains stable after reboot and app process kill.
- [ ] Device connection flow works end-to-end with Laravel v3 backend.
- [ ] Payload templates, headers, and retry behavior are behaviorally equivalent.
- [ ] UI and UX are production-grade, consistent, and maintainable.

---

Prepared for: Full Flutter rebuild with GetX while preserving Java V2 operational logic and integrating with Laravel v3 backend device/sms domain.
