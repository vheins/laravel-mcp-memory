# Tech Stack: Rust (System/Web)

## 1. Core Stack
- **Language**: Rust 2024 Edition
- **Web Framework**: Axum or Actix-web
- **Database**: SQLx (Compile-time checked queries)
- **Serialization**: Serde

## 2. Coding Rules
- **Error Handling**: Use `Result<T, AppError>`. No `unwrap()` in production code.
- **Async**: Tokio runtime.
- **Borrow Checker**: Minimize cloning. Use references where possible.
- **Modules**: Keep `main.rs` clean. detailed logic in `lib.rs` or modules.

## 3. Architecture
- **Structure**:
  - `/src/bin`: Binary entry points.
  - `/src/api`: Route handlers.
  - `/src/core`: Domain models and business logic.
  - `/src/infra`: Database and external service adapters.
  - `/migrations`: SQL migrations.

## 4. Testing
- **Unit**: `#[test]` in the same file/module.
- **Integration**: `/tests` directory for API integration tests.
