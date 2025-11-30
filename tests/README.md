# Test Suite Documentation

## Overview

This test suite provides comprehensive coverage for the GrowthLabs Incubator Platform, including unit tests, feature tests, and integration tests.

## Test Structure

```
tests/
├── Feature/          # Feature/Integration tests
│   ├── API/          # API endpoint tests
│   └── Web/          # Web route tests
├── Unit/             # Unit tests
│   ├── Models/       # Model tests
│   ├── Services/     # Service layer tests
│   ├── Repositories/ # Repository tests
│   └── Policies/     # Policy tests
└── TestCase.php      # Base test case
```

## Running Tests

### Run All Tests
```bash
php artisan test
# or
vendor/bin/phpunit
```

### Run Specific Test Suite
```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature
```

### Run Specific Test File
```bash
php artisan test tests/Unit/Services/UserServiceTest.php
```

### Run Specific Test Method
```bash
php artisan test --filter it_can_store_a_new_user
```

### Run with Coverage
```bash
php artisan test --coverage
```

## Test Categories

### Unit Tests

#### Services Tests
- `UserServiceTest.php` - Tests for user service methods
- `AuthServiceTest.php` - Tests for authentication service
- `CompanyServiceTest.php` - Tests for company service

#### Repository Tests
- `UserRepositoryTest.php` - Tests for user repository methods
- `CompanyRepositoryTest.php` - Tests for company repository

#### Model Tests
- `UserModelTest.php` - Tests for User model relationships and methods
- `CompanyModelTest.php` - Tests for Company model

#### Policy Tests
- `UserPolicyTest.php` - Tests for authorization policies

### Feature Tests

#### API Tests
- `AuthTest.php` - Authentication endpoints (login, register, logout)
- `UserManagementTest.php` - User CRUD operations via API

## Writing New Tests

### Unit Test Example

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\YourService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class YourServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_perform_action()
    {
        // Arrange
        $service = new YourService();
        
        // Act
        $result = $service->method();
        
        // Assert
        $this->assertTrue($result);
    }
}
```

### Feature Test Example

```php
<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class YourFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_access_endpoint()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
                         ->getJson('/api/endpoint');
        
        $response->assertStatus(200);
    }
}
```

## Test Database

Tests use a separate database configured in `phpunit.xml`. The database is refreshed before each test using `RefreshDatabase` trait.

## Mocking

Use Mockery for mocking dependencies:

```php
use Mockery;

$mock = Mockery::mock(SomeClass::class);
$mock->shouldReceive('method')->once()->andReturn('value');
```

## Factories

Use Laravel factories to create test data:

```php
$user = User::factory()->create();
$users = User::factory()->count(10)->create();
```

## Assertions

Common assertions used:

- `assertTrue()` / `assertFalse()`
- `assertEquals()` / `assertNotEquals()`
- `assertInstanceOf()`
- `assertDatabaseHas()` / `assertDatabaseMissing()`
- `assertJson()` / `assertJsonStructure()`
- `assertStatus()`

## Best Practices

1. **One assertion per test** - Keep tests focused
2. **Use descriptive test names** - `it_can_perform_action()` format
3. **Arrange-Act-Assert** - Structure tests clearly
4. **Use factories** - Don't manually create test data
5. **Mock external dependencies** - Keep tests isolated
6. **Test edge cases** - Not just happy paths
7. **Keep tests fast** - Use RefreshDatabase sparingly
8. **Test behavior, not implementation** - Focus on outcomes

## Continuous Integration

Tests should run automatically on:
- Pull requests
- Before merging to main
- On deployment

## Coverage Goals

- **Unit Tests**: 80%+ coverage
- **Feature Tests**: Critical paths 100%
- **Overall**: 70%+ coverage

## Troubleshooting

### Tests failing due to database
```bash
php artisan migrate:fresh --env=testing
```

### Tests failing due to cache
```bash
php artisan config:clear
php artisan cache:clear
```

### Permission issues
```bash
chmod -R 775 storage bootstrap/cache
```

## Additional Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Mockery Documentation](http://docs.mockery.io/)

