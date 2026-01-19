# Tech Stack: Express.js (Node.js)

## 1. Core Stack
- **Runtime**: Node.js 24 (LTS)
- **Framework**: Express.js 5.x
- **Language**: TypeScript
- **ORM**: Prisma or TypeORM

## 2. Coding Rules
- **Async/Await**: Use `express-async-errors` or try/catch blocks.
- **Types**: Extend `Express.Request` for custom properties (e.g. `req.user`).
- **Validation**: Joi or Zod middleware.
- **Logging**: Winston or Pino (Structured logging).

## 3. Architecture
- **Pattern**: Layered Architecture (Controller-Service-Data).
- **Structure**:
  - `/src/controllers`: Request handlers.
  - `/src/services`: Business logic.
  - `/src/routes`: Route definitions.
  - `/src/middleware`: Auth, Logging, Error handling.
  - `/src/models`: DB Models (if not using ORM types).

## 4. Testing
- **Framework**: Jest or Mocha/Chai.
- **Integration**: Supertest.
