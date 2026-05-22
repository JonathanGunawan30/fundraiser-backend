## 1. API response standards

**Success response**
```json
{ "data": { "id": 1, "name": "Jonathan" }, "message": "User retrieved successfully" }
```

**Success with pagination**
```json
{
  "data": [ ... ],
  "meta": { "current_page": 1, "per_page": 15, "total": 72 },
  "message": "Campaigns retrieved successfully"
}
```

**Success with no data**
```json
{ "data": null, "message": "Logged out successfully" }
```

**Validation error (422)**
```json
{
  "errors": {
    "email": ["The email field is required.", "The email must be a valid email address."],
    "password": ["The password must be at least 8 characters."]
  },
  "message": "Validation failed"
}
```

**General error**
```json
{ "status": "error", "errors": null, "message": "Campaign not found" }
```

**HTTP status codes standard**
- `200` — success (GET, PUT, PATCH, DELETE)
- `201` — resource created (POST)
- `401` — unauthenticated
- `403` — forbidden
- `404` — resource not found
- `409` — conflict
- `422` — validation error
- `500` — internal server error

**Implementation — `app/Traits/ApiResponse.php`**

Contain all response helpers.

---

## 2. Folder structure & layered architecture

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Auth/
│   │       ├── OAuthController.php
│   │       └── AdminAuthController.php
│   ├── Requests/
│   │   ├── BaseRequest.php
│   │   └── Auth/
│   │       └── AdminLoginRequest.php
│   └── Resources/
│       ├── UserResource.php
│       └── AdminResource.php
├── Services/
│   ├── Interfaces/
│   │   └── AuthServiceInterface.php
│   └── Implementations/
│       └── AuthServiceImpl.php
├── Repositories/
│   ├── Interfaces/
│   │   └── UserRepositoryInterface.php
│   └── Implementations/
│       └── UserRepository.php
└── Traits/
    └── ApiResponse.php
```

**Base FormRequest — `app/Http/Requests/BaseRequest.php`**

All FormRequest classes must extend `BaseRequest`, not Laravel's `FormRequest` directly.

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'errors'  => $validator->getMessageBag(),
                'message' => 'Validation failed',
            ], 422)
        );
    }
}
```

**All FormRequests extend BaseRequest**
```php
class AdminLoginRequest extends BaseRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
```

**Usage in controller — always use `$request->validated()`**
```php
public function login(AdminLoginRequest $request): JsonResponse
{
    $data = $request->validated();
    $result = $this->authService->adminLogin($data);
    return $this->success($result, 'Login successful');
}
```

> Never call `$request->all()` or `$request->input()` in controllers. Always use `$request->validated()`.

**Rules**
- [ ] Controllers must only call Service methods — no business logic, no direct DB queries
- [ ] Services contain all business logic — they call Repositories for data access
- [ ] Repositories contain all Eloquent queries — no logic, just data in/out
- [ ] Every incoming request with input must use a `FormRequest` extending `BaseRequest`
- [ ] Every model returned in a response must be wrapped in a `JsonResource`
- [ ] Validation rules go inside `FormRequest::rules()` only — never inline anywhere else

**Binding in `AppServiceProvider`**
```php
public array $singletons = [
    AuthServiceInterface::class      => AuthServiceImpl::class,
    UserRepositoryInterface::class   => UserRepository::class,
];

public function provides(): array
{
    return [
        AuthServiceInterface::class,
        UserRepositoryInterface::class,
    ];
}
```

Inject via constructor in controllers:
```php
public function __construct(
    private readonly AuthServiceInterface $authService
) {}
```

---

## 3. Running the application

The application is containerized using Docker and managed via a `Makefile`.

### Prerequisites
- Docker & Docker Compose
- `make` utility

### Common Commands

| Command | Description |
|---------|-------------|
| `make setup` | **First-time setup**: Create `.env`, build, start, install deps, and migrate |
| `make up` | Start the application containers |
| `make down` | Stop and remove containers |
| `make shell` | Open a bash shell inside the `app` container |
| `make migrate` | Run database migrations |
| `make migrate-fresh` | Reset database and run seeders |
| `make test` | Run PHPUnit tests |
| `make logs` | Follow application logs |
| `make clear` | Clear all Laravel caches |

### Development Workflow
1. **Start**: `make up`
2. **Work**: Make your changes in the code.
3. **Test**: `make test`
4. **Stop**: `make down`
