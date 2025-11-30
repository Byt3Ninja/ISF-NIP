# Test Suite Setup Guide

## Prerequisites

1. PHP 8.1 or higher
2. Composer installed
3. Database configured for testing

## Initial Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Create Test Database

Create a separate database for testing:

```sql
CREATE DATABASE growthlabs_test;
```

### 3. Configure Environment

Create or update `.env.testing`:

```env
APP_ENV=testing
APP_DEBUG=true
APP_KEY=base64:your-test-key-here

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=growthlabs_test
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

### 4. Run Migrations

```bash
php artisan migrate --env=testing
```

Or use in-memory SQLite for faster tests:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### 5. Create Factories (if needed)

Some factories may need to be created. Check if these exist:

```bash
# Check existing factories
ls database/factories/

# Create missing factories if needed
php artisan make:factory UserFactory
php artisan make:factory CompanyFactory
php artisan make:factory RoleFactory
# etc.
```

## Required Factories

You'll need factories for:

- `UserFactory`
- `CompanyFactory`
- `RoleFactory`
- `OrganizationFactory`
- `CountryFactory`
- `StateFactory`
- `CityFactory`
- `StageFactory`
- `SectorFactory`
- `SettingsFactory`
- `MilestoneFactory`
- `Team_memberFactory`

### Example Factory (UserFactory)

```php
<?php

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name_en' => $this->faker->firstName,
            'last_name_en' => $this->faker->lastName,
            'first_name_ar' => $this->faker->firstName,
            'last_name_ar' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'password' => Hash::make('password'),
            'status' => 'active',
            'gender' => $this->faker->randomElement(['male', 'female']),
            'email_verified_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
```

## Running Tests

### First Time Setup

```bash
# Generate application key for testing
php artisan key:generate --env=testing

# Run migrations
php artisan migrate --env=testing

# Run seeders if needed
php artisan db:seed --env=testing
```

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
# Unit tests
php artisan test --testsuite=Unit

# Feature tests
php artisan test --testsuite=Feature
```

### Run with Coverage

```bash
# Install Xdebug first
# Then run:
php artisan test --coverage

# Or with minimum coverage threshold
php artisan test --coverage --min=70
```

## Common Issues and Solutions

### Issue: "Class 'Factory' not found"

**Solution:** Make sure your models use the `HasFactory` trait:

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;
    // ...
}
```

### Issue: "Database connection failed"

**Solution:** 
1. Check `.env.testing` configuration
2. Ensure test database exists
3. Run migrations: `php artisan migrate --env=testing`

### Issue: "Permission denied" errors

**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
```

### Issue: "Token mismatch" in feature tests

**Solution:** Tests automatically handle CSRF. If issues persist, check middleware configuration.

### Issue: "Factory not found"

**Solution:** Create the factory:
```bash
php artisan make:factory ModelNameFactory --model=ModelName
```

## Test Database Seeding

You may want to seed test data:

```php
// In TestCase.php or specific test
protected function setUp(): void
{
    parent::setUp();
    $this->seed(); // Run all seeders
    // or
    $this->seed(SpecificSeeder::class);
}
```

## Continuous Integration Setup

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: pdo, pdo_mysql
        
    - name: Install Dependencies
      run: composer install
      
    - name: Setup Environment
      run: cp .env.example .env.testing
      
    - name: Generate Key
      run: php artisan key:generate --env=testing
      
    - name: Run Migrations
      run: php artisan migrate --env=testing
      
    - name: Run Tests
      run: php artisan test
```

## Best Practices

1. **Keep tests isolated** - Each test should be independent
2. **Use RefreshDatabase** - Ensures clean state
3. **Use factories** - Don't manually create test data
4. **Mock external services** - Keep tests fast and isolated
5. **Test edge cases** - Not just happy paths
6. **Use descriptive names** - `it_can_perform_action()` format
7. **One assertion per test** - Keep tests focused

## Next Steps

1. Create missing factories
2. Run initial test suite
3. Fix any failing tests
4. Add more tests for uncovered areas
5. Set up CI/CD pipeline
6. Aim for 70%+ code coverage

## Resources

- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [PHPUnit Docs](https://phpunit.de/documentation.html)
- [Mockery Docs](http://docs.mockery.io/)

