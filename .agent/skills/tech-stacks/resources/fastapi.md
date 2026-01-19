# Tech Stack: FastAPI (Python)

## 1. Core Stack
- **Framework**: FastAPI
- **Language**: Python 3.13+
- **ORM**: SQLAlchemy (Async) + Pydantic v2
- **Server**: Uvicorn

## 2. Coding Rules
- **Type Hints**: Mandatory for everything (Args, Returns).
- **Async**: Use `async def` for I/O bound operations.
- **Dependency Injection**: Use `Depends()` for services and db sessions.
- **Validation**: Detailed Pydantic models for Requests/Responses.

## 3. Architecture
- **Structure**:
  - `/app/api`: Routers (v1, v2).
  - `/app/core`: Config, Security.
  - `/app/crud`: Database operations.
  - `/app/models`: SQLAlchemy models.
  - `/app/schemas`: Pydantic schemas (DTOs).

## 4. Testing
- **Framework**: Pytest + httpx.
- **Coverage**: Pytest-cov.
