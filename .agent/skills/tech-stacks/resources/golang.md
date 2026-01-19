# Tech Stack: Golang (Clean Arch)

## 1. Core Stack
- **Language**: Go 1.25+
- **Web Framework**: Echo / Gin
- **Database**: PostgreSQL (pgx) or GORM
- **Config**: Viper

## 2. Coding Rules
- **Error Handling**: Return errors explicitly (`val, err := ...`). Don't panic.
- **Concurrency**: Use Goroutines and Channels safely (waitgroups, mutex).
- **Interfaces**: Accept interfaces, return structs.
- **Linting**: `golangci-lint` strict mode.

## 3. Architecture
- **Pattern**: Hexagonal / Clean Architecture.
- **Structure**:
  - `/cmd`: Entry points (main.go).
  - `/internal/core/domain`: Entities.
  - `/internal/core/ports`: Interfaces (Repository/Service).
  - `/internal/core/services`: Business Logic.
  - `/internal/adapters/handlers`: HTTP Handlers.
  - `/internal/adapters/repositories`: DB Implementation.

## 4. Testing
- **Unit**: Standard `testing` package with Table Driven Tests.
- **Mocking**: `vektra/mockery` for interface mocking.
