# Tech Stack: .NET (Enterprise)

## 1. Core Stack

| Category | Technology | Version |
|----------|------------|---------|
| Framework | .NET | 10 (LTS) |
| Language | C# | 14+ |
| Web Framework | ASP.NET Core Web API | 10.x |
| ORM | Entity Framework Core | 10.x |
| Database | SQL Server / PostgreSQL | 2022+ / 17+ |

---

## 2. Architecture: Clean Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Presentation                         │
│   (API Controllers, Middleware, Filters, ViewModels)        │
└─────────────────────────┬───────────────────────────────────┘
                          │ depends on
┌─────────────────────────▼───────────────────────────────────┐
│                       Application                           │
│   (Use Cases, CQRS Handlers, DTOs, Interfaces, Validators)  │
└─────────────────────────┬───────────────────────────────────┘
                          │ depends on
┌─────────────────────────▼───────────────────────────────────┐
│                         Domain                              │
│   (Entities, Value Objects, Domain Services, Events)        │
└─────────────────────────────────────────────────────────────┘
                          ▲
                          │ implements
┌─────────────────────────┴───────────────────────────────────┐
│                      Infrastructure                         │
│   (EF Core, Repositories, External APIs, Caching, Email)    │
└─────────────────────────────────────────────────────────────┘
```

### 2.1 Project Structure

```
Solution/
├── src/
│   ├── Domain/
│   │   ├── Common/
│   │   │   ├── BaseEntity.cs
│   │   │   ├── AuditableEntity.cs
│   │   │   └── ValueObject.cs
│   │   ├── Entities/
│   │   ├── Enums/
│   │   ├── Events/
│   │   ├── Exceptions/
│   │   └── Interfaces/
│   │       └── IDomainService.cs
│   │
│   ├── Application/
│   │   ├── Common/
│   │   │   ├── Behaviours/
│   │   │   │   ├── LoggingBehaviour.cs
│   │   │   │   ├── ValidationBehaviour.cs
│   │   │   │   └── PerformanceBehaviour.cs
│   │   │   ├── Exceptions/
│   │   │   ├── Interfaces/
│   │   │   │   ├── IApplicationDbContext.cs
│   │   │   │   ├── ICurrentUserService.cs
│   │   │   │   └── IDateTimeService.cs
│   │   │   ├── Mappings/
│   │   │   └── Models/
│   │   ├── Features/
│   │   │   └── [FeatureName]/
│   │   │       ├── Commands/
│   │   │       │   ├── Create[Entity]/
│   │   │       │   │   ├── Create[Entity]Command.cs
│   │   │       │   │   ├── Create[Entity]CommandHandler.cs
│   │   │       │   │   └── Create[Entity]CommandValidator.cs
│   │   │       │   └── Update[Entity]/
│   │   │       └── Queries/
│   │   │           ├── Get[Entity]ById/
│   │   │           └── Get[Entity]List/
│   │   └── DependencyInjection.cs
│   │
│   ├── Infrastructure/
│   │   ├── Persistence/
│   │   │   ├── Configurations/
│   │   │   ├── Migrations/
│   │   │   ├── Repositories/
│   │   │   └── ApplicationDbContext.cs
│   │   ├── Services/
│   │   │   ├── DateTimeService.cs
│   │   │   ├── EmailService.cs
│   │   │   └── CacheService.cs
│   │   ├── Identity/
│   │   └── DependencyInjection.cs
│   │
│   └── API/
│       ├── Controllers/
│       ├── Filters/
│       │   ├── ApiExceptionFilterAttribute.cs
│       │   └── ValidationFilterAttribute.cs
│       ├── Middleware/
│       │   ├── ExceptionHandlingMiddleware.cs
│       │   └── RequestLoggingMiddleware.cs
│       ├── Extensions/
│       └── Program.cs
│
├── tests/
│   ├── Domain.UnitTests/
│   ├── Application.UnitTests/
│   ├── Application.IntegrationTests/
│   └── API.FunctionalTests/
│
└── docs/
```

### 2.2 Layer Responsibilities

| Layer | Responsibility | Dependencies |
|-------|----------------|--------------|
| **Domain** | Business logic, Entities, Value Objects, Domain Events | None (innermost) |
| **Application** | Use cases, CQRS, Validation, DTOs, Interfaces | Domain |
| **Infrastructure** | Data access, External services, Caching | Application, Domain |
| **API** | HTTP endpoints, Auth, Middleware, Filters | Application |

---

## 3. Essential Libraries

### 3.1 Core Libraries

| Purpose | Library | Usage |
|---------|---------|-------|
| CQRS/Mediator | `MediatR` | Command/Query separation |
| Validation | `FluentValidation` | Request validation |
| Mapping | `Mapster` or `AutoMapper` | DTO ↔ Entity mapping |
| Logging | `Serilog` | Structured logging |
| API Docs | `Swashbuckle.AspNetCore` | OpenAPI/Swagger |

### 3.2 Infrastructure Libraries

| Purpose | Library | Usage |
|---------|---------|-------|
| Caching | `StackExchange.Redis` | Distributed cache |
| Background Jobs | `Hangfire` or `Quartz.NET` | Scheduled tasks |
| Health Checks | `AspNetCore.HealthChecks.*` | Readiness/Liveness |
| Resilience | `Polly` | Retry, Circuit Breaker |
| HTTP Client | `Refit` | Typed HTTP clients |

### 3.3 Testing Libraries

| Purpose | Library | Usage |
|---------|---------|-------|
| Framework | `xUnit` | Test runner |
| Assertions | `FluentAssertions` | Readable assertions |
| Mocking | `Moq` or `NSubstitute` | Test doubles |
| Integration | `WebApplicationFactory` | API testing |
| Fixtures | `Bogus` | Fake data generation |
| Architecture | `NetArchTest` | Architecture rules |

---

## 4. Coding Standards

### 4.1 General Rules

```csharp
// ✅ Enable nullable reference types in .csproj
<Nullable>enable</Nullable>
<ImplicitUsings>enable</ImplicitUsings>
<TreatWarningsAsErrors>true</TreatWarningsAsErrors>

// ✅ Use async/await for all I/O operations
public async Task<Result<T>> GetByIdAsync(Guid id, CancellationToken ct = default);

// ✅ Use CancellationToken in all async methods
public async Task<IEnumerable<T>> GetAllAsync(CancellationToken cancellationToken);

// ✅ Use records for DTOs (immutable)
public record CreateUserCommand(string Email, string Name) : IRequest<Guid>;

// ✅ Use LINQ Method Syntax
var activeUsers = users.Where(u => u.IsActive).OrderBy(u => u.Name).ToList();
```

### 4.2 Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Interface | `I` prefix | `IUserRepository` |
| Async Method | `Async` suffix | `GetUserAsync` |
| Command | Action + Entity + `Command` | `CreateUserCommand` |
| Query | `Get` + Entity + `Query` | `GetUserByIdQuery` |
| Handler | Command/Query + `Handler` | `CreateUserCommandHandler` |
| Validator | Command/Query + `Validator` | `CreateUserCommandValidator` |
| DTO | Entity + `Dto` | `UserDto` |

### 4.3 Response Pattern

```csharp
// Use Result<T> pattern for operation results
public class Result<T>
{
    public bool IsSuccess { get; }
    public T? Value { get; }
    public string? Error { get; }
    public static Result<T> Success(T value) => new(true, value, null);
    public static Result<T> Failure(string error) => new(false, default, error);
}

// API responses should follow JSON:API or a consistent envelope
public class ApiResponse<T>
{
    public bool Success { get; init; }
    public T? Data { get; init; }
    public ApiError? Error { get; init; }
    public Dictionary<string, object>? Meta { get; init; }
}
```

---

## 5. CQRS with MediatR

### 5.1 Command Example

```csharp
// Command (Application/Features/Users/Commands/CreateUser/)
public record CreateUserCommand(string Email, string Name) : IRequest<Result<Guid>>;

// Handler
public class CreateUserCommandHandler : IRequestHandler<CreateUserCommand, Result<Guid>>
{
    private readonly IApplicationDbContext _context;
    
    public CreateUserCommandHandler(IApplicationDbContext context) => _context = context;
    
    public async Task<Result<Guid>> Handle(CreateUserCommand request, CancellationToken ct)
    {
        var user = new User(request.Email, request.Name);
        _context.Users.Add(user);
        await _context.SaveChangesAsync(ct);
        return Result<Guid>.Success(user.Id);
    }
}

// Validator
public class CreateUserCommandValidator : AbstractValidator<CreateUserCommand>
{
    public CreateUserCommandValidator(IApplicationDbContext context)
    {
        RuleFor(x => x.Email)
            .NotEmpty().WithMessage("Email is required")
            .EmailAddress().WithMessage("Invalid email format")
            .MustAsync(async (email, ct) => 
                !await context.Users.AnyAsync(u => u.Email == email, ct))
            .WithMessage("Email already exists");
            
        RuleFor(x => x.Name)
            .NotEmpty().MaximumLength(100);
    }
}
```

### 5.2 Query Example

```csharp
// Query
public record GetUserByIdQuery(Guid Id) : IRequest<Result<UserDto>>;

// Handler
public class GetUserByIdQueryHandler : IRequestHandler<GetUserByIdQuery, Result<UserDto>>
{
    private readonly IApplicationDbContext _context;
    private readonly IMapper _mapper;
    
    public async Task<Result<UserDto>> Handle(GetUserByIdQuery request, CancellationToken ct)
    {
        var user = await _context.Users
            .AsNoTracking()
            .FirstOrDefaultAsync(u => u.Id == request.Id, ct);
            
        return user is null 
            ? Result<UserDto>.Failure("User not found")
            : Result<UserDto>.Success(_mapper.Map<UserDto>(user));
    }
}
```

### 5.3 MediatR Pipeline Behaviours

```csharp
// Register in DI
services.AddTransient(typeof(IPipelineBehavior<,>), typeof(LoggingBehaviour<,>));
services.AddTransient(typeof(IPipelineBehavior<,>), typeof(ValidationBehaviour<,>));
services.AddTransient(typeof(IPipelineBehavior<,>), typeof(PerformanceBehaviour<,>));

// Validation Behaviour
public class ValidationBehaviour<TRequest, TResponse> : IPipelineBehavior<TRequest, TResponse>
    where TRequest : notnull
{
    private readonly IEnumerable<IValidator<TRequest>> _validators;
    
    public async Task<TResponse> Handle(TRequest request, RequestHandlerDelegate<TResponse> next, CancellationToken ct)
    {
        if (!_validators.Any()) return await next();
        
        var context = new ValidationContext<TRequest>(request);
        var failures = _validators
            .Select(v => v.Validate(context))
            .SelectMany(r => r.Errors)
            .Where(f => f != null)
            .ToList();
            
        if (failures.Count != 0)
            throw new ValidationException(failures);
            
        return await next();
    }
}
```

---

## 6. Repository Pattern

### 6.1 Generic Repository Interface

```csharp
// Domain/Interfaces/IRepository.cs
public interface IRepository<T> where T : BaseEntity
{
    Task<T?> GetByIdAsync(Guid id, CancellationToken ct = default);
    Task<IReadOnlyList<T>> GetAllAsync(CancellationToken ct = default);
    Task<IReadOnlyList<T>> GetAsync(Expression<Func<T, bool>> predicate, CancellationToken ct = default);
    Task<T> AddAsync(T entity, CancellationToken ct = default);
    Task UpdateAsync(T entity, CancellationToken ct = default);
    Task DeleteAsync(T entity, CancellationToken ct = default);
}
```

### 6.2 Unit of Work

```csharp
// Application/Common/Interfaces/IUnitOfWork.cs
public interface IUnitOfWork : IDisposable
{
    IRepository<User> Users { get; }
    IRepository<Order> Orders { get; }
    Task<int> SaveChangesAsync(CancellationToken ct = default);
    Task BeginTransactionAsync(CancellationToken ct = default);
    Task CommitTransactionAsync(CancellationToken ct = default);
    Task RollbackTransactionAsync(CancellationToken ct = default);
}
```

---

## 7. Exception Handling

### 7.1 Custom Exceptions

```csharp
// Domain/Exceptions/
public class NotFoundException : Exception
{
    public NotFoundException(string name, object key)
        : base($"Entity \"{name}\" ({key}) was not found.") { }
}

public class ValidationException : Exception
{
    public IDictionary<string, string[]> Errors { get; }
    public ValidationException(IEnumerable<ValidationFailure> failures)
    {
        Errors = failures
            .GroupBy(e => e.PropertyName)
            .ToDictionary(g => g.Key, g => g.Select(e => e.ErrorMessage).ToArray());
    }
}

public class ForbiddenAccessException : Exception { }
```

### 7.2 Global Exception Middleware

```csharp
// API/Middleware/ExceptionHandlingMiddleware.cs
public class ExceptionHandlingMiddleware
{
    private readonly RequestDelegate _next;
    private readonly ILogger<ExceptionHandlingMiddleware> _logger;

    public async Task InvokeAsync(HttpContext context)
    {
        try
        {
            await _next(context);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Unhandled exception");
            await HandleExceptionAsync(context, ex);
        }
    }

    private static async Task HandleExceptionAsync(HttpContext context, Exception exception)
    {
        var (statusCode, response) = exception switch
        {
            ValidationException ex => (400, new ApiResponse<object> 
            { 
                Success = false, 
                Error = new ApiError("Validation Failed", ex.Errors) 
            }),
            NotFoundException ex => (404, new ApiResponse<object> 
            { 
                Success = false, 
                Error = new ApiError(ex.Message) 
            }),
            ForbiddenAccessException => (403, new ApiResponse<object> 
            { 
                Success = false, 
                Error = new ApiError("Forbidden") 
            }),
            _ => (500, new ApiResponse<object> 
            { 
                Success = false, 
                Error = new ApiError("Internal Server Error") 
            })
        };

        context.Response.StatusCode = statusCode;
        context.Response.ContentType = "application/json";
        await context.Response.WriteAsJsonAsync(response);
    }
}
```

---

## 8. Authentication & Authorization

### 8.1 JWT Configuration

```csharp
// API/Extensions/AuthenticationExtensions.cs
public static IServiceCollection AddJwtAuthentication(this IServiceCollection services, IConfiguration config)
{
    services.AddAuthentication(JwtBearerDefaults.AuthenticationScheme)
        .AddJwtBearer(options =>
        {
            options.TokenValidationParameters = new TokenValidationParameters
            {
                ValidateIssuer = true,
                ValidateAudience = true,
                ValidateLifetime = true,
                ValidateIssuerSigningKey = true,
                ValidIssuer = config["Jwt:Issuer"],
                ValidAudience = config["Jwt:Audience"],
                IssuerSigningKey = new SymmetricSecurityKey(
                    Encoding.UTF8.GetBytes(config["Jwt:SecretKey"]!)),
                ClockSkew = TimeSpan.Zero
            };
        });
        
    return services;
}
```

### 8.2 Policy-Based Authorization

```csharp
// Define policies
services.AddAuthorization(options =>
{
    options.AddPolicy("AdminOnly", policy => policy.RequireRole("Admin"));
    options.AddPolicy("CanManageUsers", policy => 
        policy.RequireClaim("Permission", "users.manage"));
});

// Usage in Controller
[Authorize(Policy = "CanManageUsers")]
[HttpPost]
public async Task<IActionResult> CreateUser([FromBody] CreateUserCommand command)
{
    return Ok(await _mediator.Send(command));
}
```

---

## 9. Logging with Serilog

### 9.1 Configuration

```csharp
// Program.cs
Log.Logger = new LoggerConfiguration()
    .MinimumLevel.Override("Microsoft", LogEventLevel.Warning)
    .Enrich.FromLogContext()
    .Enrich.WithMachineName()
    .Enrich.WithEnvironmentName()
    .WriteTo.Console(new JsonFormatter())
    .WriteTo.Seq("http://localhost:5341") // or Elasticsearch
    .CreateLogger();

builder.Host.UseSerilog();
```

### 9.2 Structured Logging

```csharp
// Use semantic logging
_logger.LogInformation("User {UserId} created order {OrderId} with {ItemCount} items", 
    userId, orderId, items.Count);

// Avoid string interpolation
// ❌ _logger.LogInformation($"User {userId} created order {orderId}");
```

---

## 10. Caching Strategy

```csharp
// Application/Common/Interfaces/ICacheService.cs
public interface ICacheService
{
    Task<T?> GetAsync<T>(string key, CancellationToken ct = default);
    Task SetAsync<T>(string key, T value, TimeSpan? expiry = null, CancellationToken ct = default);
    Task RemoveAsync(string key, CancellationToken ct = default);
    Task<T> GetOrSetAsync<T>(string key, Func<Task<T>> factory, TimeSpan? expiry = null, CancellationToken ct = default);
}

// Usage in Query Handler
public async Task<Result<UserDto>> Handle(GetUserByIdQuery request, CancellationToken ct)
{
    var cacheKey = $"user:{request.Id}";
    var user = await _cache.GetOrSetAsync(cacheKey, 
        async () => await _context.Users.FindAsync(request.Id, ct),
        TimeSpan.FromMinutes(5), ct);
        
    return user is null 
        ? Result<UserDto>.Failure("Not found") 
        : Result<UserDto>.Success(_mapper.Map<UserDto>(user));
}
```

---

## 11. API Versioning

```csharp
// Program.cs
builder.Services.AddApiVersioning(options =>
{
    options.DefaultApiVersion = new ApiVersion(1, 0);
    options.AssumeDefaultVersionWhenUnspecified = true;
    options.ReportApiVersions = true;
    options.ApiVersionReader = ApiVersionReader.Combine(
        new UrlSegmentApiVersionReader(),
        new HeaderApiVersionReader("X-Api-Version"));
});

// Controller
[ApiVersion("1.0")]
[Route("api/v{version:apiVersion}/[controller]")]
public class UsersController : ApiControllerBase { }

[ApiVersion("2.0")]
[Route("api/v{version:apiVersion}/[controller]")]
public class UsersV2Controller : ApiControllerBase { }
```

---

## 12. Health Checks

```csharp
// Program.cs
builder.Services.AddHealthChecks()
    .AddDbContextCheck<ApplicationDbContext>("database")
    .AddRedis(config["Redis:ConnectionString"]!, "redis")
    .AddUrlGroup(new Uri("https://external-api.com/health"), "external-api");

app.MapHealthChecks("/health", new HealthCheckOptions
{
    ResponseWriter = UIResponseWriter.WriteHealthCheckUIResponse
});

app.MapHealthChecks("/health/ready", new HealthCheckOptions
{
    Predicate = check => check.Tags.Contains("ready")
});

app.MapHealthChecks("/health/live", new HealthCheckOptions
{
    Predicate = _ => false
});
```

---

## 13. Testing Strategy

### 13.1 Unit Tests (xUnit + Moq)

```csharp
public class CreateUserCommandHandlerTests
{
    private readonly Mock<IApplicationDbContext> _contextMock;
    private readonly CreateUserCommandHandler _handler;

    public CreateUserCommandHandlerTests()
    {
        _contextMock = new Mock<IApplicationDbContext>();
        _handler = new CreateUserCommandHandler(_contextMock.Object);
    }

    [Fact]
    public async Task Handle_ValidCommand_ReturnsSuccessWithUserId()
    {
        // Arrange
        var command = new CreateUserCommand("test@example.com", "Test User");
        _contextMock.Setup(x => x.Users.Add(It.IsAny<User>()));
        _contextMock.Setup(x => x.SaveChangesAsync(It.IsAny<CancellationToken>()))
            .ReturnsAsync(1);

        // Act
        var result = await _handler.Handle(command, CancellationToken.None);

        // Assert
        result.IsSuccess.Should().BeTrue();
        result.Value.Should().NotBeEmpty();
        _contextMock.Verify(x => x.SaveChangesAsync(It.IsAny<CancellationToken>()), Times.Once);
    }
}
```

### 13.2 Integration Tests (WebApplicationFactory)

```csharp
public class UsersControllerTests : IClassFixture<CustomWebApplicationFactory>
{
    private readonly HttpClient _client;
    private readonly CustomWebApplicationFactory _factory;

    public UsersControllerTests(CustomWebApplicationFactory factory)
    {
        _factory = factory;
        _client = factory.CreateClient();
    }

    [Fact]
    public async Task CreateUser_ValidRequest_ReturnsCreated()
    {
        // Arrange
        var request = new { Email = "test@example.com", Name = "Test" };

        // Act
        var response = await _client.PostAsJsonAsync("/api/v1/users", request);

        // Assert
        response.StatusCode.Should().Be(HttpStatusCode.Created);
    }
}
```

### 13.3 Architecture Tests (NetArchTest)

```csharp
[Fact]
public void Domain_Should_Not_Depend_On_Other_Layers()
{
    var result = Types.InAssembly(typeof(Domain.AssemblyReference).Assembly)
        .ShouldNot()
        .HaveDependencyOnAny("Application", "Infrastructure", "API")
        .GetResult();

    result.IsSuccessful.Should().BeTrue();
}

[Fact]
public void Handlers_Should_Have_Dependency_On_Domain()
{
    var result = Types.InAssembly(typeof(Application.AssemblyReference).Assembly)
        .That().HaveNameEndingWith("Handler")
        .Should().HaveDependencyOn("Domain")
        .GetResult();

    result.IsSuccessful.Should().BeTrue();
}
```

---

## 14. Configuration Summary

### 14.1 Dependency Injection Setup

```csharp
// Application/DependencyInjection.cs
public static IServiceCollection AddApplication(this IServiceCollection services)
{
    services.AddMediatR(cfg => cfg.RegisterServicesFromAssembly(Assembly.GetExecutingAssembly()));
    services.AddValidatorsFromAssembly(Assembly.GetExecutingAssembly());
    services.AddTransient(typeof(IPipelineBehavior<,>), typeof(ValidationBehaviour<,>));
    services.AddTransient(typeof(IPipelineBehavior<,>), typeof(LoggingBehaviour<,>));
    services.AddAutoMapper(Assembly.GetExecutingAssembly());
    return services;
}

// Infrastructure/DependencyInjection.cs
public static IServiceCollection AddInfrastructure(this IServiceCollection services, IConfiguration config)
{
    services.AddDbContext<ApplicationDbContext>(options =>
        options.UseSqlServer(config.GetConnectionString("DefaultConnection")));
    services.AddScoped<IApplicationDbContext>(sp => sp.GetRequiredService<ApplicationDbContext>());
    services.AddScoped<IUnitOfWork, UnitOfWork>();
    services.AddSingleton<ICacheService, RedisCacheService>();
    return services;
}

// Program.cs
builder.Services.AddApplication();
builder.Services.AddInfrastructure(builder.Configuration);
```

---

## 15. Quick Reference

### 15.1 Do's ✅

- Use `record` for DTOs and Commands/Queries
- Use `async/await` with `CancellationToken`
- Use MediatR for CQRS pattern
- Use FluentValidation for input validation
- Use Result pattern for operation outcomes
- Write unit tests for handlers
- Write integration tests for API endpoints

### 15.2 Don'ts ❌

- Don't put business logic in Controllers
- Don't use `Helpers` folder (anti-pattern)
- Don't skip validation
- Don't catch `Exception` without logging
- Don't hardcode configuration values
- Don't skip integration tests
- Don't ignore `CancellationToken`

---

## 16. Migration from Simple Structure

If your current project uses `Controllers, DTOs, Data, Helpers, Models, Services`:

| Current | Target | Action |
|---------|--------|--------|
| `Controllers/` | `API/Controllers/` | Keep, add base controller |
| `DTOs/` | `Application/Features/*/` | Convert to `record`, organize by feature |
| `Models/` | `Domain/Entities/` | Extract to Domain layer |
| `Services/` | `Application/Features/*/` | Convert to MediatR Handlers |
| `Data/` | `Infrastructure/Persistence/` | Keep, add Repository pattern |
| `Helpers/` | ❌ Dissolve | Move to appropriate layers |
| *(new)* | `Domain/Common/` | Add base entities, value objects |
| *(new)* | `Application/Common/` | Add behaviours, interfaces |
