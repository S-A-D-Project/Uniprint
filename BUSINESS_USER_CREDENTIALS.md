# Business User Login Credentials

## Default Password
All business users use the default password: **business123**

## Sample Business Users (Pre-configured)

### QuickPrint Services
- **Username:** quickprint
- **Email:** quickprint@business.com
- **Password:** business123

### Elite Copy Center
- **Username:** elitecopy
- **Email:** elite@business.com
- **Password:** business123

### Digital Print Hub
- **Username:** digitalhub
- **Email:** digitalhub@business.com
- **Password:** business123

## Baguio Printshops (Auto-generated)

Each Baguio printshop has a corresponding business user manager account:

| Enterprise Name | Username | Email | Password |
|---|---|---|---|
| Almuril Copy Center | almurilcopycenter | almurilcopycenter@business.com | business123 |
| Anndreleigh Photocopy Services | anndreleighphotocopyservices | anndreleighphotocopyservices@business.com | business123 |
| Baguio Allied Printers | baguioalliedprinters/alliedprintingpress | baguioalliedprinters/alliedprintingpress@business.com | business123 |
| Bensa Publishing House | bensapublishinghouse | bensapublishinghouse@business.com | business123 |
| Cjs Printing Services | cjsprintingservices | cjsprintingservices@business.com | business123 |
| Cyber Printing Press | cyberprintingpress | cyberprintingpress@business.com | business123 |
| Dacanay Printshop | dacanayprintshop | dacanayprintshop@business.com | business123 |
| Gold Ink Printing Press | goldinkprintingpress | goldinkprintingpress@business.com | business123 |
| Higher-UP Printing | higher-upprinting | higher-upprinting@business.com | business123 |
| IKT Printing Services | iktprintingservices | iktprintingservices@business.com | business123 |
| Kebs Enterprise | kebsenterprise | kebsenterprise@business.com | business123 |
| LSK Printing Services | lskprintingservices | lskprintingservices@business.com | business123 |
| Point and Print Printing Services | pointandprintprintingservices | pointandprintprintingservices@business.com | business123 |
| PRINTOREX Digital Printing Shop | printorexdigitalprintingshop | printorexdigitalprintingshop@business.com | business123 |
| Printitos Printing Services | printitosprintingservices | printitosprintingservices@business.com | business123 |
| Unique Printing Press | uniqueprintingpress | uniqueprintingpress@business.com | business123 |
| V. Mendoza Printing Press | v.mendozaprintingpress | v.mendozaprintingpress@business.com | business123 |
| Valley Printing Specialist | valleyprintingspecialist | valleyprintingspecialist@business.com | business123 |

## Business User Dashboard Features

Once logged in, business users can access:

### 1. Dashboard (`/business/dashboard`)
- View total orders
- View pending orders
- View in-progress orders
- View total services offered
- View total revenue
- View active customizations
- View pricing rules
- See recent orders

### 2. Order Management (`/business/orders`)
- View all orders for their enterprise
- View order details
- Update order status

### 3. Service Management (`/business/services`)
- Create new services
- Edit existing services
- Delete services
- View all services for their enterprise

### 4. Customization Management (`/business/services/{serviceId}/customizations`)
- Add customization options to services
- Edit customization options
- Delete customization options

### 5. Pricing Rules (`/business/pricing-rules`)
- Create pricing rules
- Edit pricing rules
- Delete pricing rules
- View active pricing rules

### 6. Design File Management
- Approve design files
- Reject design files

### 7. Chat (`/business/chat`)
- Communicate with customers

## Access Control

- Business users can only access their own enterprise's data
- Tenant isolation middleware ensures data security
- Each business user is linked to exactly one enterprise via the `staff` table
- Role-based access control is enforced via the `CheckRole` middleware

## Database Structure

### Users Table
- Stores user information
- Each business user has a unique `user_id` (UUID)

### Login Table
- Stores login credentials
- Username and hashed password
- Linked to users via `user_id`

### Roles Table
- Assigns role types to users
- Business users have `role_type` = 'business_user'

### Staff Table
- Links business users to enterprises
- Each staff record has:
  - `user_id` (references users)
  - `enterprise_id` (references enterprises)
  - `position` (e.g., "Manager")
  - Unique constraint on (user_id, enterprise_id)

## Login Flow

1. User navigates to `/login`
2. Enters username (or email) and password
3. System validates credentials against `login` table
4. On success, user is redirected to appropriate dashboard based on role:
   - Admin → `/admin/dashboard`
   - Business User → `/business/dashboard`
   - Customer → `/customer/dashboard`

## Security Features

- Session regeneration on login
- CSRF token protection
- Password hashing using bcrypt
- IP address and user agent logging
- Tenant isolation for multi-tenant data access
- Role-based middleware protection
