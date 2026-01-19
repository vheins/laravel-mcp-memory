# Tech Stack: Flutter (GetX)

## 1. Core Stack
- **Framework**: Flutter 3.27+
- **Language**: Dart 3.0+
- **State Management**: GetX
- **Navigation**: GetX (Route Management)
- **Dependency Injection**: GetX (Bindings)

## 2. Coding Rules
- **State**: Use `.obs` for reactive variables and `Obx()` in UI.
- **Controllers**: Extend `GetxController`. Use `onInit()` for startup logic.
- **Bindings**: Always use Bindings for dependency injection. avoid `Get.put` in UI.
- **Widgets**: Extend `GetView<T>` for pages/views to auto-inject controller.
- **Navigation**: Use named routes `Get.toNamed()`.

## 3. Architecture
- **Pattern**: MVC or MVVM (Clean Architecture friendly).
- **Structure**:
  - `/lib/app/routes`: AppPages and AppRoutes.
  - `/lib/app/modules`: Feature modules (Binding, Controller, View).
  - `/lib/app/data`: Providers (API) and Models.
  - `/lib/app/core`: Utils, Constants, Translations.

## 4. Testing
- **Unit**: `test` package + `mocktail`.
- **Widget**: Standard widget testing.

