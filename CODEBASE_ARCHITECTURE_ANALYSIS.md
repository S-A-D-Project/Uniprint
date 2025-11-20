# Uniprint Codebase Architecture Analysis

**Date**: November 20, 2025  
**Status**: ✅ Complete Analysis  
**Scope**: Customer-side architecture, patterns, and integration guidelines

---

## Executive Summary

The Uniprint codebase follows a **Service-Oriented Architecture (SOA)** with clear separation of concerns. The customer-side implementation demonstrates:

- ✅ **Layered Architecture**: Controllers → Services → Database
- ✅ **Multi-tenancy**: Enterprise and customer isolation via scopes
- ✅ **RESTful APIs**: Consistent API patterns for frontend integration
- ✅ **Service Layer Pattern**: Business logic encapsulated in services
- ✅ **Policy-Based Authorization**: Fine-grained access control
- ✅ **Audit Logging**: Comprehensive tracking of all operations
- ✅ **Error Handling**: Consistent exception handling and logging

---

## Architecture Overview

### 1. Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── CustomerController.php          (Customer views & actions)
│   │   ├── CustomerDashboardController.php (Dashboard logic)
│   │   └── Api/
│   │       └── CustomerDashboardApiController.php (API endpoints)
│   ├── Middleware/
│   │   ├── CheckAuth.php                   (Authentication)
│   │   ├── CheckRole.php                   (Role-based access)
│   │   ├── SessionTimeout.php              (Session management)
│   │   └── EnsureTenantIsolation.php       (Multi-tenancy)
│   └── Requests/                           (Form validation)
├── Models/
│   ├── CustomerOrder.php                   (Order model)
│   ├── Service.php                         (Service/Product model)
│   ├── Enterprise.php                      (Business entity)
│   └── User.php                            (User model)
├── Services/
│   └── Customer/
│       ├── CustomerOrderService.php        (Order management)
│       ├── CustomerDesignFileService.php   (File handling)
│       └── CustomerDashboardService.php    (Analytics)
├── Scopes/
│   └── CustomerTenantScope.php             (Data isolation)
└── Policies/
    └── OrderPolicy.php                     (Authorization rules)

routes/
├── web.php                                 (Web routes)
└── api.php                                 (API routes)

resources/
└── views/
    └── customer/                           (Customer templates)
        ├── dashboard.blade.php
        ├── orders.blade.php
        ├── order-details.blade.php
        └── ...
```

---

## Core Components Analysis

### 1. Controllers Layer

#### **CustomerController** (`app/Http/Controllers/CustomerController.php`)

**Purpose**: Main controller for customer-facing operations

**Key Methods**:
- `dashboard()` - Display customer dashboard with stats
- `orders()` - List customer orders with pagination
- `orderDetails($id)` - Show detailed order information
- `uploadDesignFile()` - Handle file uploads
- `deleteDesignFile()` - Remove design files
- `enterpriseServices($id)` - Browse services by enterprise
- `serviceDetails($id)` - View service details
- `placeOrder()` - Create new order
- `notifications()` - List notifications
- `markNotificationRead()` - Mark notification as read

**Patterns Used**:
- Session-based user identification
- Direct database queries (mixed with service layer)
- File storage integration
- Notification system

**Technical Debt**:
- ⚠️ Mixed concerns: Some business logic in controller
- ⚠️ Direct DB queries instead of service layer
- ⚠️ File handling could be abstracted

#### **CustomerDashboardController** (`app/Http/Controllers/CustomerDashboardController.php`)

**Purpose**: Dedicated dashboard controller with service integration

**Key Methods**:
- `index()` - Load dashboard with comprehensive stats
- `orders()` - Filter and paginate orders
- `savedServices()` - Display saved services

**Patterns Used**:
- Service layer integration
- Comprehensive error handling
- Query exception handling
- Logging for debugging

**Best Practices**:
- ✅ Uses CustomerDashboardService
- ✅ Proper error handling
- ✅ Detailed logging
- ✅ Clear separation of concerns

#### **CustomerDashboardApiController** (`app/Http/Controllers/Api/CustomerDashboardApiController.php`)

**Purpose**: RESTful API endpoints for dashboard data

**Key Methods**:
- `getServices()` - Fetch services with filtering
- `getOrders()` - Retrieve customer orders
- `getPaymentHistory()` - Payment records
- `updateProfile()` - Update user profile
- `getDashboardStats()` - Dashboard statistics
- `getSavedServices()` - Saved services list

**Patterns Used**:
- JSON responses
- Query filtering and sorting
- Pagination
- Comprehensive logging

**Features**:
- Search functionality
- Category filtering
- Price range filtering
- Sort options (price, newest, popular)

---

### 2. Service Layer

#### **CustomerOrderService** (`app/Services/Customer/CustomerOrderService.php`)

**Purpose**: Encapsulates all order-related business logic

**Key Methods**:
```php
// Order retrieval
getOrders(userId, filters, perPage)
getOrderDetails(userId, orderId)

// Order operations
createOrder(userId, orderData)
cancelOrder(userId, orderId, reason)

// Internal helpers
getOrderItems(orderId)
getStatusHistory(orderId)
getCurrentStatus(orderId)
getTransactions(orderId)
getDesignFiles(orderId)
calculateOrderTotals(items)
createOrderItem(orderId, itemData)
createInitialStatus(orderId, userId)
notifyBusiness(enterpriseId, orderId, orderNo, type)
generateOrderNumber()
logAudit(userId, action, tableName, recordId, changes)
```

**Architecture Patterns**:
- ✅ Transaction management (DB::beginTransaction/commit/rollback)
- ✅ Validation before operations
- ✅ Comprehensive error handling
- ✅ Audit logging for all operations
- ✅ Business notifications
- ✅ Order number generation

**Data Isolation**:
- Verifies user ownership before operations
- Prevents unauthorized access
- Validates enterprise associations

#### **CustomerDesignFileService** (`app/Services/Customer/CustomerDesignFileService.php`)

**Purpose**: Manages design file uploads and versioning

**Key Methods**:
```php
uploadFile(userId, orderId, fileData)
deleteFile(userId, orderId, fileId)
getOrderFiles(userId, orderId)
getFileDetails(userId, fileId)
getFileDownloadUrl(userId, fileId)
```

**Features**:
- File type validation (jpg, jpeg, png, pdf, ai, psd, eps, svg)
- File size limits (50MB max)
- Version tracking
- Approval workflow
- Storage management
- Audit logging
- Business notifications

**Security**:
- ✅ Ownership verification
- ✅ File type validation
- ✅ Size restrictions
- ✅ Access control

#### **CustomerDashboardService** (`app/Services/Customer/CustomerDashboardService.php`)

**Purpose**: Aggregates dashboard statistics and analytics

**Key Methods**:
```php
getDashboardStats(userId, useCache)
getOrderStats(userId)
getAssetStats(userId)
getFinancialStats(userId)
getActivityStats(userId)
getRecentOrders(userId, limit)
clearCache(userId)
```

**Features**:
- Comprehensive statistics
- Caching (5-minute TTL)
- Financial analytics
- Activity tracking
- Recent order aggregation

**Performance**:
- ✅ Cache-based optimization
- ✅ Efficient queries with window functions
- ✅ Aggregation queries

---

### 3. Models Layer

#### **CustomerOrder** (`app/Models/CustomerOrder.php`)

**Structure**:
```php
protected $primaryKey = 'order_id';
protected $fillable = [
    'customer_account_id',
    'enterprise_id',
    'order_creation_date',
    'total_order_amount',
    'current_status',
];

// Relationships
customer()        // belongsTo User
enterprise()      // belongsTo Enterprise
orderItems()      // hasMany OrderItem
statusHistory()   // hasMany OrderStatusHistory
transactions()    // hasMany Transaction
```

**Patterns**:
- ✅ UUID primary keys
- ✅ Type casting for dates and decimals
- ✅ Relationship definitions
- ✅ Mass assignment protection

---

### 4. Authorization & Security

#### **OrderPolicy** (`app/Policies/OrderPolicy.php`)

**Authorization Rules**:
```php
viewAny()      // All authenticated users
view()         // Admin (all), Business (enterprise), Customer (own)
create()       // Customers only
update()       // Admin (all), Business (enterprise)
delete()       // Admin only
updateStatus() // Admin and Business users
cancel()       // Customer (pending only), Business, Admin
```

**Multi-Tenancy**:
- ✅ Enterprise-level isolation for business users
- ✅ Customer-level isolation for customers
- ✅ Admin override capability

#### **CustomerTenantScope** (`app/Scopes/CustomerTenantScope.php`)

**Purpose**: Automatic query filtering for data isolation

**Filtering Logic**:
```php
Admin users       → No filtering (see all)
Business users    → Filter by enterprise_id
Customers         → Filter by customer_account_id
```

**Implementation**:
- Global scope applied to models
- Macro methods for bypassing scope when needed
- Transparent to application code

---

### 5. Middleware Stack

#### **CheckAuth** - Session Validation
- Verifies user session exists
- Validates user in database
- Handles Laravel auth system integration
- Redirects to login if invalid

#### **CheckRole** - Role-Based Access
- Validates user role matches route requirement
- Returns 403 if unauthorized
- Supports role parameters

#### **SessionTimeout** - Session Management
- Detects session expiration
- Provides timeout warnings
- Graceful logout handling

#### **EnsureTenantIsolation** - Multi-Tenancy
- Validates tenant access
- Logs security events
- Prevents cross-tenant access

---

## Data Flow Patterns

### Order Creation Flow

```
1. Customer submits order form
   ↓
2. CustomerController::placeOrder()
   ↓
3. Validation (Request class)
   ↓
4. CustomerOrderService::createOrder()
   ├─ Generate order number
   ├─ Calculate totals
   ├─ Create order record
   ├─ Create order items
   ├─ Create initial status
   ├─ Notify business
   └─ Log audit
   ↓
5. Redirect to orders page
```

### Order Retrieval Flow

```
1. Customer requests orders
   ↓
2. CustomerDashboardController::orders()
   ↓
3. CustomerOrderService::getOrders()
   ├─ Apply tenant scope (automatic)
   ├─ Apply filters
   ├─ Join with status and enterprise
   └─ Paginate results
   ↓
4. Return to view
```

### Design File Upload Flow

```
1. Customer uploads file
   ↓
2. CustomerController::uploadDesignFile()
   ↓
3. File validation
   ↓
4. CustomerDesignFileService::uploadFile()
   ├─ Verify order ownership
   ├─ Check order status
   ├─ Get version number
   ├─ Store file
   ├─ Create database record
   ├─ Notify business
   └─ Log audit
   ↓
5. Return success message
```

---

## Database Schema Patterns

### Key Tables

**customer_orders**
```
purchase_order_id (UUID, PK)
customer_id (UUID, FK → users)
enterprise_id (UUID, FK → enterprises)
order_no (String, unique)
date_requested (Timestamp)
delivery_date (Date)
subtotal (Decimal)
discount (Decimal)
shipping_fee (Decimal)
total (Decimal)
created_at, updated_at
```

**order_items**
```
item_id (UUID, PK)
purchase_order_id (UUID, FK)
service_id (UUID, FK → services)
quantity (Integer)
price_snapshot (Decimal)
item_subtotal (Decimal)
notes_to_enterprise (Text)
```

**order_design_files**
```
file_id (UUID, PK)
purchase_order_id (UUID, FK)
uploaded_by (UUID, FK → users)
file_name (String)
file_path (String)
file_type (String)
file_size (Integer)
design_notes (Text)
version (Integer)
is_approved (Boolean)
created_at, updated_at
```

**order_status_history**
```
history_id (UUID, PK)
purchase_order_id (UUID, FK)
status_id (UUID, FK → statuses)
timestamp (Timestamp)
user_id (UUID, FK → users)
notes (Text)
```

---

## Coding Conventions & Patterns

### 1. Naming Conventions

**Controllers**:
- Singular noun: `CustomerController`, `OrderController`
- Suffix: `Controller`

**Services**:
- Singular noun: `CustomerOrderService`
- Suffix: `Service`
- Location: `app/Services/{Domain}/{Entity}Service.php`

**Models**:
- Singular noun: `CustomerOrder`, `Service`
- Location: `app/Models/`

**Database Tables**:
- Plural noun: `customer_orders`, `order_items`
- Snake_case: `order_design_files`

**Methods**:
- camelCase: `getOrders()`, `createOrder()`
- Verb-first: `get`, `create`, `update`, `delete`

### 2. Error Handling Pattern

```php
try {
    // Business logic
    DB::beginTransaction();
    
    // Operations
    
    DB::commit();
    Log::info('Success message', ['context' => $data]);
    
} catch (ValidationException $e) {
    // Handle validation errors
    Log::warning('Validation failed', ['errors' => $e->errors()]);
    throw $e;
    
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('Operation failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    throw $e;
}
```

### 3. Audit Logging Pattern

```php
private function logAudit(
    string $userId,
    string $action,
    string $tableName,
    string $recordId,
    array $changes = []
): void {
    DB::table('audit_logs')->insert([
        'log_id' => Str::uuid(),
        'user_id' => $userId,
        'action' => $action,
        'table_name' => $tableName,
        'record_id' => $recordId,
        'changes' => json_encode($changes),
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'timestamp' => now(),
        'created_at' => now(),
    ]);
}
```

### 4. Tenant Isolation Pattern

```php
// Automatic filtering via scope
$orders = CustomerOrder::where('status', 'pending')->get();
// Only returns orders for authenticated customer

// Explicit verification
$order = DB::table('customer_orders')
    ->where('purchase_order_id', $orderId)
    ->where('customer_id', $userId)  // Always verify ownership
    ->first();

if (!$order) {
    throw new \Exception('Order not found or access denied');
}
```

### 5. Validation Pattern

```php
$validator = Validator::make($data, [
    'email' => 'required|email|unique:users,email|max:255',
    'quantity' => 'required|integer|min:1|max:1000',
    'date' => 'required|date|after:today',
]);

if ($validator->fails()) {
    throw new ValidationException($validator);
}
```

---

## API Endpoints Pattern

### RESTful Conventions

**Resource URLs**:
```
GET    /api/customer/orders              → List orders
GET    /api/customer/orders/{id}         → Get order details
POST   /api/customer/orders              → Create order
PUT    /api/customer/orders/{id}         → Update order
DELETE /api/customer/orders/{id}         → Delete order

GET    /api/customer/services            → List services
GET    /api/customer/services/{id}       → Get service details

GET    /api/customer/dashboard-stats     → Get dashboard stats
```

**Response Format**:
```json
{
  "data": { /* resource data */ },
  "meta": {
    "current_page": 1,
    "total": 100,
    "per_page": 10
  }
}
```

**Error Format**:
```json
{
  "error": "Error message",
  "message": "Detailed error message",
  "status": 400
}
```

---

## Performance Considerations

### 1. Database Optimization

**Indexes**:
- ✅ Primary keys on all tables
- ✅ Foreign keys indexed
- ✅ Composite indexes on frequent queries

**Query Optimization**:
- ✅ Window functions for latest status
- ✅ Eager loading with joins
- ✅ Pagination for large result sets
- ✅ Select specific columns (not SELECT *)

### 2. Caching Strategy

**Dashboard Stats**:
- Cache TTL: 5 minutes
- Cache key: `customer_dashboard_stats_{userId}`
- Manual invalidation on order changes

**Service Catalog**:
- Implement caching for frequently accessed services
- Invalidate on service updates

### 3. N+1 Query Prevention

**Current Implementation**:
- ✅ Uses joins instead of multiple queries
- ✅ Eager loading relationships
- ✅ Aggregation queries for statistics

---

## Security Best Practices Implemented

✅ **Authentication**: Session-based with middleware validation  
✅ **Authorization**: Policy-based with role checking  
✅ **Data Isolation**: Tenant scopes and explicit ownership verification  
✅ **Input Validation**: Validator class with comprehensive rules  
✅ **File Upload Security**: Type and size validation  
✅ **SQL Injection Prevention**: Parameterized queries  
✅ **CSRF Protection**: Laravel's built-in CSRF middleware  
✅ **Audit Logging**: All operations logged with user context  
✅ **Error Handling**: Exceptions caught and logged  

---

## Technical Debt & Refactoring Opportunities

### High Priority

1. **Consolidate File Handling**
   - Move file upload logic from controller to service
   - Create dedicated FileUploadService
   - Implement file validation service

2. **Reduce Direct DB Queries**
   - Move remaining DB queries from controllers to services
   - Use Eloquent models consistently
   - Create repository layer for data access

3. **API Response Standardization**
   - Create response formatter class
   - Standardize error responses
   - Implement API versioning

### Medium Priority

4. **Implement Request Classes**
   - Create FormRequest classes for validation
   - Move validation logic from controllers
   - Centralize validation rules

5. **Add Caching Layer**
   - Implement Redis caching for frequently accessed data
   - Cache service catalog
   - Cache user preferences

6. **Implement Event System**
   - Create events for order operations
   - Implement listeners for notifications
   - Decouple notification logic

### Low Priority

7. **Documentation**
   - Add API documentation (Swagger/OpenAPI)
   - Create architecture diagrams
   - Document business rules

8. **Testing**
   - Increase unit test coverage
   - Add integration tests
   - Implement feature tests

---

## Integration Guidelines for New Features

### 1. Adding New Customer Feature

**Step 1: Create Service Class**
```php
namespace App\Services\Customer;

class NewFeatureService {
    public function execute(string $userId, array $data) {
        // Validate
        // Execute business logic
        // Log audit
        // Notify if needed
        // Return result
    }
}
```

**Step 2: Create Controller Method**
```php
public function handleFeature(Request $request) {
    $userId = session('user_id');
    $service = new NewFeatureService();
    $result = $service->execute($userId, $request->validated());
    return response()->json($result);
}
```

**Step 3: Add Route**
```php
Route::post('/customer/feature', [CustomerController::class, 'handleFeature'])
    ->middleware(['auth', 'role:customer']);
```

**Step 4: Add Tests**
```php
public function test_customer_can_use_feature() {
    // Test implementation
}
```

### 2. Adding New API Endpoint

**Step 1: Add Method to API Controller**
```php
public function newEndpoint(Request $request) {
    try {
        $userId = session('user_id');
        $data = $this->service->getData($userId, $request->all());
        return response()->json($data);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
```

**Step 2: Register Route**
```php
Route::get('/api/customer/new-endpoint', [CustomerDashboardApiController::class, 'newEndpoint']);
```

**Step 3: Document Endpoint**
```
GET /api/customer/new-endpoint
Parameters: [list parameters]
Response: [document response structure]
```

---

## Dependency Injection & Service Container

### Current Pattern

**Manual Instantiation**:
```php
$service = new CustomerOrderService();
$result = $service->getOrders($userId);
```

### Recommended Pattern

**Container Binding**:
```php
// In AppServiceProvider
$this->app->bind(CustomerOrderService::class, function ($app) {
    return new CustomerOrderService();
});

// In Controller
public function __construct(CustomerOrderService $service) {
    $this->service = $service;
}
```

---

## Testing Strategy

### Unit Tests

**Service Layer Tests**:
```php
test('customer_can_create_order')
test('customer_cannot_create_order_with_invalid_data')
test('customer_cannot_view_other_customer_orders')
test('order_total_calculated_correctly')
```

**Model Tests**:
```php
test('customer_order_relationships_work')
test('order_items_cascade_delete')
```

### Integration Tests

**API Endpoint Tests**:
```php
test('api_returns_customer_orders')
test('api_filters_orders_by_status')
test('api_paginates_results')
```

**Feature Tests**:
```php
test('customer_can_place_order_flow')
test('customer_can_upload_design_file_flow')
test('customer_receives_notifications')
```

---

## Deployment Checklist

- [ ] All tests passing (90%+ coverage)
- [ ] Code review completed
- [ ] Static analysis passing
- [ ] Performance benchmarks acceptable
- [ ] Security audit completed
- [ ] Documentation updated
- [ ] Database migrations tested
- [ ] Rollback plan prepared
- [ ] Monitoring configured
- [ ] Staging deployment verified

---

## Conclusion

The Uniprint codebase demonstrates solid architectural principles with:

✅ Clear separation of concerns  
✅ Service-oriented design  
✅ Multi-tenancy support  
✅ Comprehensive error handling  
✅ Audit logging  
✅ Security best practices  

New customer-side features should follow these established patterns to maintain consistency and code quality.

---

**Document Version**: 1.0  
**Last Updated**: November 20, 2025  
**Status**: ✅ Complete
