# Customer-Side Feature Development Guide

**Date**: November 20, 2025  
**Version**: 1.0  
**Status**: ✅ Ready for Implementation

---

## Quick Start: Adding a New Customer Feature

### Template: Complete Feature Implementation

#### Step 1: Create Service Class

**File**: `app/Services/Customer/NewFeatureService.php`

```php
<?php

namespace App\Services\Customer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * New Feature Service
 * Handles [feature description]
 */
class NewFeatureService
{
    /**
     * Execute feature operation
     *
     * @param string $userId
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function execute(string $userId, array $data): array
    {
        // Validate input
        $validator = Validator::make($data, [
            'field1' => 'required|string|max:255',
            'field2' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();
        try {
            // Verify user exists and is active
            $user = DB::table('users')
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();

            if (!$user) {
                throw new \Exception('User not found or inactive');
            }

            // Execute business logic
            $result = $this->performOperation($userId, $data);

            // Log audit trail
            $this->logAudit($userId, 'feature_executed', 'table_name', $result['id'], [
                'field1' => $data['field1'],
                'field2' => $data['field2'],
            ]);

            DB::commit();

            Log::info('Feature executed successfully', [
                'user_id' => $userId,
                'result_id' => $result['id'],
            ]);

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Feature execution failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Perform the actual operation
     *
     * @param string $userId
     * @param array $data
     * @return array
     */
    private function performOperation(string $userId, array $data): array
    {
        $id = \Illuminate\Support\Str::uuid()->toString();

        // Insert or update database record
        DB::table('table_name')->insert([
            'id' => $id,
            'user_id' => $userId,
            'field1' => $data['field1'],
            'field2' => $data['field2'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return [
            'id' => $id,
            'message' => 'Operation completed successfully',
        ];
    }

    /**
     * Log audit trail
     *
     * @param string $userId
     * @param string $action
     * @param string $tableName
     * @param string $recordId
     * @param array $changes
     * @return void
     */
    private function logAudit(
        string $userId,
        string $action,
        string $tableName,
        string $recordId,
        array $changes = []
    ): void {
        try {
            DB::table('audit_logs')->insert([
                'log_id' => \Illuminate\Support\Str::uuid()->toString(),
                'user_id' => $userId,
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'changes' => json_encode($changes),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging audit', ['error' => $e->getMessage()]);
        }
    }
}
```

#### Step 2: Create Controller Method

**File**: `app/Http/Controllers/CustomerController.php`

```php
/**
 * Handle new feature request
 */
public function handleNewFeature(Request $request)
{
    try {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        // Validate request
        $validated = $request->validate([
            'field1' => 'required|string|max:255',
            'field2' => 'required|integer|min:1',
        ]);

        // Execute service
        $service = new \App\Services\Customer\NewFeatureService();
        $result = $service->execute($userId, $validated);

        // Return response
        return response()->json($result, 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('Validation failed', [
            'errors' => $e->errors(),
            'ip' => request()->ip(),
        ]);
        return back()->withErrors($e->errors())->withInput();

    } catch (\Exception $e) {
        Log::error('Feature error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'An error occurred. Please try again.');
    }
}
```

#### Step 3: Create API Controller Method

**File**: `app/Http/Controllers/Api/CustomerDashboardApiController.php`

```php
/**
 * API endpoint for new feature
 */
public function newFeature(Request $request)
{
    try {
        $userId = session('user_id');

        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'field1' => 'required|string|max:255',
            'field2' => 'required|integer|min:1',
        ]);

        $service = new \App\Services\Customer\NewFeatureService();
        $result = $service->execute($userId, $validated);

        Log::info('API: New feature executed', [
            'user_id' => $userId,
            'result_id' => $result['id'],
        ]);

        return response()->json($result, 201);

    } catch (\Exception $e) {
        Log::error('API: Feature error', [
            'user_id' => $userId ?? null,
            'error' => $e->getMessage(),
        ]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
```

#### Step 4: Add Routes

**File**: `routes/web.php`

```php
// Web route
Route::middleware([\App\Http\Middleware\CheckAuth::class, \App\Http\Middleware\SessionTimeout::class])->group(function () {
    Route::post('/customer/new-feature', [CustomerController::class, 'handleNewFeature'])
        ->name('customer.new-feature');
});
```

**File**: `routes/api.php`

```php
// API route
Route::prefix('api/customer')->middleware([\App\Http\Middleware\CheckAuth::class, \App\Http\Middleware\CheckRole::class.':customer'])->group(function () {
    Route::post('/new-feature', [CustomerDashboardApiController::class, 'newFeature'])
        ->name('api.customer.new-feature');
});
```

#### Step 5: Create Tests

**File**: `tests/Feature/Customer/NewFeatureTest.php`

```php
<?php

namespace Tests\Feature\Customer;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewFeatureTest extends TestCase
{
    private $userId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->userId = Str::uuid();
        DB::table('users')->insert([
            'user_id' => $this->userId,
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'position' => 'Customer',
            'department' => 'External',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Set session
        session(['user_id' => $this->userId]);
    }

    public function test_customer_can_execute_feature()
    {
        $response = $this->post('/customer/new-feature', [
            'field1' => 'test value',
            'field2' => 10,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'message']);
    }

    public function test_feature_requires_authentication()
    {
        session()->flush();

        $response = $this->post('/customer/new-feature', [
            'field1' => 'test value',
            'field2' => 10,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_feature_validates_input()
    {
        $response = $this->post('/customer/new-feature', [
            'field1' => '',  // Required
            'field2' => 0,   // Min 1
        ]);

        $response->assertSessionHasErrors(['field1', 'field2']);
    }

    public function test_api_endpoint_returns_json()
    {
        $response = $this->postJson('/api/customer/new-feature', [
            'field1' => 'test value',
            'field2' => 10,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'Operation completed successfully']);
    }

    public function test_audit_log_created()
    {
        $this->post('/customer/new-feature', [
            'field1' => 'test value',
            'field2' => 10,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->userId,
            'action' => 'feature_executed',
        ]);
    }
}
```

**File**: `tests/Unit/Services/Customer/NewFeatureServiceTest.php`

```php
<?php

namespace Tests\Unit\Services\Customer;

use Tests\TestCase;
use App\Services\Customer\NewFeatureService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewFeatureServiceTest extends TestCase
{
    private $service;
    private $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new NewFeatureService();
        $this->userId = Str::uuid();

        // Create test user
        DB::table('users')->insert([
            'user_id' => $this->userId,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'position' => 'Customer',
            'department' => 'External',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_service_executes_successfully()
    {
        $result = $this->service->execute($this->userId, [
            'field1' => 'test value',
            'field2' => 10,
        ]);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_service_validates_input()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->execute($this->userId, [
            'field1' => '',  // Required
            'field2' => 0,   // Min 1
        ]);
    }

    public function test_service_checks_user_exists()
    {
        $this->expectException(\Exception::class);

        $this->service->execute(Str::uuid(), [
            'field1' => 'test value',
            'field2' => 10,
        ]);
    }
}
```

---

## Development Workflow

### 1. Feature Planning

**Checklist**:
- [ ] Define feature requirements
- [ ] Identify database tables needed
- [ ] Plan API endpoints
- [ ] Design authorization rules
- [ ] Create wireframes/mockups

### 2. Database Design

**Steps**:
1. Create migration file
2. Define table schema
3. Add indexes
4. Add foreign keys
5. Run migration

**Example Migration**:
```php
Schema::create('new_feature_table', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id')->index();
    $table->string('field1');
    $table->integer('field2');
    $table->timestamps();

    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
});
```

### 3. Service Implementation

**Best Practices**:
- ✅ Encapsulate business logic
- ✅ Validate input
- ✅ Handle transactions
- ✅ Log operations
- ✅ Throw meaningful exceptions
- ✅ Verify user ownership

### 4. Controller Implementation

**Best Practices**:
- ✅ Delegate to service
- ✅ Handle validation
- ✅ Return appropriate responses
- ✅ Log errors
- ✅ Redirect on success

### 5. Route Registration

**Best Practices**:
- ✅ Use resource routes when applicable
- ✅ Apply appropriate middleware
- ✅ Use route names
- ✅ Group related routes

### 6. Testing

**Coverage Goals**:
- ✅ Happy path (success case)
- ✅ Validation failures
- ✅ Authorization failures
- ✅ Edge cases
- ✅ Error handling

**Target**: 90%+ code coverage

### 7. Documentation

**Required**:
- [ ] API endpoint documentation
- [ ] Parameter descriptions
- [ ] Response examples
- [ ] Error codes
- [ ] Usage examples

---

## Code Quality Standards

### Static Analysis

**Run Before Commit**:
```bash
# PHP CodeSniffer
./vendor/bin/phpcs app/

# PHPStan
./vendor/bin/phpstan analyse app/

# PHP Mess Detector
./vendor/bin/phpmd app/ text cleancode,codesize,controversial,design,naming,unusedcode
```

### Testing

**Run Before Commit**:
```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/Customer/NewFeatureTest.php
```

### Code Style

**PSR-12 Standards**:
- ✅ 4-space indentation
- ✅ Proper spacing around operators
- ✅ Consistent naming conventions
- ✅ Proper comment formatting

**Auto-format**:
```bash
./vendor/bin/php-cs-fixer fix app/
```

---

## Security Checklist

- [ ] Input validation on all endpoints
- [ ] User ownership verification
- [ ] Authorization checks
- [ ] SQL injection prevention (use parameterized queries)
- [ ] CSRF token validation
- [ ] Rate limiting on sensitive endpoints
- [ ] Audit logging for all operations
- [ ] Error messages don't leak sensitive info
- [ ] File uploads validated (type, size)
- [ ] Sensitive data not logged

---

## Performance Optimization

### Database

- [ ] Use indexes on frequently queried columns
- [ ] Avoid N+1 queries (use eager loading)
- [ ] Use pagination for large result sets
- [ ] Optimize joins
- [ ] Use aggregation queries when appropriate

### Caching

- [ ] Cache frequently accessed data
- [ ] Set appropriate TTL
- [ ] Invalidate cache on updates
- [ ] Monitor cache hit rate

### API

- [ ] Return only necessary fields
- [ ] Implement pagination
- [ ] Use compression
- [ ] Implement rate limiting
- [ ] Monitor response times

---

## Accessibility Standards (WCAG 2.1 AA)

### Forms

- [ ] All inputs have labels
- [ ] Error messages are clear
- [ ] Required fields are marked
- [ ] Form validation is accessible
- [ ] Keyboard navigation works

### Navigation

- [ ] Logical tab order
- [ ] Skip links for navigation
- [ ] Clear focus indicators
- [ ] Consistent navigation

### Content

- [ ] Sufficient color contrast (4.5:1 for text)
- [ ] Text alternatives for images
- [ ] Descriptive link text
- [ ] Proper heading hierarchy
- [ ] Readable font sizes (minimum 12px)

### Interactive Elements

- [ ] Buttons are keyboard accessible
- [ ] Modals are properly labeled
- [ ] Alerts are announced to screen readers
- [ ] Loading states are communicated

---

## Deployment Checklist

### Pre-Deployment

- [ ] All tests passing
- [ ] Code review approved
- [ ] Static analysis passing
- [ ] Performance benchmarks acceptable
- [ ] Security audit completed
- [ ] Database migrations tested
- [ ] Rollback plan prepared

### Deployment

- [ ] Backup database
- [ ] Run migrations
- [ ] Clear caches
- [ ] Deploy code
- [ ] Verify deployment
- [ ] Monitor logs

### Post-Deployment

- [ ] Monitor error logs
- [ ] Check performance metrics
- [ ] Verify functionality
- [ ] Gather user feedback
- [ ] Document any issues

---

## Common Patterns & Examples

### Pattern 1: List with Filtering

```php
public function getFilteredItems(string $userId, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
{
    $query = DB::table('items')
        ->where('user_id', $userId);

    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    if (!empty($filters['search'])) {
        $query->where('name', 'LIKE', '%' . $filters['search'] . '%');
    }

    if (!empty($filters['date_from'])) {
        $query->where('created_at', '>=', $filters['date_from']);
    }

    return $query->orderBy('created_at', 'desc')->paginate(10);
}
```

### Pattern 2: Create with Validation

```php
public function create(string $userId, array $data): string
{
    $validator = Validator::make($data, [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:2000',
    ]);

    if ($validator->fails()) {
        throw new ValidationException($validator);
    }

    $id = Str::uuid()->toString();

    DB::table('items')->insert([
        'id' => $id,
        'user_id' => $userId,
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}
```

### Pattern 3: Update with Ownership Check

```php
public function update(string $userId, string $itemId, array $data): bool
{
    $item = DB::table('items')
        ->where('id', $itemId)
        ->where('user_id', $userId)
        ->first();

    if (!$item) {
        throw new \Exception('Item not found or access denied');
    }

    DB::table('items')
        ->where('id', $itemId)
        ->update([
            'name' => $data['name'] ?? $item->name,
            'description' => $data['description'] ?? $item->description,
            'updated_at' => now(),
        ]);

    return true;
}
```

### Pattern 4: Delete with Verification

```php
public function delete(string $userId, string $itemId): bool
{
    DB::beginTransaction();
    try {
        $item = DB::table('items')
            ->where('id', $itemId)
            ->where('user_id', $userId)
            ->first();

        if (!$item) {
            throw new \Exception('Item not found or access denied');
        }

        DB::table('items')->where('id', $itemId)->delete();

        $this->logAudit($userId, 'item_deleted', 'items', $itemId, [
            'name' => $item->name,
        ]);

        DB::commit();
        return true;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

---

## Troubleshooting Guide

### Issue: 419 Page Expired Error

**Solution**:
- Verify CSRF token in form: `@csrf`
- Check session configuration
- Clear browser cache
- Regenerate session token

### Issue: 403 Unauthorized Error

**Solution**:
- Verify user role
- Check authorization policy
- Verify route middleware
- Check user permissions

### Issue: N+1 Query Problem

**Solution**:
- Use eager loading: `with(['relationship'])`
- Use joins for related data
- Use aggregation queries
- Implement query caching

### Issue: Slow API Response

**Solution**:
- Check database indexes
- Use pagination
- Implement caching
- Optimize queries
- Monitor performance

---

## Resources & References

### Documentation
- [Laravel Documentation](https://laravel.com/docs)
- [PHP Standards](https://www.php-fig.org/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

### Tools
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)
- [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [PHPStan](https://phpstan.org/)

---

**Version**: 1.0  
**Last Updated**: November 20, 2025  
**Status**: ✅ Complete and Ready for Use
