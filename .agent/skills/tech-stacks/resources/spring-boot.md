# Tech Stack: Spring Boot (Java)

## 1. Core Stack
- **Framework**: Spring Boot 3.5+
- **Language**: Java 25 (LTS) or Kotlin
- **Build**: Gradle or Maven
- **DB**: JPA / Hibernate (Postgres)

## 2. Coding Rules
- **Injection**: Constructor Injection prefers over `@Autowired` field injection.
- **Lombok**: Use `@Data`, `@Builder`, `@Slf4j` to reduce boilerplate.
- **Streams**: Use Java Streams API for collections processing.
- **Exceptions**: Global `@ControllerAdvice` for error handling.

## 3. Architecture
- **Structure**:
  - `com.app.controller`: REST Controllers.
  - `com.app.service`: Business Logic.
  - `com.app.repository`: JPA Repositories.
  - `com.app.dto`: Data Transfer Objects.
  - `com.app.entity`: DB Entities.

## 4. Testing
- **Unit**: JUnit 5 + Mockito.
- **Integration**: `@SpringBootTest` + Testcontainers.
