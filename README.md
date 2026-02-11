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

#### Service Discovery
- **Marketplace Browse** — View all available print services
- **Enterprise Profiles** — Browse print shop profiles and their service catalogs
- **Service Details** — Comprehensive service info with pricing, options, reviews
- **Search & Filter** — Find services by category, price, location

#### Ordering System
- **Save Services** — Bookmark services for later ordering
- **Service Configuration** — Select options, sizes, quantities, custom fields
- **Design Upload** — Upload design files (with file type validation)
- **AI Design Generation** — Generate designs using AI (with usage limits)
- **Saved Collections** — Organize saved services into collections

#### Checkout & Payment
- **Multi-Service Checkout** — Order from multiple businesses simultaneously
- **Rush Options** — Standard, Express, Rush, Same-day pickup options
- **Payment Methods** — GCash, Cash (pickup only), PayPal integration
- **Discount Codes** — Apply promotional codes at checkout
- **Fulfillment Options** — Pickup or delivery selection
- **Order Scheduling** — Request specific fulfillment dates

#### Order Management
- **Order Tracking** — Real-time status updates (Pending → Confirmed → Processing → Ready → Completed)
- **Design File Management** — Upload/replace design files post-order
- **Extension Requests** — Request order deadline extensions
- **Order Cancellation** — Cancel orders before processing begins
- **Completion Confirmation** — Confirm receipt and satisfaction

#### Communication
- **Real-time Chat** — Direct messaging with business owners
- **Notifications** — In-app notifications for order updates, messages
- **Review System** — Rate and review completed orders

#### Reporting
- **Report Businesses/Services** — Flag inappropriate content or issues to admins

---

### Business User Features

#### Onboarding & Verification
- **Business Registration** — Onboarding flow for new print businesses
- **Document Verification** — Submit business documents for admin verification
- **Enterprise Profile** — Business name, description, contact info, branding

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

#### Pricing & Customization
- **Pricing Rules** — Advanced pricing based on quantity, options, formulas
- **Customization Groups** — Organize options into logical groups
- **Option Pricing** — Price modifiers for each customization choice
- **Custom Size Support** — Enable and price custom sizing

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

#### Customer Communication
- **Real-time Chat** — Respond to customer inquiries via integrated chat
- **Notification Management** — Mark notifications as read/unread

#### Business Settings
- **Account Settings** — Update business info, contact details
- **Checkout Configuration**:
  - Allowed payment methods
  - Supported fulfillment methods
  - Rush options with custom fees and lead times
- **GCash Settings** — Configure GCash payment receiving

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

#### System Administration
- **System Settings**:
  - Branding configuration (name, tagline, logos)
  - Order auto-complete settings
  - Order overdue cancellation days
  - Tax rate configuration
- **Database Management** — Create/download backups, database reset
- **Cache Management** — Clear cache, optimize application
- **Audit Logs** — View system activity logs

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
