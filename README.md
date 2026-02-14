# UniPrint (Laravel 12) — Print Service Marketplace

**UniPrint** is a comprehensive print service marketplace platform connecting customers with print businesses. Built on Laravel 12 with modern tooling.

## Table of Contents

- [Platform Overview](#platform-overview)
- [Technology Stack](#technology-stack)
- [Features by User Role](#features-by-user-role)
  - [Customer Features](#customer-features)
  - [Business User Features](#business-user-features)
  - [Admin Features](#admin-features)
- [Core System Components](#core-system-components)
- [Database Schema](#database-schema)
- [API & Integrations](#api--integrations)
- [Requirements](#requirements)
- [Setup Instructions](#setup-instructions)
- [Run the App](#run-the-app)
- [Troubleshooting](#troubleshooting)

---

## Platform Overview

UniPrint operates as a three-sided marketplace with distinct user roles:

- **Customers** — Browse print services, place orders, upload designs, track progress, communicate with businesses
- **Business Users** — Manage print shops, services, pricing rules, orders, and customer communications
- **Administrators** — Oversee platform operations, verify businesses, manage users, configure system settings

### System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        UniPrint Platform                    │
├─────────────────────────────────────────────────────────────┤
│  Customers    │  Business Users    │  Administrators       │
│  ─────────    │  ─────────────     │  ─────────────        │
│  • Browse     │  • Onboarding      │  • Dashboard          │
│  • Order      │  • Services        │  • User Management    │
│  • Track      │  • Orders          │  • Verification       │
│  • Chat       │  • Pricing Rules   │  • System Settings    │
│  • Review     │  • Chat            │  • Reports            │
└─────────────────────────────────────────────────────────────┘
                    Laravel 12 + PostgreSQL
```

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| **Framework** | Laravel 12 (PHP 8.2+) |
| **Frontend** | TailwindCSS, Alpine.js, Bootstrap 5, Lucide Icons |
| **Database** | PostgreSQL (recommended) / MySQL |
| **Authentication** | Session-based + Social Auth (Google, Facebook) |
| **Security** | 2FA (Email/TOTP), Rate Limiting, CSRF Protection |
| **Real-time** | Pusher (Chat notifications) |
| **AI/ML** | AI Image Generation for Design Assets |
| **File Storage** | Local / S3 Compatible |
| **Payments** | GCash, Cash, PayPal |
| **QR Codes** | chillerlan/php-qrcode |

### Key Packages

```php
"laravel/framework": "^12.0"           // Core framework
"laravel/socialite": "^5.10"           // Social authentication
"laravel/octane": "^2.13"              // High-performance server
"laravel/sanctum": "^4.0"              // API authentication
"pragmarx/google2fa": "^8.0"           // TOTP 2FA
"pusher/pusher-php-server": "^7.2"     // Real-time events
"chillerlan/php-qrcode": "^5.0"        // QR code generation
"league/flysystem-aws-s3-v3": "^3.0"   // S3 storage
```

---

## Features by User Role

### Customer Features

#### Account Management
- **Registration/Login** — Email/password or social auth (Google, Facebook)
- **Two-Factor Authentication** — Email-based 2FA with secure verification codes
- **Profile Management** — Update personal info, password, profile picture
- **Social Account Linking** — Connect Facebook account for easier login

#### Security & Access Control
- **Rate-Limited Authentication** — Login/register protected via Laravel rate limiting
- **2FA Challenge Flow** — Dedicated verification/challenge pages before accessing protected areas
- **Security Settings Page** — Enable/confirm/resend/disable email-based 2FA

#### Service Discovery
- **Marketplace Browse** — View all available print services
- **Enterprise Profiles** — Browse print shop profiles and their service catalogs
- **Service Details** — Comprehensive service info with pricing, options, reviews
- **Search & Filter** — Find services by category, price, location

#### Service Saving (Cart-like)
- **Saved Services List** — View all saved services
- **Save/Unsave Service** — Add/remove services from saved list
- **Update Saved Item Configuration** — Update quantity/options/notes on saved items
- **Bulk Clear Saved Services** — Remove all saved services
- **Saved Services Count Endpoint** — Lightweight endpoint for header/cart badge count
- **Checkout Selection** — Select a subset of saved services for checkout and clear selection

#### Ordering System
- **Save Services** — Bookmark services for later ordering
- **Service Configuration** — Select options, sizes, quantities, custom fields
- **Design Upload** — Upload design files (with file type validation)
- **AI Design Generation** — Generate designs using AI (with usage limits)
- **Saved Collections** — Organize saved services into collections

#### Design Assets & Files
- **AI Design Library** — View previously generated AI designs
- **Delete AI Designs** — Remove generated designs from your library
- **Order Design Files** — Upload/replace design files associated with orders (where enabled)

#### Checkout & Payment
- **Multi-Service Checkout** — Order from multiple businesses simultaneously
- **Rush Options** — Standard, Express, Rush, Same-day pickup options
- **Payment Methods** — GCash, Cash (pickup only), PayPal integration
- **Discount Codes** — Apply promotional codes at checkout
- **Fulfillment Options** — Pickup or delivery selection
- **Order Scheduling** — Request specific fulfillment dates

#### PayPal Checkout Integration
- **Create PayPal Order** — Server-side PayPal order creation
- **Capture PayPal Order** — Server-side PayPal capture/confirmation

#### Order Management
- **Order Tracking** — Real-time status updates (Pending → Confirmed → Processing → Ready → Completed)
- **Design File Management** — Upload/replace design files post-order
- **Extension Requests** — Request order deadline extensions
- **Extension Request Responses** — Accept/decline business-proposed extensions
- **Order Cancellation** — Cancel orders before processing begins
- **Completion Confirmation** — Confirm receipt and satisfaction

#### Notifications
- **In-app Notifications** — Order and workflow updates surfaced in the UI
- **Mark Notifications Read** — Explicit action to mark notifications as read

#### Communication
- **Real-time Chat** — Direct messaging with business owners
- **Notifications** — In-app notifications for order updates, messages
- **Review System** — Rate and review completed orders

#### Chat (Realtime)
- **Conversation List** — View all conversations
- **Start Chat with a Print Shop** — Open chat from enterprise/service context
- **Typing Indicators** — Real-time typing events
- **Read Receipts** — Mark messages as read
- **Online Status** — Update/check online status
- **Pusher Auth** — Authenticate private channels for real-time messaging

#### Reporting
- **Report Businesses/Services** — Flag inappropriate content or issues to admins
- **System Feedback** — Submit reviews and improvement suggestions directly to admins

#### Public Pages
- **Terms Page** — Public terms and conditions page
- **Enterprise Listing Page** — Browse all enterprises

---

### Business User Features

#### Onboarding & Verification
- **Business Registration** — Onboarding flow for new print businesses
- **Document Verification** — Submit business documents for admin verification
- **Enterprise Profile** — Business name, description, contact info, branding

#### Business Verification Status Gate
- **Verified Business Access Control** — Unverified businesses are blocked from full dashboard access until verified
- **Pending Page** — Dedicated page/flow for businesses awaiting verification

#### Service Management
- **Service Creation** — Create print services with detailed specifications
- **Service Configuration**:
  - Base pricing and quantity tiers
  - Customization options (sizes, paper types, finishes)
  - Custom fields for special requirements
  - File upload requirements
  - Rush order support toggle
  - Payment method restrictions
  - Fulfillment type (pickup/delivery/both)
- **Service Images** — Upload multiple images, set primary image
- **Service Status** — Activate/deactivate services

#### Upload Controls
- **Enable/Disable Design Upload** — Toggle file upload per service
- **Require Design Upload** — Force customers to upload files for a service

#### Pricing & Customization
- **Pricing Rules** — Advanced pricing based on quantity, options, formulas
- **Customization Groups** — Organize options into logical groups
- **Option Pricing** — Price modifiers for each customization choice
- **Custom Size Support** — Enable and price custom sizing

#### Custom Fields
- **Custom Field Builder** — Create/update/delete service-specific custom fields

#### Order Management
- **Order Dashboard** — View all incoming orders with status filters
- **Order Actions**:
  - Confirm orders
  - Update status (Pending → Confirmed → Processing → Ready → Completed)
  - Mark downpayment received
  - Confirm full payment
  - Print order details
  - Request deadline extensions
- **Walk-in Orders** — Create orders for in-person customers
- **Design File Approval** — Review and approve/reject customer uploads

#### Order Documents
- **Printable Order View** — Print-ready order details for shop processing

#### Customer Communication
- **Real-time Chat** — Respond to customer inquiries via integrated chat
- **Notification Management** — Mark notifications as read/unread

#### Notifications
- **Order Notifications** — Receive order/extension/payment notifications
- **Mark Notifications Read** — Acknowledge notifications from the business dashboard

#### Business Settings
- **Account Settings** — Update business info, contact details
- **Checkout Configuration**:
  - Allowed payment methods
  - Supported fulfillment methods
  - Rush options with custom fees and lead times
- **GCash Settings** — Configure GCash payment receiving

#### Branding
- **Shop Logo Upload** — Upload shop logo for enterprise profile

---

### Admin Features

#### Dashboard & Analytics
- **Real-time Statistics** — User counts, order volumes, revenue metrics
- **Enterprise Stats** — Business registration and verification status
- **System Health** — Cache status, database overview

#### User Management
- **User Directory** — View all registered users
- **User Actions**:
  - View user details and activity
  - Toggle active/inactive status
  - Disable 2FA for users
  - Delete users (with dependency cleanup)
  - Create new users manually

#### Enterprise Management
- **Enterprise List** — All registered businesses
- **Verification** — Review and verify business documents
- **Enterprise Actions** — Toggle active status, view details

#### Order Oversight
- **All Orders** — View complete order history across platform
- **Order Details** — Deep dive into any order
- **Status Management** — Update order statuses when needed

#### Content Moderation
- **User Reports** — Review customer reports about businesses/services
- **Report Resolution** — Mark reports as resolved with notes
- **System Feedback Review** — View user-submitted feedback and mark as reviewed/addressed

#### System Administration
- **System Settings**:
  - Branding configuration (name, tagline, logos)
  - Order auto-complete settings
  - Order overdue cancellation days
  - Tax rate configuration
- **Database Management** — Create/download backups, database reset
- **Cache Management** — Clear cache, optimize application
- **Audit Logs** — View system activity logs

#### Admin Real-time / API Endpoints
- **Dashboard Stats API** — Endpoint for live-updating dashboard metrics
- **Enterprise Stats API** — Endpoint for enterprise analytics widgets

---

## HTTP API (Session + Sanctum-ready)

This project includes API routes (see `routes/api.php`). Some endpoints are designed for SPA-style fetch calls and are **Sanctum-ready**.

### Authentication API
- **Issue Token** — `POST /api/auth/token`
- **Logout / Revoke Token** — `POST /api/auth/logout` (requires `auth:sanctum`)

### Marketplace API (Customer)
- **List services** — `GET /api/marketplace/services`
- **Search suggestions** — `GET /api/marketplace/search-suggestions`
- **Service details** — `GET /api/marketplace/service/{serviceId}`
- **Toggle favorite** — `POST /api/marketplace/toggle-favorite`
- **Categories** — `GET /api/marketplace/categories`
- **Enterprises** — `GET /api/marketplace/enterprises`
- **Locations** — `GET /api/marketplace/locations`

### Customer Dashboard API
- **Services** — `GET /api/customer/services`
- **Orders** — `GET /api/customer/orders`
- **Payments history** — `GET /api/customer/payments`
- **Update profile** — `POST /api/customer/profile`
- **Dashboard stats** — `GET /api/customer/stats`
- **Saved services** — `GET /api/customer/saved-services`

### Chat API
- **Resolve enterprise owner** — `POST /api/chat/enterprise-owner`
- **Conversations** — `GET /api/chat/conversations`
- **Get/create conversation** — `POST /api/chat/conversations`
- **Conversation details** — `GET /api/chat/conversations/{conversationId}`
- **Messages** — `GET /api/chat/conversations/{conversationId}/messages`
- **Send message** — `POST /api/chat/messages`
- **Mark as read** — `POST /api/chat/messages/read`
- **Typing** — `POST /api/chat/typing`
- **Online status** — `POST /api/chat/online-status`
- **Online status check** — `POST /api/chat/online-status/check`
- **Available businesses** — `GET /api/chat/available-businesses`
- **Pusher auth** — `POST /api/chat/pusher/auth`
- **Cleanup** — `POST /api/chat/cleanup`
- **Health check** — `GET /api/chat/health`

### Pricing API
- **Calculate price** — `POST /api/pricing/calculate`

---

## Core System Components

### Controllers (20+)

| Controller | Purpose |
|------------|---------|
| `AuthController` | Login, registration, logout, password management |
| `SocialAuthController` | Google/Facebook OAuth integration |
| `TwoFactorController` | 2FA setup, verification, email codes |
| `CustomerController` | Customer orders, reviews, design uploads |
| `CustomerDashboardController` | Customer dashboard, marketplace |
| `BusinessController` | Business dashboard, orders, services, settings |
| `BusinessOnboardingController` | New business registration flow |
| `BusinessVerificationController` | Document submission for verification |
| `AdminController` | Admin dashboard, user/enterprise/order management |
| `CheckoutController` | Checkout flow, PayPal integration, discounts |
| `SavedServiceController` | Save/organize services for later |
| `AIDesignController` | AI image generation for designs |
| `ChatController` | Real-time messaging between customers and businesses |
| `ProfileController` | User profile, settings, PayPal connect |
| `ServiceMarketplaceController` | Public service browsing |
| `HomeController` | Landing page, enterprise listings |
| `UserReportController` | Content reporting system |

### Models (25+)

| Model | Description |
|-------|-------------|
| `User` | Platform users (customers, business users, admins) |
| `Enterprise` | Print business profiles |
| `Service` | Print services offered by businesses |
| `SavedService` | User's saved/bookmarked services |
| `CustomerOrder` | Customer purchase orders |
| `OrderItem` | Individual items within orders |
| `OrderItemCustomization` | Selected options for order items |
| `CustomizationOption` | Available service customizations |
| `CustomizationGroup` | Grouping of customization options |
| `PricingRule` | Dynamic pricing formulas |
| `Conversation` | Chat conversations |
| `ChatMessage` | Individual chat messages |
| `AiImageGeneration` | AI-generated design assets |
| `DesignAsset` | User-uploaded design files |
| `Transaction` | Payment transactions |
| `Staff` | Business staff members |
| `Role` / `RoleType` | User role management |

### Services

| Service | Function |
|---------|----------|
| `OrderProcessingService` | Complete order lifecycle management |
| `PricingEngine` | Dynamic price calculation with rules |
| `NotificationService` | User notification delivery |

### Middleware

| Middleware | Purpose |
|------------|---------|
| `CheckAuth` | Session authentication check |
| `CheckRole` | Role-based access control |
| `TwoFactorVerify` | 2FA enforcement |
| `EnsureBusinessVerified` | Block unverified businesses |
| `EnsureAiGenerationLimit` | Rate limit AI generation |

---

## Database Schema

### Core Tables

```
users                          -- Platform users
├── roles                      -- User roles (customer/business_user/admin)
├── role_types                 -- Role definitions
├── social_logins              -- OAuth connections

enterprises                    -- Print businesses
├── staff                      -- Business staff
├── services                   -- Print services
│   ├── customization_groups   -- Service option groups
│   ├── customization_options  -- Individual options
│   ├── pricing_rules          -- Dynamic pricing
│   └── service_images         -- Service photos
├── saved_services             -- User bookmarks
│   └── saved_service_design_files -- Uploads for saved services

customer_orders                -- Purchase orders
├── order_items                -- Order line items
├── order_item_customizations  -- Selected options
├── order_status_history       -- Status change log
├── order_workflows            -- Order workflow definitions
├── order_design_files         -- Design file attachments
├── payments                   -- Payment records
└── transactions               -- Financial transactions

conversations                  -- Chat conversations
├── chat_messages              -- Individual messages
└── chatbot_interactions       -- AI chatbot logs

ai_image_generations           -- AI-generated designs
design_assets                  -- User design uploads

user_reports                   -- Content reports to admin
system_settings                -- Platform configuration
statuses                       -- Order status definitions
discount_codes                 -- Promotional codes
```

---

## API & Integrations

### Payment Integrations
- **PayPal** — Order creation and capture via PayPal REST API
- **GCash** — Manual verification workflow
- **Cash** — In-person payment confirmation

### Social Authentication
- **Google OAuth 2.0** — Login/registration
- **Facebook Login** — Login + account linking

### Real-time Communication
- **Pusher** — Chat messages, notifications

### AI Services
- **AI Image Generation** — Design asset generation with usage tracking

### Admin API Endpoints
```
GET  /admin/api/dashboard-stats      -- Dashboard metrics
GET  /admin/api/enterprise-stats     -- Enterprise analytics
```

---

## February Software Testing Dataset (Baguio Demo Seed)

This project includes a **realistic demo dataset** intended to simulate continuous platform usage throughout **the month of February** during software testing / QA.

### What gets seeded

- **Baguio-based enterprises only**
  - Print shops use Baguio/Philippines addresses (e.g., Session Rd, Mabini St, Bonifacio St).
- **Filipino test users**
  - Customer and business user profiles use Filipino names.
- **Chat activity**
  - Seeded conversations and chat messages simulate typical customer ↔ business communication (design help, rush printing requests, pricing inquiries).
- **February order activity**
  - Orders are distributed across February timestamps to mimic daily usage.
  - Orders include multiple items and random customization selections where available.
  - Status history + transactions are generated for completed/delivered orders.

### Why this matters (testing realism)

This dataset is used to test the system under conditions that feel "alive":

- **Dashboards have real numbers** (orders, customers, business activity)
- **Order timelines look realistic** (varying status, timestamps across the month)
- **Customization + pricing paths are exercised** (options, price modifiers, totals)
- **Chat UIs have real conversations** (read/unread states, timestamps)

### How to seed

```bash
php artisan migrate:fresh --seed
```

The default `DatabaseSeeder` runs the demo seeders:

- `RoleTypesSeeder`
- `StatusesSeeder`
- `NewUsersSeeder`
- `BaguioPrintshopsSeeder`
- `SampleOrdersSeeder`


---

## System Complexity & Advanced Features

### Dynamic Customization Engine

UniPrint implements a sophisticated **rule-based customization system** that allows businesses to create complex product configurations while maintaining data integrity and user experience:

#### **Multi-Level Customization Architecture**
```
Services
├── Customization Groups (logical categories)
│   ├── Customization Options (individual choices)
│   └── Customization Rules (dependencies & constraints)
└── Pricing Rules (dynamic calculations)
```

#### **Smart Dependency Resolution**
- **Rule Types**: `requires`, `conflicts`, `requires_any`
- **Real-time Validation**: Frontend updates available options based on current selections
- **Caching Layer**: 5-minute cache for available options to optimize performance
- **Bulk Operations**: Import/export entire customization configurations for similar services

**Example**: A "Glossy Finish" option might require "Premium Paper" and conflict with "Matte Coating"

#### **Space-Efficient Design Choices**
- **Option IDs as strings**: Eliminates integer sequence gaps, saves storage
- **JSON Storage**: Complex rules and tier structures stored as JSON, avoiding normalization overhead
- **Selective Loading**: Only loads customization data when needed (lazy loading)
- **Cache Invalidation**: Targeted cache clearing prevents full cache rebuilds

### Advanced Pricing Engine

The **PricingEngine** service provides enterprise-grade pricing calculations with multiple strategies:

#### **Multi-Dimensional Pricing**
- **Base Price**: Service-specific starting price
- **Customization Modifiers**: Individual option price impacts
- **Quantity Tiers**: Volume discounts with configurable thresholds
- **Dynamic Rules**: Time-based, customer-type, and order-value pricing
- **Safe Formula Evaluation**: Custom math parser (NO eval()) for complex calculations

#### **Pricing Rule Conditions**
```php
// Example rule: 15% discount for orders over 5000 with rush delivery
{
  'conditions': [
    ['field' => 'subtotal', 'operator' => '>', 'value' => 5000],
    ['field' => 'is_rush', 'operator' => '=', 'value' => true]
  ],
  'calculation_method' => 'percentage',
  'value' => 15
}
```

#### **Performance Optimizations**
- **Rule Caching**: 1-hour cache for enterprise pricing rules
- **Batch Processing**: Calculate multiple items in single database round-trip
- **Priority Ordering**: Rules processed by priority to ensure correct application

### Workflow Automation System

**OrderWorkflow** provides customizable order processing pipelines with intelligent deadline management:

#### **Dynamic Workflow Assignment**
- **Context-Aware**: Workflows selected based on order complexity, value, and type
- **Conditional Logic**: Support for priority, amount, and category-based routing
- **Fallback Mechanism**: Default workflow when no specific rules match

#### **Automated Deadline Calculations**
```php
// Rush options with business-day awareness
'same_day'  => +3 hours (if before 2 PM)
'rush'      => +6 hours (next business day)
'express'   => +24 hours (next business day at 5 PM)
'standard'  => +48 hours (2 business days at 5 PM)
```

#### **Progress Tracking & Analytics**
- **Stage Progression**: Automatic advancement through workflow stages
- **Timeline Visualization**: Real-time order progress with estimated vs actual times
- **Performance Metrics**: Average completion time per workflow type
- **Business Day Calculations**: Weekends and holidays excluded from deadlines

### Saved Service Architecture

**SavedServiceCollection** implements a sophisticated cart-like system with enterprise features:

#### **Space-Optimized Storage**
- **UUID Primary Keys**: Distributed ID generation prevents bottlenecks
- **JSON Customizations**: Complex selections stored as JSON arrays
- **Price Caching**: Pre-calculated totals stored to avoid recalculation
- **Lazy Relationships**: Service data loaded only when displayed

#### **Collection Management**
- **Multi-Enterprise Support**: Save services from different businesses
- **Bulk Operations**: Clear, update, and remove operations with single queries
- **Relationship Optimization**: Eager loading prevents N+1 query problems

### Database Design Optimizations

#### **Multi-Tenant Architecture**
- **Enterprise Scoping**: Global scopes ensure data isolation between businesses
- **Shared Tables**: Common tables (users, statuses) shared for efficiency
- **Tenant-Specific Tables**: Business data separated for security and performance

#### **Performance Patterns**
- **Composite Indexes**: Optimized for common query patterns
- **Soft Deletes**: Data retention without performance impact
- **Audit Trails**: Automatic history tracking for compliance
- **Connection Pooling**: Database connection reuse for high concurrency

#### **Scalability Considerations**
- **Horizontal Scaling**: Stateless design enables multiple app servers
- **Read Replicas**: Read-heavy operations can be offloaded
- **Cache Layers**: Redis-ready for distributed caching
- **Queue System**: Background processing for heavy operations

---

## Requirements

- PHP `^8.2`
- Composer 2
- Node.js `>= 18` + npm
- A database (PostgreSQL recommended)

You can run the built-in checker:

```bash
php scripts/check-requirements.php
```

### Required PHP Extensions

At minimum, you must have:

- `pdo`
- `mbstring`
- `openssl`
- `json`
- `tokenizer`
- `xml`
- `curl`
- `zip`
- `bcmath`
- `fileinfo`

Database driver extensions (install at least one):

- **PostgreSQL**: `pdo_pgsql` (and usually `pgsql`)
- **MySQL**: `pdo_mysql`

If you use Supabase/PostgreSQL and see `could not find driver`, it means `pdo_pgsql` is missing.

---

## Setup Instructions

### 1) Install dependencies

```bash
composer install
npm install
```

### 2) Create `.env`

```bash
cp .env.example .env
php artisan key:generate
```

### 3) Configure database

Edit `.env`:

- `DB_CONNECTION` (`pgsql` recommended)
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

### 4) Migrate + seed

```bash
php artisan migrate --seed
```

### 5) Build frontend assets

```bash
npm run build
```

---

## Run the App

### Option A — Simple run (recommended for debugging)

```bash
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
```

Open:

- `http://127.0.0.1:8000`

### Option B — Full dev (Vite)

In two terminals:

Terminal 1:
```bash
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
```

Terminal 2:
```bash
npm run dev
```

---

## Troubleshooting

### 1) `could not find driver (Connection: pgsql ...)`

Cause: `pdo_pgsql` is not installed/enabled on that machine.

Quick check:

```bash
php -m | grep -E 'pdo_pgsql|pgsql' || true
```

Install the correct package for your OS (examples):

- Ubuntu/Debian: `sudo apt install php-pgsql`
- Arch: `sudo pacman -S php-pgsql`

After installing, restart your PHP process / rerun `php artisan serve`.

### 2) App boots but fails after moving to another PC

If config is cached from a previous environment, Laravel may still use old `.env` values.

Run:

```bash
php artisan optimize:clear
```

### 3) `127.0.0.1 refused to connect` after a refresh

This is not a normal Laravel 500 response. It means the web server process stopped.

Do this to capture the real error:

1. Start the server with:
   ```bash
   php artisan serve --host=127.0.0.1 --port=8000 --no-reload
   ```
2. Keep that terminal open.
3. Refresh the failing page.
4. Copy/paste the **last lines** printed in that terminal.

If the process exits with a signal (e.g. `SIGILL` / `Segmentation fault`), it is usually a PHP build/extension crash. Common workarounds:

- Use a different PHP version (often `8.3.x` is more stable than `8.4.x` for some extension combos)
- Disable JIT / PCRE JIT in your `php.ini` (if enabled)

### 4) Sessions / cache / queue portability

For local development and portability (especially when DB access is unstable), use:

- `SESSION_DRIVER=file`
- `CACHE_STORE=file`
- `QUEUE_CONNECTION=sync`

These are set in `.env.example` as the recommended defaults.

### 5) Mail not sending (2FA codes not delivered)

For Gmail: Use App Password, not regular password
- Enable 2FA on Gmail account
- Generate App Password at https://myaccount.google.com/apppasswords
- Use that in `MAIL_PASSWORD` in `.env`

Check `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT` settings match your provider.

---

## Additional Documentation

- `BUSINESS_USER_GUIDE.md` — Detailed guide for print business owners
- `BUSINESS_USER_CREDENTIALS.md` — Business account setup
- `QUICK_START_BUSINESS_USERS.md` — Quick onboarding for businesses

---

*UniPrint v1.0 — Laravel 12 Print Service Marketplace*
