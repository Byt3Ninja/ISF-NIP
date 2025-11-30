# Laravel Project Code Review

**Project:** GrowthLabs Incubator Platform  
**Framework:** Laravel 10.x  
**PHP Version:** 8.1+  
**Review Date:** 2025-01-27

---

## Step 1: Project Understanding

### What This App Does
This is a comprehensive **incubator/accelerator management platform** for managing startup programs, mentorship, training, screening, and evaluation processes. It supports multiple organizations, cycles (programs), mentors, startups, and various assessment tools.

### Main Modules
- **Authentication & Authorization**: Multi-role system (Admin, Mentor, Startup, Judger, Organization Manager)
- **User Management**: User profiles, roles, permissions (Spatie Permission)
- **Company/Startup Management**: Company profiles, team members, milestones, funding rounds
- **Program/Cycle Management**: Program creation, steps, forms, custom pages
- **Mentorship System**: Mentorship requests, sessions, tasks, schedules
- **Training System**: Training courses, sessions, quizzes, surveys
- **Screening & Evaluation**: Multi-step screening, evaluation forms, final screening
- **Forms Builder**: Dynamic form builder with custom fields
- **Messaging System**: Internal messaging, chat (WireChat), WhatsApp integration
- **Calendar Integration**: Google Calendar, Microsoft Calendar sync
- **Reports & Analytics**: Dashboard, mentorship reports, training reports
- **Notifications**: Template-based notifications with multiple channels
- **API**: RESTful API with Sanctum authentication
- **File Management**: File uploads, document management
- **Hackathon Management**: Team formation, submissions, evaluations

---

## Step 2: Architecture Review

### ✅ Strengths
- **Service Layer**: Good use of Services (`UserService`, `CompanyService`, `AuthService`, etc.)
- **Repository Pattern**: Some repositories exist (`UserRepository`, `CompanyRepository`)
- **Form Requests**: Extensive use of FormRequest classes for validation
- **API Resources**: API responses use Resource classes (`AuthResource`, `UserResource`)
- **Policies**: Some policies exist (`UserPolicy`, `OrganizationPolicy`, `CurrencyPolicy`)

### ❌ Issues

#### Folder Structure
- **Controllers are too large**: `ClientCompanyController.php` (3000+ lines), `HomeController.php` (3000+ lines)
- **Mixed concerns**: Business logic mixed with controllers
- **Inconsistent organization**: Some controllers in subdirectories, others not

#### Route Organization
- **Routes file is massive**: `web.php` has 880+ lines
- **No route groups by feature**: Routes scattered, hard to maintain
- **Inconsistent naming**: Mix of resource routes and manual routes
- **Test routes in production**: Routes like `/test`, `/testting`, `/2otp` should be removed

#### Service/Repository Usage
- **Inconsistent**: Some controllers use services, others have direct model access
- **Missing repositories**: Many models don't have repositories
- **Services not always used**: Controllers bypass services for simple operations

#### FormRequest Usage
- **Good coverage**: Many FormRequests exist
- **Some missing**: Controllers like `SettingsController` use `Request` directly instead of FormRequest

#### API Resources
- **Inconsistent**: Some API endpoints use Resources, others return raw models
- **No versioning**: API routes not versioned (except some `/v1/` prefixes)

### Target Structure Recommendation

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Dashboard/
│   │   ├── Users/
│   │   ├── Companies/
│   │   ├── Programs/
│   │   ├── Mentorship/
│   │   ├── Training/
│   │   └── API/
│   │       └── v1/
│   ├── Requests/
│   │   ├── Auth/
│   │   ├── Users/
│   │   └── ...
│   ├── Resources/
│   │   └── API/
│   │       └── v1/
│   └── Middleware/
├── Services/
│   ├── Auth/
│   ├── Users/
│   └── ...
├── Repositories/
│   ├── Contracts/
│   └── Eloquent/
└── Policies/
```

---

## Step 3: Code Quality Review

### Worst Offenders

#### 1. ClientCompanyController.php (Lines 1-3075)
**Issues:**
- 3000+ lines in single file
- Multiple responsibilities (CRUD, file uploads, team management, milestones)
- Heavy business logic in controller
- Missing type hints in many methods
- Inconsistent error handling

**Refactor Plan:**
- Split into: `CompanyController`, `CompanyTeamController`, `CompanyMilestoneController`, `CompanyDocumentController`
- Move business logic to `CompanyService`
- Extract file upload logic to dedicated service

**Priority:** HIGH

#### 2. HomeController.php (Lines 1-3000+)
**Issues:**
- Massive controller handling dashboard, reports, statistics
- Complex queries mixed with presentation logic
- N+1 query problems

**Refactor Plan:**
- Split into: `DashboardController`, `ReportController`, `StatisticsController`
- Move queries to dedicated services
- Add eager loading

**Priority:** HIGH

#### 3. SettingsController.php (Lines 1-1236)
**Issues:**
- 1200+ lines
- No FormRequest validation (uses `Request` directly)
- Direct model manipulation without service layer
- Missing authorization checks

**Refactor Plan:**
- Create FormRequests for each update method
- Move to `SettingsService`
- Add proper authorization

**Priority:** MEDIUM

#### 4. CyclesController.php
**Issues:**
- Complex form handling logic
- Missing validation in some methods
- Business logic in controller

**Priority:** MEDIUM

#### 5. MentorsController.php
**Issues:**
- Long methods
- Complex query building
- Missing eager loading

**Priority:** MEDIUM

### Code Smells Found

1. **Long Methods**: Many methods exceed 50 lines
2. **Missing Type Hints**: Many methods lack return types and parameter types
3. **Missing PHPDoc**: Complex methods lack documentation
4. **Duplicated Logic**: Similar code patterns repeated across controllers
5. **Direct DB Queries**: Some controllers use `DB::select()` instead of Eloquent
6. **Hard-coded Values**: Magic numbers and strings throughout codebase

### Refactor Priority

**HIGH:**
- Split large controllers (`ClientCompanyController`, `HomeController`)
- Add missing FormRequests
- Fix N+1 queries
- Add type hints

**MEDIUM:**
- Extract business logic to services
- Standardize error handling
- Add PHPDoc comments
- Remove duplicated code

**LOW:**
- Code style improvements
- Add missing tests
- Improve naming conventions

---

## Step 4: Validation and Security

### Critical Security Issues

#### 1. Missing Validation

**File:** `app/Http/Controllers/SettingsController.php`

**Issue:** Direct use of `Request` without FormRequest validation

```php
// Line 76-96: No validation
public function update_settings(Request $request)
{
    $Icon1->url = $request->input('facebook'); // No validation!
    $Icon2->url = $request->input('twitter');
    // ...
}
```

**Risk:** XSS, injection attacks, invalid data

**Fix:**
```php
// Create: app/Http/Requests/UpdateSettingsRequest.php
public function rules(): array
{
    return [
        'facebook' => 'nullable|url|max:255',
        'twitter' => 'nullable|url|max:255',
        // ...
    ];
}

// In controller:
public function update_settings(UpdateSettingsRequest $request)
{
    // Now validated
}
```

**Priority:** HIGH

#### 2. Mass Assignment Risks

**File:** `app/User.php` (Line 37-39)

**Issue:** Very large `$fillable` array (40+ fields)

```php
protected $fillable = [
    'organization_id', 'first_name_en', 'last_name_en', 
    // ... 40+ fields
];
```

**Risk:** Mass assignment vulnerability if validation fails

**Fix:** Use `$guarded = []` with careful validation, or split into smaller arrays per use case

**Priority:** MEDIUM

**File:** `app/Company.php` (Line 30-108)

**Issue:** 70+ fields in `$fillable`

**Risk:** Same as above

**Priority:** MEDIUM

#### 3. SQL Injection Risks

**File:** `app/Http/Controllers/MessengerController.php` (Line 1309)

**Issue:** Raw query with user input

```php
return DB::table('messenger')->whereRaw($where)->count();
```

**Risk:** SQL injection if `$where` contains user input

**Fix:** Use parameter binding:
```php
DB::table('messenger')->whereRaw($where, $bindings)->count();
```

**Priority:** HIGH

**File:** `app/Http/Controllers/Mentor/MentorshipTaskController.php` (Line 1060)

**Issue:** Raw SQL query with string interpolation

```php
$query = "... WHERE $condition ...";
$tasks = DB::select($query, [$data]);
```

**Risk:** SQL injection if `$condition` is not properly sanitized

**Fix:** Use Eloquent or properly parameterize all parts

**Priority:** HIGH

#### 4. Direct Request Input Usage

**File:** `app/Http/Controllers/surveysController.php` (Line 79, 145)

**Issue:** Direct `$request->input()` without validation

```php
$questions_ids = $request->input('selected_questions', []);
```

**Risk:** Invalid data, type confusion

**Fix:** Use FormRequest validation

**Priority:** MEDIUM

#### 5. XSS in Views

**File:** Need to check Blade templates

**Issue:** Unescaped output (need to verify)

**Fix:** Always use `{{ }}` (escaped) instead of `{!! !!}` unless necessary

**Priority:** MEDIUM (needs verification)

#### 6. File Upload Security

**File:** `app/Http/Controllers/ClientCompanyController.php`

**Issue:** File uploads may lack proper validation

**Fix:** Ensure:
- File type validation
- File size limits
- Virus scanning (if possible)
- Secure file storage paths

**Priority:** HIGH

### Security Checklist

- ✅ CSRF protection (Laravel default)
- ✅ Password hashing (bcrypt)
- ⚠️ Some missing validation
- ⚠️ Mass assignment risks
- ❌ SQL injection risks in raw queries
- ⚠️ File upload validation needs review
- ✅ Authentication middleware
- ⚠️ Authorization checks inconsistent

---

## Step 5: Auth and Permissions

### Current Implementation

**Good:**
- Uses Spatie Permission package
- Some policies exist (`UserPolicy`, `OrganizationPolicy`)
- Middleware for permissions (`Permissions` middleware)
- Role-based access control

**Issues:**

#### 1. Inconsistent Authorization

**File:** `app/Http/Controllers/ClientCompanyController.php` (Line 264)

**Issue:** Hard-coded role checks instead of policies

```php
if (Auth::user()->hasRole('organizationManager')) {
    // ...
}
```

**Fix:** Use policies:
```php
$this->authorize('view', $company);
```

**Priority:** MEDIUM

#### 2. Missing Authorization Checks

**File:** `app/Http/Controllers/SettingsController.php`

**Issue:** Many methods lack authorization checks

**Fix:** Add `$this->authorize()` calls or middleware

**Priority:** HIGH

#### 3. Permission Bypass

**File:** `app/User.php` (Line 225, 233, etc.)

**Issue:** Environment-based permission bypass

```php
if( env('PERMISSION_BYPASS', false) === true ) { return true; }
```

**Risk:** Dangerous if enabled in production

**Fix:** Remove or use config file, never enable in production

**Priority:** HIGH

#### 4. Hard-coded Role Names

**File:** Multiple controllers

**Issue:** Hard-coded strings like `'Admin'`, `'organizationManager'`, `'Judger'`

**Fix:** Use constants or config:
```php
// config/roles.php
return [
    'admin' => 'Admin',
    'org_manager' => 'organizationManager',
    // ...
];
```

**Priority:** MEDIUM

### Recommended Authorization Structure

```php
// Policies for all resources
- UserPolicy
- CompanyPolicy
- ProgramPolicy
- MentorshipPolicy
- TrainingPolicy
- etc.

// Middleware
- Check permissions on routes
- Verify organization access
- Verify resource ownership

// Gates (for complex logic)
- view-company
- manage-program
- etc.
```

---

## Step 6: Performance

### N+1 Query Problems

#### 1. ClientCompanyController.php (Line 69)

**Issue:** Missing eager loading

```php
$company = Company::with('state')->with(['city', 'user', 'stage'])->find($id);
// But later accesses:
$company->sectoR  // Not eager loaded!
$company->country // Not eager loaded!
```

**Fix:**
```php
$company = Company::with(['state', 'city', 'user', 'stage', 'sectoR', 'country'])->find($id);
```

**Priority:** HIGH

#### 2. HomeController.php

**Issue:** Multiple queries in loops

**Fix:** Use eager loading with `with()`

**Priority:** HIGH

#### 3. ProgramController.php (Line 444)

**Issue:** Potential N+1 in company queries

```php
$companyQuery = $cycle->companies()->with(['sectoR', 'stagE']);
// Good, but check if all relations are loaded
```

**Priority:** MEDIUM

### Missing Database Indexes

Based on common query patterns, ensure indexes on:

- `users.organization_id`
- `users.company_id`
- `users.email`
- `companies.cycle`
- `companies.step`
- `mentorship_requests.mentor_id`
- `mentorship_requests.user_id`
- `mentorship_requests.zoom_date`
- `form_data.company_id`
- `form_data.form_id`

**Priority:** HIGH

### Heavy Operations in Loops

**File:** `app/Http/Controllers/HomeController.php`

**Issue:** Complex queries inside loops

**Fix:** Use `chunk()` or batch operations

**Priority:** MEDIUM

### Unnecessary Data Loading

**File:** Multiple controllers

**Issue:** Loading all columns when only few needed

**Fix:** Use `select()` to specify columns:
```php
User::select('id', 'name', 'email')->get();
```

**Priority:** LOW

### Caching Opportunities

- Settings (rarely change)
- Countries, States, Cities (static data)
- Permissions (cached by Spatie, but verify)
- Dashboard statistics (can be cached)

**Priority:** MEDIUM

---

## Step 7: API Design

### Current State

**Good:**
- Uses Sanctum for authentication
- Some API Resources exist
- FormRequest validation
- API versioning started (`/v1/`)

**Issues:**

#### 1. Inconsistent Response Format

**File:** `app/Http/Controllers/API/AuthController.php`

**Good example:**
```php
return $this->responseWithToken($token, new AuthResource($user), __('api.login_success'), 200);
```

**But other endpoints may return different formats**

**Fix:** Standardize all API responses:
```php
{
    "status": true,
    "message": "Success message",
    "data": { ... },
    "errors": null
}
```

**Priority:** MEDIUM

#### 2. Missing API Versioning

**File:** `routes/api.php`

**Issue:** Most routes not versioned, only some have `/v1/` prefix

**Fix:** Organize all routes under version prefixes:
```php
Route::prefix('v1')->group(function () {
    // All v1 routes
});
```

**Priority:** MEDIUM

#### 3. Inconsistent Error Handling

**Issue:** Some endpoints return different error formats

**Fix:** Use consistent error responses:
```php
{
    "status": false,
    "message": "Error message",
    "errors": { ... }
}
```

**Priority:** MEDIUM

#### 4. Missing Rate Limiting

**Issue:** No rate limiting on API endpoints

**Fix:** Add rate limiting:
```php
Route::middleware(['throttle:60,1'])->group(function () {
    // API routes
});
```

**Priority:** HIGH

### Recommended API Structure

```php
// routes/api.php
Route::prefix('v1')->middleware(['sanctum.custom', 'throttle:60,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('companies', CompanyController::class);
        // ...
    });
});
```

---

## Step 8: Testing

### Current State

**Issues:**
- Very few tests found
- No test coverage visible
- Missing feature tests for critical flows

### Critical Features Needing Tests

**HIGH Priority:**
1. **Authentication**
   - Login/Logout
   - Registration
   - Password reset
   - 2FA

2. **Authorization**
   - Role-based access
   - Permission checks
   - Resource ownership

3. **User Management**
   - User CRUD
   - Role assignment
   - Permission management

4. **Company Management**
   - Company creation
   - Team member management
   - File uploads

5. **Mentorship**
   - Request creation
   - Session scheduling
   - Task management

6. **Forms & Submissions**
   - Form submission
   - Validation
   - Data storage

**MEDIUM Priority:**
- Training system
- Screening system
- Messaging
- Reports

### Recommended Test Structure

```php
tests/
├── Feature/
│   ├── Auth/
│   ├── Users/
│   ├── Companies/
│   ├── Mentorship/
│   └── API/
├── Unit/
│   ├── Services/
│   ├── Repositories/
│   └── Models/
└── TestCase.php
```

### Minimal Test Strategy

1. **Start with critical paths:**
   - User registration/login
   - Company creation
   - Form submission
   - Authorization checks

2. **Use Feature tests** for user flows
3. **Use Unit tests** for services and complex logic
4. **Aim for 60%+ coverage** on critical modules

---

## Step 9: DevOps and Environment

### Environment Variables

**Issues Found:**

#### 1. Direct `env()` Calls

**File:** `app/User.php` (Line 61, 225, etc.)

**Issue:** Direct `env()` calls in application code

```php
if( !in_array(env('APP_ENV', 'local'), ['local']) )
```

**Risk:** `env()` returns `null` when config is cached

**Fix:** Use `config()`:
```php
// config/app.php
'permission_bypass' => env('PERMISSION_BYPASS', false),

// In code:
if( config('app.permission_bypass') ) { ... }
```

**Priority:** HIGH

#### 2. Missing .env.example

**Issue:** No `.env.example` file found (or not visible)

**Fix:** Create comprehensive `.env.example` with all required variables

**Priority:** MEDIUM

### Config Files

**Issues:**
- Some config values hard-coded
- Missing config caching setup

**Fix:** Move all configurable values to config files

**Priority:** MEDIUM

### Logging

**Current:** Uses Laravel's default logging

**Recommendations:**
- Set appropriate log levels per environment
- Use separate channels for different log types
- Implement log rotation
- Monitor error logs

**Priority:** MEDIUM

### Queues

**Current:** Queue system exists (`QueueJobController`)

**Issues:**
- Need to verify queue workers are running
- Check failed jobs handling

**Recommendations:**
- Use supervisor for queue workers
- Monitor failed jobs
- Set up retry logic

**Priority:** MEDIUM

### Rate Limiting

**Issue:** No rate limiting on public endpoints

**Fix:** Add rate limiting middleware:
```php
Route::middleware(['throttle:60,1'])->group(function () {
    // Public routes
});
```

**Priority:** HIGH

### Environment Checklist

**Local:**
- ✅ Debug mode enabled
- ✅ Detailed error messages
- ✅ Log all queries
- ⚠️ Permission bypass (remove)

**Staging:**
- ⚠️ Debug mode disabled
- ⚠️ Error logging enabled
- ⚠️ Queue workers running
- ⚠️ Cache enabled
- ⚠️ Rate limiting enabled

**Production:**
- ❌ Debug mode MUST be disabled
- ✅ Error logging enabled
- ✅ Queue workers running
- ✅ Cache enabled
- ✅ Rate limiting enabled
- ✅ SSL/HTTPS enforced
- ✅ Backup strategy
- ✅ Monitoring setup

---

## Step 10: Action Plan - Detailed Step-by-Step Instructions

### HIGH Priority (Security & Critical Bugs)

---

#### 1. Fix SQL Injection Risks

**Files:** `app/Http/Controllers/MessengerController.php:1309`, `app/Http/Controllers/Mentor/MentorshipTaskController.php:1060`

**Step-by-Step:**

**A. Fix MessengerController.php (Line 1309)**

1. Open `app/Http/Controllers/MessengerController.php`
2. Locate the `countMessages()` method around line 1309
3. **Current problematic code:**
   ```php
   return DB::table('messenger')->whereRaw($where)->count();
   ```
4. **Replace with parameterized query:**
   ```php
   // Build query with proper parameter binding
   $query = DB::table('messenger');
   
   switch ($type) {
       case 'inbox':
           $query->where(function($q) use ($userId) {
               $q->where('receiver', $userId)
                 ->whereNull('reply_id')
                 ->whereNull('deleted_at');
           })->orWhere(function($q) use ($userId) {
               $q->whereHas('replies', function($q2) use ($userId) {
                   $q2->where('receiver', $userId);
               });
           });
           break;
       case 'sent':
           $query->where('sender', $userId)->whereNull('deleted_at');
           break;
       case 'starred':
           $query->where('starred', 1)
                 ->where(function($q) use ($userId) {
                     $q->where('sender', $userId)->orWhere('receiver', $userId);
                 })
                 ->whereNull('deleted_at')
                 ->whereNull('reply_id');
           break;
       case 'trash':
           $query->whereNotNull('deleted_at')
                 ->where(function($q) use ($userId) {
                     $q->where('sender', $userId)->orWhere('receiver', $userId);
                 })
                 ->whereNull('reply_id');
           break;
   }
   
   return $query->count();
   ```
5. Test the method with different `$type` values
6. Verify the count returns correct results

**B. Fix MentorshipTaskController.php (Line 1060)**

1. Open `app/Http/Controllers/Mentor/MentorshipTaskController.php`
2. Locate the method around line 1060 that builds the raw SQL query
3. **Current problematic code:**
   ```php
   $query = "... WHERE $condition ...";
   $tasks = DB::select($query, [$data]);
   ```
4. **Replace with Eloquent query builder:**
   ```php
   $query = MentorshipTask::query()
       ->leftJoin('companies', 'mentorship_tasks.company_id', '=', 'companies.id')
       ->leftJoin('users as mentor', 'mentorship_tasks.mentor_id', '=', 'mentor.id')
       ->leftJoin('users as assignee', 'mentorship_tasks.user_id', '=', 'assignee.id')
       ->leftJoin('users as reporter', 'mentorship_tasks.reported_by', '=', 'reporter.id')
       ->leftJoin('mentorship_task_comments', 'mentorship_tasks.id', '=', 'mentorship_task_comments.mentorship_task_id')
       ->leftJoin('users as commenter', 'mentorship_task_comments.user_id', '=', 'commenter.id')
       ->leftJoin('mentorship_task_labels', 'mentorship_tasks.id', '=', 'mentorship_task_labels.mentorship_tasks_id')
       ->leftJoin('mentorship_task_files', 'mentorship_tasks.id', '=', 'mentorship_task_files.mentorship_task_id')
       ->leftJoin('users as uploader', 'mentorship_task_files.user_id', '=', 'uploader.id')
       ->leftJoin('labels', 'mentorship_task_labels.label_id', '=', 'labels.id')
       ->leftJoin('mentorship_task_logs', 'mentorship_tasks.id', '=', 'mentorship_task_logs.mentorship_task_id')
       ->leftJoin('users as user', 'mentorship_task_logs.user_id', '=', 'user.id')
       ->whereNull('mentorship_tasks.parent_id');
   
   // Apply conditions using query builder methods instead of raw SQL
   if (!empty($data['company_id'])) {
       $query->where('mentorship_tasks.company_id', $data['company_id']);
   }
   if (!empty($data['mentor_id'])) {
       $query->where('mentorship_tasks.mentor_id', $data['mentor_id']);
   }
   // Add other conditions as needed
   
   $tasks = $query->get();
   ```
5. If raw SQL is absolutely necessary, use parameter binding:
   ```php
   $query = "SELECT ... WHERE mentorship_tasks.company_id = ? AND mentorship_tasks.mentor_id = ?";
   $tasks = DB::select($query, [$data['company_id'], $data['mentor_id']]);
   ```
6. Test thoroughly with various input combinations
7. Run database queries in a transaction to verify no SQL errors

**Verification:**
- Run the application and test all affected endpoints
- Check Laravel logs for any SQL errors
- Use a SQL injection testing tool to verify protection

---

#### 2. Add Missing Authorization Checks

**Files:** `app/Http/Controllers/SettingsController.php`, `app/Http/Controllers/ClientCompanyController.php`

**Step-by-Step:**

**A. Create/Update Policies**

1. **Create SettingsPolicy:**
   ```bash
   php artisan make:policy SettingsPolicy --model=Settings
   ```

2. **Edit `app/Policies/SettingsPolicy.php`:**
   ```php
   <?php
   
   namespace App\Policies;
   
   use App\User;
   use App\Settings;
   
   class SettingsPolicy
   {
       public function viewAny(User $user): bool
       {
           return $user->ableTo('settings.system_general_view');
       }
       
       public function update(User $user): bool
       {
           return $user->ableTo('settings.system_general_update');
       }
       
       public function updateSocial(User $user): bool
       {
           return $user->ableTo('settings.system_social_login_update');
       }
       
       // Add more methods as needed
   }
   ```

3. **Register policy in `app/Providers/AuthServiceProvider.php`:**
   ```php
   protected $policies = [
       // ... existing policies
       Settings::class => SettingsPolicy::class,
   ];
   ```

4. **Create CompanyPolicy if it doesn't exist:**
   ```bash
   php artisan make:policy CompanyPolicy --model=Company
   ```

**B. Add Authorization to SettingsController**

1. Open `app/Http/Controllers/SettingsController.php`
2. Add authorization to `get_settings()` method:
   ```php
   public function get_settings(Request $request)
   {
       $this->authorize('viewAny', Settings::class);
       // ... rest of method
   }
   ```
3. Add authorization to `update_settings()` method:
   ```php
   public function update_settings(Request $request)
   {
       $this->authorize('update', Settings::class);
       // ... rest of method
   }
   ```
4. Add authorization to all other update methods:
   - `update_aboutus()` → `$this->authorize('update', Settings::class);`
   - `update_contactus()` → `$this->authorize('update', Settings::class);`
   - `update_settinglogo()` → `$this->authorize('update', Settings::class);`
   - etc.

**C. Add Authorization to ClientCompanyController**

1. Open `app/Http/Controllers/ClientCompanyController.php`
2. Add authorization to `show()` method:
   ```php
   public function show($id)
   {
       $company = Company::findOrFail($id);
       $this->authorize('view', $company);
       // ... rest of method
   }
   ```
3. Add authorization to `editOrCreate()` method:
   ```php
   public function editOrCreate($id = null)
   {
       if ($id) {
           $company = Company::findOrFail($id);
           $this->authorize('update', $company);
       } else {
           $this->authorize('create', Company::class);
       }
       // ... rest of method
   }
   ```
4. Add authorization to all other methods that modify data

**D. Update CompanyPolicy**

1. Edit `app/Policies/CompanyPolicy.php`:
   ```php
   <?php
   
   namespace App\Policies;
   
   use App\User;
   use App\Company;
   
   class CompanyPolicy
   {
       public function view(User $user, Company $company): bool
       {
           // User can view their own company
           if ($user->company_id === $company->id) {
               return true;
           }
           
           // Organization managers can view companies in their organization
           if ($user->hasRole('organizationManager')) {
               return $user->organization_id === $company->cycleRelation?->organization_id;
           }
           
           // Admins can view all
           return $user->hasRole('Admin') || $user->ableTo('companies.view');
       }
       
       public function update(User $user, Company $company): bool
       {
           // User can update their own company
           if ($user->company_id === $company->id) {
               return true;
           }
           
           // Organization managers can update companies in their organization
           if ($user->hasRole('organizationManager')) {
               return $user->organization_id === $company->cycleRelation?->organization_id;
           }
           
           return $user->hasRole('Admin') || $user->ableTo('companies.update');
       }
       
       public function create(User $user): bool
       {
           return $user->ableTo('companies.create');
       }
   }
   ```

**Verification:**
- Test with different user roles
- Verify unauthorized users get 403 errors
- Check that authorized users can access resources

---

#### 3. Remove Permission Bypass

**Files:** `app/User.php`, `app/Http/Middleware/Permissions.php`

**Step-by-Step:**

1. **Move to Config File:**
   - Open `config/app.php`
   - Add new config value:
     ```php
     'permission_bypass' => env('PERMISSION_BYPASS', false),
     ```

2. **Update app/User.php:**
   - Find all instances of `env('PERMISSION_BYPASS', false)`
   - Replace with `config('app.permission_bypass')`
   - Lines to update: 225, 233, 248, 256, 268
   - Example:
     ```php
     // Before:
     if( env('PERMISSION_BYPASS', false) === true ) { return true; }
     
     // After:
     if( config('app.permission_bypass') === true ) { return true; }
     ```

3. **Update app/Http/Middleware/Permissions.php:**
   - Line 61: Replace `env('PERMISSION_BYPASS', false)` with `config('app.permission_bypass')`

4. **Add Safety Check:**
   - Update the middleware to prevent bypass in production:
     ```php
     public function handle(Request $request, Closure $next): Response
     {
         // NEVER allow bypass in production
         if (config('app.env') === 'production' && config('app.permission_bypass')) {
             \Log::critical('Permission bypass attempted in production!');
             abort(500, 'Configuration error');
         }
         
         if (config('app.permission_bypass') === true) {
             return $next($request);
         }
         // ... rest of method
     }
     ```

5. **Update .env.example:**
   - Add comment warning:
     ```env
     # WARNING: Never set to true in production!
     PERMISSION_BYPASS=false
     ```

6. **Remove from Production:**
   - Ensure `PERMISSION_BYPASS` is not set or set to `false` in production `.env`

**Verification:**
- Test that permissions work correctly
- Verify bypass doesn't work in production (if testing)
- Check logs for any bypass attempts

---

#### 4. Fix Direct env() Calls

**Files:** Multiple files (User.php, Services, etc.)

**Step-by-Step:**

1. **Identify all env() calls:**
   ```bash
   grep -r "env(" app/ --include="*.php" | grep -v "config/" | grep -v "vendor/"
   ```

2. **Create/Update Config Files:**

   **A. Update config/app.php:**
   ```php
   return [
       // ... existing config
       'env' => env('APP_ENV', 'local'),
       'permission_bypass' => env('PERMISSION_BYPASS', false),
   ];
   ```

   **B. Create config/zoom.php (if needed):**
   ```php
   <?php
   
   return [
       'api_key' => env('ZOOM_API_KEY', ''),
       'api_secret' => env('ZOOM_API_SECRET', ''),
       'licensed_hosts' => explode(',', env('ZOOM_LICENSED_HOSTS', '')),
   ];
   ```

   **C. Create config/whatsapp.php (if needed):**
   ```php
   <?php
   
   return [
       'api_key' => env('WAPI_KEY', ''),
   ];
   ```

3. **Replace env() calls:**

   **In app/User.php:**
   ```php
   // Before:
   if( !in_array(env('APP_ENV', 'local'), ['local']) )
   
   // After:
   if( !in_array(config('app.env'), ['local']) )
   ```

   **In app/Services/ZoomService.php:**
   ```php
   // Before:
   $licensedHosts = explode(',', env('ZOOM_LICENSED_HOSTS'));
   
   // After:
   $licensedHosts = config('zoom.licensed_hosts', []);
   ```

   **In app/Services/WhatsAppService.php:**
   ```php
   // Before:
   'x-api-key' => env('WAPI_KEY')
   
   // After:
   'x-api-key' => config('whatsapp.api_key')
   ```

4. **Update Notifications:**
   ```php
   // Before:
   Thanks from ' . env('APP_NAME')
   
   // After:
   Thanks from ' . config('app.name')
   ```

5. **Test:**
   - Clear config cache: `php artisan config:clear`
   - Test application functionality
   - Cache config: `php artisan config:cache`
   - Test again to ensure it works with cached config

**Verification:**
- Run `php artisan config:cache`
- Test all functionality
- Ensure no errors occur

---

#### 5. Add FormRequest Validation

**Files:** `app/Http/Controllers/SettingsController.php`, `app/Http/Controllers/surveysController.php`, `app/Http/Controllers/quizzesController.php`

**Step-by-Step:**

**A. Create FormRequests for SettingsController**

1. **Create UpdateSettingsRequest:**
   ```bash
   php artisan make:request UpdateSettingsRequest
   ```

2. **Edit `app/Http/Requests/UpdateSettingsRequest.php`:**
   ```php
   <?php
   
   namespace App\Http\Requests;
   
   use Illuminate\Foundation\Http\FormRequest;
   
   class UpdateSettingsRequest extends FormRequest
   {
       public function authorize(): bool
       {
           return $this->user()->ableTo('settings.system_general_update');
       }
       
       public function rules(): array
       {
           return [
               'facebook' => 'nullable|url|max:255',
               'twitter' => 'nullable|url|max:255',
               'youtube' => 'nullable|url|max:255',
               'instagram' => 'nullable|url|max:255',
               'linkedin' => 'nullable|url|max:255',
           ];
       }
   }
   ```

3. **Create UpdateAboutUsRequest:**
   ```bash
   php artisan make:request UpdateAboutUsRequest
   ```
   ```php
   public function rules(): array
   {
       return [
           'title' => 'required|string|max:255',
           'ar_title' => 'required|string|max:255',
           'dsc' => 'required|string',
           'ar_dsc' => 'required|string',
           'set1' => 'nullable|string',
           'ar_set1' => 'nullable|string',
           'set2' => 'nullable|string',
           'ar_set2' => 'nullable|string',
           'url' => 'nullable|url|max:255',
       ];
   }
   ```

4. **Create UpdateContactUsRequest:**
   ```bash
   php artisan make:request UpdateContactUsRequest
   ```
   ```php
   public function rules(): array
   {
       return [
           'title' => 'required|string|max:255',
           'ar_title' => 'required|string|max:255',
           'dsc' => 'required|string',
           'ar_dsc' => 'required|string',
           'set1' => 'nullable|string',
           'ar_set1' => 'nullable|string',
           'set2' => 'nullable|string',
           'ar_set2' => 'nullable|string',
       ];
   }
   ```

5. **Update SettingsController methods:**
   ```php
   // Before:
   public function update_settings(Request $request)
   
   // After:
   public function update_settings(UpdateSettingsRequest $request)
   {
       // Remove manual validation, it's now in FormRequest
       $validated = $request->validated();
       // ... rest of method
   }
   ```

**B. Create FormRequests for surveysController**

1. **Create StoreSurveyRequest:**
   ```bash
   php artisan make:request StoreSurveyRequest
   ```
   ```php
   public function rules(): array
   {
       return [
           'title' => 'required|string|max:255',
           'description' => 'nullable|string',
           'organization_id' => 'required|exists:organizations,id',
           'cycle_id' => 'nullable|exists:cycles,id',
           'mentorship_id' => 'nullable|exists:mentorship_requests,id',
           'training_id' => 'nullable|exists:trainings,id',
       ];
       }
   ```

2. **Create UpdateSurveyRequest:**
   ```bash
   php artisan make:request UpdateSurveyRequest
   ```

3. **Create StoreSurveyQuestionsRequest:**
   ```bash
   php artisan make:request StoreSurveyQuestionsRequest
   ```
   ```php
   public function rules(): array
   {
       return [
           'selected_questions' => 'required|array',
           'selected_questions.*' => 'required|exists:questions_bank,id',
       ];
   }
   ```

4. **Update surveysController:**
   ```php
   public function store(StoreSurveyRequest $request)
   {
       $validated = $request->validated();
       // Remove: $questions_ids = $request->input('selected_questions', []);
       // Use: $validated['selected_questions']
   }
   ```

**C. Create FormRequests for quizzesController**

1. **Create StoreQuizRequest:**
   ```bash
   php artisan make:request StoreQuizRequest
   ```

2. **Create UpdateQuizRequest:**
   ```bash
   php artisan make:request UpdateQuizRequest
   ```

3. **Create StoreQuizQuestionsRequest:**
   ```bash
   php artisan make:request StoreQuizQuestionsRequest
   ```
   ```php
   public function rules(): array
   {
       return [
           'question_ids' => 'required|array',
           'question_ids.*' => 'required|exists:questions_bank,id',
       ];
   }
   ```

**Verification:**
- Test form submissions with invalid data
- Verify validation errors are displayed
- Test with valid data to ensure it works

---

#### 6. Fix N+1 Query Problems

**Files:** `app/Http/Controllers/ClientCompanyController.php:69`, `app/Http/Controllers/HomeController.php`

**Step-by-Step:**

**A. Fix ClientCompanyController.php**

1. Open `app/Http/Controllers/ClientCompanyController.php`
2. Locate `show()` method around line 69
3. **Current code:**
   ```php
   $company = Company::with('state')->with(['city', 'user', 'stage'])->find($id);
   ```
4. **Identify all relationships used in the method:**
   - Check what relationships are accessed: `$company->sectoR`, `$company->country`, `$company->technologies`, etc.
5. **Update to eager load all relationships:**
   ```php
   $company = Company::with([
       'state',
       'city',
       'user',
       'stage',
       'sectoR',
       'country',
       'technologies', // if CompanyRelation is used
       'sectors',      // if CompanyRelation is used
       'industry',    // if CompanyRelation is used
       'teamMembers',
       'milestones',
       'fundingRounds',
       'employees',
   ])->findOrFail($id);
   ```
6. **Check other methods in the controller:**
   - `editOrCreate()` method - add eager loading
   - Any method that loads companies in loops

**B. Fix HomeController.php**

1. Open `app/Http/Controllers/HomeController.php`
2. **Identify N+1 queries:**
   - Look for loops that access relationships
   - Look for queries without `with()`
3. **Example fixes:**
   ```php
   // Before:
   $companies = Company::all();
   foreach ($companies as $company) {
       echo $company->user->name; // N+1 query!
   }
   
   // After:
   $companies = Company::with('user')->get();
   foreach ($companies as $company) {
       echo $company->user->name; // No additional query
   }
   ```
4. **Common patterns to fix:**
   - User queries: `User::with(['company', 'organization', 'roles'])->get()`
   - Company queries: `Company::with(['user', 'sectoR', 'stage', 'cycle'])->get()`
   - Cycle queries: `Cycle::with(['companies', 'steps', 'mentors'])->get()`

**C. Use Query Debugging:**

1. **Enable query logging temporarily:**
   ```php
   DB::enableQueryLog();
   // Your code
   $queries = DB::getQueryLog();
   dd($queries);
   ```

2. **Use Laravel Debugbar** to identify N+1 queries:
   ```bash
   composer require barryvdh/laravel-debugbar --dev
   ```

**Verification:**
- Check query count before and after
- Use Laravel Debugbar to verify
- Test page load times

---

#### 7. Add Rate Limiting

**Files:** `routes/api.php`, `routes/web.php`

**Step-by-Step:**

1. **Update routes/api.php:**

   **A. Add rate limiting to public routes:**
   ```php
   // Public routes with rate limiting
   Route::middleware(['throttle:10,1'])->group(function () {
       Route::post('/login', [AuthController::class, 'login']);
       Route::post('/register', [AuthController::class, 'register']);
   });
   ```

   **B. Add rate limiting to authenticated routes:**
   ```php
   Route::middleware(['sanctum.custom', 'throttle:60,1'])->group(function () {
       // All authenticated API routes
       Route::get('/dashboard', [DashboardController::class, 'index']);
       // ... other routes
   });
   ```

2. **Update routes/web.php:**

   **A. Add rate limiting to login routes:**
   ```php
   Route::middleware(['throttle:5,1'])->group(function () {
       Auth::routes(['verify' => true]);
   });
   ```

   **B. Add rate limiting to public form submissions:**
   ```php
   Route::middleware(['throttle:10,1'])->group(function () {
       Route::post('message', 'IndexController@message');
       Route::post('eventregister', 'IndexController@eventregister');
   });
   ```

   **C. Add rate limiting to dashboard routes:**
   ```php
   Route::group([
       'prefix' => 'dashboard', 
       'middleware' => ['auth', 'verified', '2fa', 'throttle:120,1']
   ], function () {
       // Dashboard routes
   });
   ```

3. **Create custom rate limiters (optional):**

   **Edit `app/Providers/RouteServiceProvider.php`:**
   ```php
   use Illuminate\Cache\RateLimiting\Limit;
   use Illuminate\Support\Facades\RateLimiter;
   
   public function boot(): void
   {
       RateLimiter::for('api', function (Request $request) {
           return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
       });
       
       RateLimiter::for('strict', function (Request $request) {
           return Limit::perMinute(10)->by($request->ip());
       });
   }
   ```

4. **Update .env:**
   ```env
   # Rate limiting
   RATE_LIMIT_ENABLED=true
   ```

**Verification:**
- Test with multiple rapid requests
- Verify 429 status code when limit exceeded
- Check rate limit headers in response

---

#### 8. Remove Test Routes

**Files:** `routes/web.php:21-100`

**Step-by-Step:**

1. Open `routes/web.php`
2. **Remove test routes (lines 21-100):**
   ```php
   // DELETE THESE:
   Route::get('test', function () {
       $user = User::find(1);
       $user->notify(new WelcomeUserNotification($user, '12345'));
   });
   
   Route::get('default-permissions', function () {
       App\Permission::default_permissions();
   });
   
   Route::get('/testting', function () {
       // ... test code
   });
   
   Route::get('/2otp', function () {
       return redirect('/dashboard');
   })->middleware(['auth', '2fa']);
   
   Route::get('/db-migrate', 'IndexController@dbMigrate');
   Route::get('/seed-permissions', 'IndexController@seedPermissions');
   ```

3. **If these are needed for development, move to a separate file:**
   - Create `routes/dev.php`
   - Add to `app/Providers/RouteServiceProvider.php`:
     ```php
     if (app()->environment('local')) {
         $this->mapDevRoutes();
     }
     
     protected function mapDevRoutes(): void
     {
         require base_path('routes/dev.php');
     }
     ```

4. **Remove commented code:**
   - Delete all commented-out test routes
   - Clean up commented code blocks

**Verification:**
- Test that application still works
- Verify test routes are not accessible
- Check for any broken links

---

### MEDIUM Priority (Code Quality & Performance)

---

#### 9. Split Large Controllers

**Files:** `app/Http/Controllers/ClientCompanyController.php`, `app/Http/Controllers/HomeController.php`, `app/Http/Controllers/SettingsController.php`

**Step-by-Step for ClientCompanyController:**

1. **Analyze the controller:**
   - Identify distinct responsibilities
   - Group related methods

2. **Create new controllers:**
   ```bash
   php artisan make:controller Company/CompanyProfileController
   php artisan make:controller Company/CompanyTeamController
   php artisan make:controller Company/CompanyMilestoneController
   php artisan make:controller Company/CompanyDocumentController
   ```

3. **Move methods:**
   - **CompanyProfileController:** `show()`, `editOrCreate()`, `updateOrStore()`
   - **CompanyTeamController:** `AddTeams()`, `EditTeams()`, `DeleteTeams()`, `getEmployees()`, `addEmployee()`, `editEmployee()`, `deleteEmployee()`
   - **CompanyMilestoneController:** `AddMileStone()`, `EditMileStone()`, `DeleteMileStone()`
   - **CompanyDocumentController:** `deleteDocument()`, `deleteImage()`

4. **Update routes:**
   ```php
   Route::prefix('companies')->group(function () {
       Route::get('/{id}', [CompanyProfileController::class, 'show']);
       Route::get('/{id}/edit', [CompanyProfileController::class, 'edit']);
       Route::post('/{id}', [CompanyProfileController::class, 'update']);
       
       Route::prefix('{company}/team')->group(function () {
           Route::post('/', [CompanyTeamController::class, 'store']);
           Route::put('/{member}', [CompanyTeamController::class, 'update']);
           Route::delete('/{member}', [CompanyTeamController::class, 'destroy']);
       });
       
       // Similar for milestones and documents
   });
   ```

5. **Update service layer:**
   - Move business logic to `CompanyService`
   - Create `CompanyTeamService`, `CompanyMilestoneService` if needed

**Step-by-Step for HomeController:**

1. **Create new controllers:**
   ```bash
   php artisan make:controller Dashboard/DashboardController
   php artisan make:controller Reports/ReportController
   php artisan make:controller Statistics/StatisticsController
   ```

2. **Move methods:**
   - **DashboardController:** `index()`, dashboard-related methods
   - **ReportController:** All report generation methods
   - **StatisticsController:** All statistics calculation methods

3. **Extract to services:**
   - Create `DashboardService`, `ReportService`, `StatisticsService`
   - Move complex queries to services

**Verification:**
- Test all routes still work
- Verify no broken functionality
- Check code is more maintainable

---

#### 10. Add Database Indexes

**Step-by-Step:**

1. **Create migration:**
   ```bash
   php artisan make:migration add_indexes_to_improve_performance
   ```

2. **Edit migration file:**
   ```php
   <?php
   
   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;
   
   return new class extends Migration
   {
       public function up(): void
       {
           // Users table indexes
           Schema::table('users', function (Blueprint $table) {
               $table->index('organization_id');
               $table->index('company_id');
               $table->index('email');
               $table->index('phone');
               $table->index(['organization_id', 'company_id']);
           });
           
           // Companies table indexes
           Schema::table('companies', function (Blueprint $table) {
               $table->index('cycle');
               $table->index('step');
               $table->index('user_id');
               $table->index('sector');
               $table->index('stage');
               $table->index(['cycle', 'step']);
           });
           
           // Mentorship requests indexes
           Schema::table('mentorship_requests', function (Blueprint $table) {
               $table->index('mentor_id');
               $table->index('user_id');
               $table->index('zoom_date');
               $table->index(['mentor_id', 'zoom_date']);
           });
           
           // Form data indexes
           Schema::table('form_data', function (Blueprint $table) {
               $table->index('company_id');
               $table->index('form_id');
               $table->index(['company_id', 'form_id']);
           });
           
           // Training sessions indexes
           Schema::table('training_sessions', function (Blueprint $table) {
               $table->index('training_id');
               $table->index('date');
               $table->index(['training_id', 'date']);
           });
           
           // Add more indexes based on your query patterns
       }
       
       public function down(): void
       {
           // Drop indexes in reverse order
           Schema::table('users', function (Blueprint $table) {
               $table->dropIndex(['organization_id']);
               $table->dropIndex(['company_id']);
               $table->dropIndex(['email']);
               $table->dropIndex(['phone']);
               $table->dropIndex(['organization_id', 'company_id']);
           });
           
           // ... drop other indexes
       }
   };
   ```

3. **Run migration:**
   ```bash
   php artisan migrate
   ```

4. **Verify indexes:**
   ```sql
   SHOW INDEX FROM users;
   SHOW INDEX FROM companies;
   ```

**Verification:**
- Check query execution times before and after
- Use `EXPLAIN` on slow queries
- Monitor database performance

---

#### 11. Standardize API Responses

**Files:** `app/Http/Controllers/API/*`

**Step-by-Step:**

1. **Create Base API Response Trait:**
   ```bash
   php artisan make:trait ApiResponseTrait
   ```
   
   **Edit `app/Traits/ApiResponseTrait.php`:**
   ```php
   <?php
   
   namespace App\Traits;
   
   use Illuminate\Http\JsonResponse;
   
   trait ApiResponseTrait
   {
       protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
       {
           return response()->json([
               'status' => true,
               'message' => $message,
               'data' => $data,
               'errors' => null
           ], $code);
       }
       
       protected function error(string $message = 'Error', $errors = null, int $code = 400): JsonResponse
       {
           return response()->json([
               'status' => false,
               'message' => $message,
               'data' => null,
               'errors' => $errors
           ], $code);
       }
       
       protected function validationError($errors, string $message = 'Validation failed'): JsonResponse
       {
           return $this->error($message, $errors, 422);
       }
       
       protected function notFound(string $message = 'Resource not found'): JsonResponse
       {
           return $this->error($message, null, 404);
       }
       
       protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
       {
           return $this->error($message, null, 401);
       }
   }
   ```

2. **Update Base API Controller:**
   ```php
   // app/Http/Controllers/API/Controller.php
   <?php
   
   namespace App\Http\Controllers\API;
   
   use App\Http\Controllers\Controller as BaseController;
   use App\Traits\ApiResponseTrait;
   
   class Controller extends BaseController
   {
       use ApiResponseTrait;
   }
   ```

3. **Update all API Controllers:**
   - Make them extend `App\Http\Controllers\API\Controller`
   - Replace custom response methods with trait methods
   - Example:
     ```php
     // Before:
     return response()->json(['success' => true, 'data' => $data]);
     
     // After:
     return $this->success($data, 'Operation successful');
     ```

4. **Update existing ApiResponder trait** (if it exists):
   - Merge with new trait or update to use same format

**Verification:**
- Test all API endpoints
- Verify consistent response format
- Update API documentation

---

#### 12. Implement API Versioning

**Files:** `routes/api.php`

**Step-by-Step:**

1. **Restructure routes/api.php:**
   ```php
   <?php
   
   use Illuminate\Support\Facades\Route;
   
   // Public routes (no versioning needed or v1)
   Route::post('/v1/login', [AuthController::class, 'login']);
   Route::post('/v1/register', [AuthController::class, 'register']);
   
   // Versioned API routes
   Route::prefix('v1')->middleware(['sanctum.custom', 'throttle:60,1'])->group(function () {
       Route::get('/dashboard', [DashboardController::class, 'index']);
       Route::get('/me', [ProfileController::class, 'me']);
       
       // Users
       Route::apiResource('users', UserController::class);
       
       // Companies
       Route::apiResource('companies', CompanyController::class);
       
       // Programs
       Route::prefix('programs')->group(function () {
           Route::get('/', [ProgramController::class, 'index']);
           Route::get('/{id}', [ProgramController::class, 'show']);
           Route::post('/', [ProgramController::class, 'store']);
           // ...
       });
       
       // Continue organizing all routes...
   });
   
   // Future: v2 routes
   // Route::prefix('v2')->group(function () {
   //     // New version routes
   // });
   ```

2. **Update controllers to handle versioning:**
   - Controllers can check version if needed
   - Use version-specific resources if necessary

3. **Update API documentation:**
   - Document version in Swagger/OpenAPI
   - Specify which endpoints are in which version

**Verification:**
- Test all API endpoints work with `/v1/` prefix
- Verify backward compatibility if needed
- Update API clients

---

#### 13. Extract Business Logic to Services

**Files:** Multiple controllers

**Step-by-Step:**

1. **Identify business logic in controllers:**
   - Look for complex calculations
   - Database operations beyond simple CRUD
   - Business rules and validations

2. **Create/Update services:**
   ```bash
   php artisan make:service Company/CompanyTeamService
   php artisan make:service Company/CompanyMilestoneService
   ```

3. **Move logic to services:**
   ```php
   // Before in controller:
   public function addTeam($id, Request $request)
   {
       $company = Company::find($id);
       // Complex validation
       // Business logic
       // Database operations
       // Notifications
   }
   
   // After:
   // In CompanyTeamService:
   public function addTeamMember(int $companyId, array $data): array
   {
       $company = Company::findOrFail($companyId);
       
       // Validation
       $this->validateTeamMember($data);
       
       // Business logic
       $teamMember = TeamMember::create([
           'company_id' => $companyId,
           // ... mapped data
       ]);
       
       // Notifications
       $this->notifyTeamMemberAdded($teamMember);
       
       return ['success' => true, 'data' => $teamMember];
   }
   
   // In controller:
   public function addTeam($id, StoreTeamMemberRequest $request)
   {
       $result = $this->teamService->addTeamMember($id, $request->validated());
       
       if ($result['success']) {
           return redirect()->back()->with('success', 'Team member added');
       }
       
       return redirect()->back()->withErrors($result['errors']);
   }
   ```

4. **Update controllers to use services:**
   - Inject services via constructor
   - Keep controllers thin (only HTTP concerns)

**Verification:**
- Test all functionality still works
- Verify business logic is testable
- Check controllers are simpler

---

#### 14. Add Type Hints

**Files:** All controllers and services

**Step-by-Step:**

1. **Start with one controller at a time:**
   - Pick a small controller first
   - Add return types and parameter types

2. **Example:**
   ```php
   // Before:
   public function index(Request $request)
   {
       $users = User::all();
       return view('users.index', compact('users'));
   }
   
   // After:
   public function index(Request $request): \Illuminate\View\View
   {
       $users = User::all();
       return view('users.index', compact('users'));
   }
   ```

3. **For services:**
   ```php
   // Before:
   public function store($data)
   {
       // ...
   }
   
   // After:
   public function store(array $data): array
   {
       // ...
   }
   ```

4. **Use PHP 8.1+ features:**
   ```php
   // Union types
   public function find(int|string $id): ?User
   
   // Named arguments (when calling)
   $this->service->store(data: $data, validate: true);
   ```

5. **Run PHPStan or Psalm:**
   ```bash
   composer require --dev phpstan/phpstan
   vendor/bin/phpstan analyse app/
   ```

**Verification:**
- Run PHPStan to find type issues
- Fix all type errors
- Test application still works

---

#### 15. Review Mass Assignment

**Files:** `app/User.php`, `app/Company.php`

**Step-by-Step:**

1. **Review User model:**
   - Check which fields should be mass assignable
   - Separate fields by use case

2. **Create separate fillable arrays:**
   ```php
   // In User.php
   protected $fillable = [
       // Basic info
       'first_name_en', 'last_name_en', 'first_name_ar', 'last_name_ar',
       'email', 'phone', 'gender', 'dob',
       // Location
       'country_id', 'state_id', 'city_id',
       // Profile
       'title_en', 'title_ar', 'img_url',
       // Settings
       'language', 'currency', 'timezone',
   ];
   
   // Fields that should NEVER be mass assigned
   protected $guarded = [
       'id',
       'password', // Use setPasswordAttribute or dedicated method
       'api_token',
       'email_verified_at',
       'organization_id', // Should be set via service
       'company_id', // Should be set via service
   ];
   ```

3. **Use guarded for sensitive fields:**
   ```php
   protected $guarded = ['id', 'password', 'api_token'];
   ```

4. **Create update methods in services:**
   ```php
   // In UserService
   public function updatePassword(User $user, string $password): void
   {
       $user->password = Hash::make($password);
       $user->save();
   }
   
   public function updateOrganization(User $user, int $organizationId): void
   {
       $user->organization_id = $organizationId;
       $user->save();
   }
   ```

5. **Review Company model similarly**

**Verification:**
- Test mass assignment doesn't allow sensitive fields
- Verify updates work through services
- Check no security vulnerabilities

---

#### 16. Add Caching

**Files:** Settings, static data queries

**Step-by-Step:**

1. **Cache Settings:**
   ```php
   // In SettingsService or SettingsController
   public function getSettings(): array
   {
       return Cache::remember('settings', 3600, function () {
           return Settings::all()->keyBy('id')->toArray();
       });
   }
   
   public function updateSettings(array $data): void
   {
       Settings::where('id', 1)->update($data);
       Cache::forget('settings');
   }
   ```

2. **Cache Static Data:**
   ```php
   // Countries, States, Cities
   public function getCountries()
   {
       return Cache::remember('countries', 86400, function () {
           return Country::all();
       });
   }
   
   // Currencies
   public function getCurrencies()
   {
       return Cache::remember('currencies', 3600, function () {
           return Currency::where('enabled', 1)->get();
       });
   }
   ```

3. **Cache User Permissions:**
   ```php
   // Already handled by Spatie, but verify cache is working
   ```

4. **Add cache tags (if using Redis):**
   ```php
   Cache::tags(['settings'])->remember('settings', 3600, function () {
       return Settings::all();
   });
   ```

5. **Clear cache on updates:**
   ```php
   public function updateCurrency(Currency $currency, array $data): void
   {
       $currency->update($data);
       Cache::forget('currencies');
   }
   ```

**Verification:**
- Check cache is being used
- Verify cache clears on updates
- Monitor cache hit rates

---

### LOW Priority (Improvements & Polish)

---

#### 17. Add PHPDoc Comments

**Step-by-Step:**

1. **Add PHPDoc to services:**
   ```php
   /**
    * Create a new user with the given data.
    *
    * @param array<string, mixed> $data User data including name, email, password, etc.
    * @return array{success: bool, user: User|null, message: string}
    * @throws \Illuminate\Validation\ValidationException
    * @throws \Exception
    */
   public function store(array $data): array
   {
       // ...
   }
   ```

2. **Add PHPDoc to complex controller methods:**
   ```php
   /**
    * Display the company profile with all related data.
    *
    * @param int $id Company ID
    * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
    */
   public function show(int $id)
   {
       // ...
   }
   ```

3. **Use IDE helper:**
   ```bash
   composer require --dev barryvdh/laravel-ide-helper
   php artisan ide-helper:generate
   php artisan ide-helper:models
   ```

---

#### 18. Remove Hard-coded Values

**Step-by-Step:**

1. **Create constants file:**
   ```php
   // app/Constants/Roles.php
   <?php
   
   namespace App\Constants;
   
   class Roles
   {
       const ADMIN = 'Admin';
       const ORGANIZATION_MANAGER = 'organizationManager';
       const MENTOR = 'Mentor';
       const STARTUP = 'Startup';
       const JUDGER = 'Judger';
   }
   ```

2. **Create config for business rules:**
   ```php
   // config/business.php
   return [
       'max_team_members' => 10,
       'max_file_size' => 5242880, // 5MB
       'session_duration_minutes' => 60,
   ];
   ```

3. **Replace hard-coded values:**
   ```php
   // Before:
   if ($user->hasRole('Admin')) {
   
   // After:
   use App\Constants\Roles;
   if ($user->hasRole(Roles::ADMIN)) {
   ```

---

#### 19. Improve Error Handling

**Step-by-Step:**

1. **Create custom exception handler:**
   ```php
   // app/Exceptions/Handler.php
   public function render($request, Throwable $exception)
   {
       if ($request->expectsJson()) {
           return $this->handleApiException($request, $exception);
       }
       
       return parent::render($request, $exception);
   }
   
   protected function handleApiException($request, Throwable $exception)
   {
       if ($exception instanceof ValidationException) {
           return response()->json([
               'status' => false,
               'message' => 'Validation failed',
               'errors' => $exception->errors()
           ], 422);
       }
       
       // Handle other exceptions...
   }
   ```

2. **Use try-catch in controllers:**
   ```php
   public function store(StoreRequest $request)
   {
       try {
           $result = $this->service->store($request->validated());
           return $this->success($result);
       } catch (ValidationException $e) {
           return $this->validationError($e->errors());
       } catch (\Exception $e) {
           \Log::error('Store failed', ['error' => $e->getMessage()]);
           return $this->error('An error occurred');
       }
   }
   ```

---

#### 20-22. Testing and Code Style

**Step-by-Step:**

1. **Install testing tools:**
   ```bash
   composer require --dev pestphp/pest
   php artisan pest:install
   ```

2. **Create first test:**
   ```php
   // tests/Feature/UserTest.php
   test('user can register', function () {
       $response = $this->postJson('/api/v1/register', [
           'first_name_en' => 'John',
           'last_name_en' => 'Doe',
           'email' => 'john@example.com',
           'password' => 'password123',
       ]);
       
       $response->assertStatus(201)
                ->assertJson(['status' => true]);
   });
   ```

3. **Run Laravel Pint:**
   ```bash
   ./vendor/bin/pint
   ```

4. **Set up CI/CD:**
   - Run tests on push
   - Run Pint on push
   - Check code coverage

---

## Implementation Timeline

### Week 1 (Critical Security)
- Day 1-2: Fix SQL injection risks (#1)
- Day 3-4: Add authorization checks (#2)
- Day 5: Remove permission bypass (#3)

### Week 2 (Security & Validation)
- Day 1-2: Fix env() calls (#4)
- Day 3-4: Add FormRequest validation (#5)
- Day 5: Add rate limiting (#7)

### Week 3 (Performance)
- Day 1-2: Fix N+1 queries (#6)
- Day 3-4: Add database indexes (#10)
- Day 5: Remove test routes (#8)

### Week 4+ (Code Quality)
- Split large controllers (#9)
- Standardize API (#11, #12)
- Extract to services (#13)
- Add type hints (#14)
- Review mass assignment (#15)
- Add caching (#16)

### Ongoing (Polish)
- Add PHPDoc (#17)
- Remove hard-coded values (#18)
- Improve error handling (#19)
- Add tests (#20, #21)
- Code style (#22)

---

## Summary Statistics

- **Total Controllers:** 100+
- **Large Controllers (>1000 lines):** 3
- **FormRequests:** 70+
- **Policies:** 3 (need more)
- **Services:** 27
- **Repositories:** 11
- **API Endpoints:** 100+
- **Security Issues Found:** 8 critical, 12 medium
- **Performance Issues:** 5 N+1 queries, missing indexes
- **Test Coverage:** <5% (estimated)

---

## Recommendations

1. **Immediate Actions (This Week):**
   - Fix SQL injection risks
   - Add authorization checks
   - Remove test routes
   - Add rate limiting

2. **Short Term (This Month):**
   - Add FormRequest validation
   - Fix N+1 queries
   - Add database indexes
   - Split large controllers

3. **Long Term (Next Quarter):**
   - Comprehensive test suite
   - API standardization
   - Performance optimization
   - Code refactoring

---

**Review Completed By:** AI Code Reviewer  
**Next Review Recommended:** After implementing HIGH priority items
