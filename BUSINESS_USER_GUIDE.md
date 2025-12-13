# Business User Management Guide

## Overview

Each enterprise in the Uniprint system has a corresponding business user account that manages the enterprise's operations. Business users can:

- Manage orders from customers
- Create and manage services
- Set up customization options
- Configure pricing rules
- Communicate with customers via chat
- View enterprise analytics and statistics

## System Setup

### Database Structure

The business user management system uses the following tables:

1. **users** - Stores user information
2. **login** - Stores login credentials (username/password)
3. **roles** - Links users to role types
4. **role_types** - Defines available roles (admin, business_user, customer)
5. **staff** - Links business users to enterprises
6. **enterprises** - Stores enterprise/shop information

### Key Relationships

```
User (user_id)
  ├── Login (username/password)
  ├── Roles (role_type: business_user)
  └── Staff (position, enterprise_id)
        └── Enterprise (shop details)
```

## Business User Accounts

### Pre-configured Business Users

Three sample business users are pre-configured:

1. **QuickPrint Services**
   - Username: `quickprint`
   - Password: `business123`
   - Email: quickprint@business.com

2. **Elite Copy Center**
   - Username: `elitecopy`
   - Password: `business123`
   - Email: elite@business.com

3. **Digital Print Hub**
   - Username: `digitalhub`
   - Password: `business123`
   - Email: digitalhub@business.com

### Auto-generated Business Users

18 Baguio printshops have auto-generated business user accounts. Each follows the pattern:
- Username: `[enterprisename]` (lowercase, no spaces)
- Password: `business123`
- Email: `[enterprisename]@business.com`

Example:
- Enterprise: "Almuril Copy Center"
- Username: `almurilcopycenter`
- Email: `almurilcopycenter@business.com`

## Login Process

### Step 1: Navigate to Login
Go to `/login` or click "Login" on the homepage

### Step 2: Enter Credentials
- Enter username (or email)
- Enter password

### Step 3: Dashboard Redirect
After successful login, business users are automatically redirected to `/business/dashboard`

## Business Dashboard Features

### 1. Dashboard Overview (`/business/dashboard`)

Displays key metrics:
- **Total Orders** - All orders for the enterprise
- **Pending Orders** - Orders awaiting action
- **In Progress Orders** - Orders being processed
- **Total Services** - Number of services offered
- **Total Revenue** - Sum of all transactions
- **Active Customizations** - Number of customization options
- **Pricing Rules** - Number of active pricing rules
- **Recent Orders** - Last 10 orders with customer names and status

### 2. Order Management (`/business/orders`)

**View Orders:**
- List all orders for the enterprise
- Filter and sort by date, customer, status
- Paginated view (20 per page)

**Order Details (`/business/orders/{id}`):**
- Customer information
- Order items with services and customizations
- Order status history
- Design files attached to order
- Update order status

**Update Order Status:**
- Select new status from dropdown
- Add remarks/notes
- Set expected completion date
- Audit trail is automatically logged

### 3. Service Management (`/business/services`)

**View Services:**
- List all services offered by the enterprise
- See service name, description, and base price

**Create Service (`/business/services/create`):**
- Service name (required)
- Description
- Base price
- Is active (toggle)

**Edit Service (`/business/services/{id}/edit`):**
- Modify service details
- Update pricing
- Toggle active status

**Delete Service:**
- Remove service from enterprise
- Prevents deletion if service has active orders

### 4. Customization Options (`/business/services/{serviceId}/customizations`)

**View Customizations:**
- List all customization options for a service
- See option name, type, and price modifier

**Add Customization:**
- Option name (e.g., "Premium Paper")
- Option type (e.g., "Paper Type", "Color", "Size")
- Price modifier (additional cost)

**Edit Customization:**
- Update option details
- Adjust price modifier

**Delete Customization:**
- Remove customization option
- Prevents deletion if option is used in active orders

### 5. Pricing Rules (`/business/pricing-rules`)

**View Pricing Rules:**
- List all pricing rules for the enterprise
- See rule name, type, and conditions

**Create Pricing Rule (`/business/pricing-rules/create`):**
- Rule name
- Rule type (discount, surcharge, etc.)
- Conditions (quantity, amount, etc.)
- Rule value
- Is active (toggle)

**Edit Pricing Rule (`/business/pricing-rules/{id}/edit`):**
- Modify rule details
- Update conditions and values

**Delete Pricing Rule:**
- Remove pricing rule

### 6. Design File Management

**Approve Design File:**
- Review customer-submitted design files
- Approve for production

**Reject Design File:**
- Request changes from customer
- Add rejection reason

### 7. Chat (`/business/chat`)

**Communicate with Customers:**
- Send and receive messages
- Discuss orders and services
- Real-time notifications

## Access Control

### Tenant Isolation

Business users can only access data for their assigned enterprise:
- Cannot view other enterprises' orders
- Cannot modify other enterprises' services
- Cannot access other enterprises' customizations

### Role-based Access

- Business users have the `business_user` role
- Access is enforced via `CheckRole` middleware
- Unauthorized access attempts are logged

### Data Security

- All queries are filtered by enterprise_id
- Session regeneration on login
- CSRF token protection
- IP address and user agent logging

## Creating New Business Users

### Via Seeder

Business users are automatically created when enterprises are seeded:

```php
// In BaguioPrintshopsSeeder or EnterpriseSeeder
$businessUsers[] = [
    'user_id' => Str::uuid(),
    'name' => $enterprise['name'] . ' Manager',
    'email' => $businessUserEmail,
    'position' => 'Shop Manager',
    'department' => 'Management',
];

// Login credentials are automatically created
DB::table('login')->insert([
    'login_id' => Str::uuid(),
    'user_id' => $businessUserId,
    'username' => $username,
    'password' => Hash::make('business123'),
]);

// Staff record links user to enterprise
DB::table('staff')->insert([
    'staff_id' => Str::uuid(),
    'user_id' => $businessUserId,
    'enterprise_id' => $enterpriseId,
    'position' => 'Manager',
]);

// Role is assigned
DB::table('roles')->insert([
    'role_id' => Str::uuid(),
    'user_id' => $businessUserId,
    'role_type_id' => $businessUserRoleId,
]);
```

### Manual Creation

To manually create a business user:

1. Create user record in `users` table
2. Create login record in `login` table with hashed password
3. Create staff record in `staff` table linking user to enterprise
4. Create role record in `roles` table with business_user role type

## Routes

All business user routes are protected by:
- `CheckAuth` middleware - Verifies user is logged in
- `CheckRole:business_user` middleware - Verifies user has business_user role

### Available Routes

```
GET    /business/dashboard                                    - Dashboard
GET    /business/orders                                       - Orders list
GET    /business/orders/{id}                                  - Order details
POST   /business/orders/{id}/status                           - Update order status
GET    /business/services                                     - Services list
GET    /business/services/create                              - Create service form
POST   /business/services                                     - Store service
GET    /business/services/{id}/edit                           - Edit service form
PUT    /business/services/{id}                                - Update service
DELETE /business/services/{id}                                - Delete service
GET    /business/services/{serviceId}/customizations          - Customizations list
POST   /business/services/{serviceId}/customizations          - Store customization
PUT    /business/services/{serviceId}/customizations/{optionId} - Update customization
DELETE /business/services/{serviceId}/customizations/{optionId} - Delete customization
GET    /business/pricing-rules                                - Pricing rules list
GET    /business/pricing-rules/create                         - Create rule form
POST   /business/pricing-rules                                - Store rule
GET    /business/pricing-rules/{id}/edit                      - Edit rule form
PUT    /business/pricing-rules/{id}                           - Update rule
DELETE /business/pricing-rules/{id}                           - Delete rule
POST   /business/design-files/{fileId}/approve                - Approve design file
POST   /business/design-files/{fileId}/reject                 - Reject design file
GET    /business/chat                                         - Chat interface
```

## Troubleshooting

### Cannot Login
- Verify username/email is correct
- Check password is exactly `business123` (case-sensitive)
- Ensure user has `business_user` role
- Check if user is linked to an enterprise via staff table

### Cannot Access Dashboard
- Verify you have `business_user` role
- Check if you're linked to an enterprise
- Clear browser cache and cookies
- Try logging out and logging back in

### Cannot See Enterprise Data
- Verify you're linked to the correct enterprise
- Check if enterprise_id matches in staff table
- Verify enterprise exists in enterprises table

### Cannot Create/Edit Services
- Verify you have permission (business_user role)
- Check if service belongs to your enterprise
- Verify enterprise_id matches

## Best Practices

1. **Change Default Password** - Change from `business123` to a secure password
2. **Regular Backups** - Backup important data regularly
3. **Monitor Orders** - Check dashboard daily for new orders
4. **Update Services** - Keep service information current
5. **Review Analytics** - Monitor revenue and order trends
6. **Communicate** - Use chat to respond to customer inquiries promptly

## Support

For technical support or issues, contact the system administrator.
