# WIT MS - Warehouse Inventory and Tracking Management System

![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-orange)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-blue)
![License](https://img.shields.io/badge/license-MIT-green)

## 🏗️ Project Overview

**WITMS** is a comprehensive warehouse and financial management system built for **WeBuild** construction company. The system manages inventory across multiple warehouses while integrating with finance modules for accounts payable and receivable operations.

### Company Context
- **Client:** WeBuild Construction Company
- **Warehouses:** 3 active warehouses (4th coming soon)
- **Operations:** Construction materials and equipment tracking
- **Finance:** Central office handling billing, AP, and AR

---

## 📋 Table of Contents

- [Features](#-features)
- [System Architecture](#-system-architecture)
- [Database Schema](#-database-schema)
- [Installation](#-installation)
- [API Documentation](#-api-documentation)
- [User Roles & Permissions](#-user-roles--permissions)
- [Development Status](#-development-status)
- [Technical Stack](#-technical-stack)
- [Contributing](#-contributing)

---

## ✨ Features

### ✅ Completed Features (40-45%)

#### **1. Authentication & Authorization**
- ✅ User registration and login
- ✅ Role-based access control (RBAC)
- ✅ Session management
- ✅ Password hashing (bcrypt)

#### **2. Inventory Management**
- ✅ Full CRUD operations for inventory items
- ✅ Multi-warehouse support
- ✅ Category-based organization
- ✅ Stock level tracking
- ✅ Low stock alerts
- ✅ Inventory statistics and reporting

#### **3. Warehouse Management**
- ✅ Warehouse CRUD operations
- ✅ Status management (active/inactive/maintenance)
- ✅ Capacity tracking
- ✅ Warehouse-specific inventory views

#### **4. Stock Movement System** *(NEW)*
- ✅ Stock IN/OUT operations
- ✅ Inter-warehouse transfers
- ✅ Stock adjustments
- ✅ Movement history and audit trails
- ✅ RESTful API endpoints

### 🚧 In Development (15-20%)

#### **5. Finance Modules** *(Database Ready)*
- 🚧 Accounts Payable (AP)
  - ✅ Database migrations
  - ✅ Vendor management model
  - ⏳ Controller & API
  - ⏳ Payment processing
- 🚧 Accounts Receivable (AR)
  - ✅ Database migrations
  - ✅ Client management model
  - ⏳ Controller & API
  - ⏳ Invoice generation

### ⏳ Planned Features (35-40%)

#### **6. Advanced Features**
- ⏳ Barcode/QR Code integration
- ⏳ Real-time stock updates (AJAX/WebSocket)
- ⏳ Advanced reporting dashboard
- ⏳ PDF report generation
- ⏳ Email notifications
- ⏳ API authentication (JWT)
- ⏳ Mobile-responsive interface

---

## 🏛️ System Architecture

### MVC Structure

```
WAREHOUSE-GROUP6/
├── app/
│   ├── Controllers/          # Request handlers
│   │   ├── AUTH.php                    ✅ Authentication
│   │   ├── Dashboard.php               ✅ Role-based dashboards
│   │   ├── InventoryController.php     ✅ Inventory CRUD
│   │   └── StockMovementController.php ✅ Stock transactions
│   │
│   ├── Models/              # Business logic & database
│   │   ├── UserModel.php               ✅ User management
│   │   ├── WarehouseModel.php          ✅ Warehouse data
│   │   ├── CategoryModel.php           ✅ Material categories
│   │   ├── InventoryModel.php          ✅ Inventory operations
│   │   ├── StockMovementModel.php      ✅ Movement tracking
│   │   ├── VendorModel.php             ✅ Vendor management
│   │   └── ClientModel.php             ✅ Client management
│   │
│   ├── Database/
│   │   └── Migrations/      # Database versioning
│   │       ├── 2025-08-31_CreateUsersTable.php
│   │       ├── 2025-09-03_CreateWarehousesTable.php
│   │       ├── 2025-09-03_CreateCategoriesTable.php
│   │       ├── 2025-09-03_CreateInventoryItemsTable.php
│   │       ├── 2025-09-03_CreateStockMovementsTable.php
│   │       ├── 2025-10-31_CreateVendorsTable.php         ✅ NEW
│   │       ├── 2025-10-31_CreateClientsTable.php         ✅ NEW
│   │       ├── 2025-10-31_CreateAccountsPayableTable.php ✅ NEW
│   │       └── 2025-10-31_CreateAccountsReceivableTable.php ✅ NEW
│   │
│   ├── Config/
│   │   ├── Routes.php        # URL routing
│   │   └── Database.php      # DB configuration
│   │
│   └── Views/               # Frontend templates
│
├── public/                  # Public assets
│   ├── css/
│   ├── js/
│   └── index.php           # Entry point
│
└── writable/               # Logs & cache
    ├── logs/
    └── cache/
```

---

## 🗄️ Database Schema

### Core Tables

#### **1. users** (Authentication)
```sql
- id (PK)
- email (unique)
- password (hashed)
- role (enum: warehouse_manager, warehouse_staff, etc.)
- first_name, last_name, middle_name
- created_at, updated_at
```

#### **2. warehouses** (Warehouse Management)
```sql
- id (PK)
- warehouse_name
- location
- capacity
- status (enum: active, inactive, maintenance)
- created_at, updated_at
```

#### **3. categories** (Material Categories)
```sql
- id (PK)
- category_name
- description
- created_at, updated_at
```

#### **4. inventory_items** (Stock Items)
```sql
- id (PK)
- item_id (unique code)
- item_name
- category_id (FK)
- warehouse_id (FK)
- current_stock
- minimum_stock
- unit_price
- unit_of_measure
- status
- created_at, updated_at
```

#### **5. stock_movements** (Transaction Logs)
```sql
- id (PK)
- inventory_item_id (FK)
- movement_type (enum: in, out, transfer, adjustment)
- quantity
- from_warehouse_id (FK, nullable)
- to_warehouse_id (FK, nullable)
- reference_number
- notes
- performed_by (FK -> users)
- created_at, updated_at
```

### Finance Tables

#### **6. vendors** (Suppliers)
```sql
- id (PK)
- vendor_code (unique)
- vendor_name
- contact_person, email, phone
- address, tax_id
- payment_terms
- status
- created_at, updated_at, deleted_at
```

#### **7. clients** (Customers)
```sql
- id (PK)
- client_code (unique)
- client_name
- contact_person, email, phone
- address, tax_id
- credit_limit
- payment_terms
- status
- created_at, updated_at, deleted_at
```

#### **8. accounts_payable** (Vendor Invoices)
```sql
- id (PK)
- invoice_number (unique)
- vendor_id (FK)
- invoice_date, due_date
- invoice_amount, paid_amount, balance
- status (enum: pending, partial, paid, overdue, cancelled)
- payment_method, payment_reference
- warehouse_id (FK, nullable)
- created_by (FK -> users)
- created_at, updated_at, deleted_at
```

#### **9. accounts_receivable** (Client Invoices)
```sql
- id (PK)
- invoice_number (unique)
- client_id (FK)
- invoice_date, due_date
- invoice_amount, received_amount, balance
- status (enum: pending, partial, paid, overdue, cancelled)
- payment_method, payment_reference
- warehouse_id (FK, nullable)
- created_by (FK -> users)
- created_at, updated_at, deleted_at
```

#### **10. ap_payment_transactions** (AP Payments)
```sql
- id (PK)
- ap_id (FK -> accounts_payable)
- payment_date
- amount
- payment_method
- reference_number
- notes
- processed_by (FK -> users)
- created_at
```

#### **11. ar_payment_transactions** (AR Receipts)
```sql
- id (PK)
- ar_id (FK -> accounts_receivable)
- payment_date
- amount
- payment_method
- reference_number
- notes
- processed_by (FK -> users)
- created_at
```

### Entity Relationship Diagram (ERD)

```
[users] 1---* [stock_movements] (performed_by)
[users] 1---* [accounts_payable] (created_by)
[users] 1---* [accounts_receivable] (created_by)

[warehouses] 1---* [inventory_items]
[warehouses] 1---* [stock_movements] (from/to)

[categories] 1---* [inventory_items]

[inventory_items] 1---* [stock_movements]

[vendors] 1---* [accounts_payable]
[clients] 1---* [accounts_receivable]

[accounts_payable] 1---* [ap_payment_transactions]
[accounts_receivable] 1---* [ar_payment_transactions]
```

---

## 🚀 Installation

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- XAMPP/WAMP/MAMP or equivalent

### Step 1: Clone Repository
```bash
cd C:\xampp\htdocs
git clone <repository-url> WAREHOUSE-GROUP6
cd WAREHOUSE-GROUP6
```

### Step 2: Install Dependencies
```bash
composer install
```

### Step 3: Configure Database
1. Create database in MySQL:
```sql
CREATE DATABASE witms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update `app/Config/Database.php`:
```php
public array $default = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'witms_db',
    'DBDriver' => 'MySQLi',
    'port'     => 3306,
];
```

### Step 4: Run Migrations
```bash
php spark migrate
```

### Step 5: Seed Data (Optional)
```bash
php spark db:seed UserSeeder
php spark db:seed WarehouseSeeder
```

### Step 6: Start Server
```bash
php spark serve
```

Access the application at: `http://localhost:8080`

---

## 📡 API Documentation

### Base URL
```
http://localhost:8080/api
```

### Authentication
Currently using session-based authentication. JWT implementation planned.

### Stock Movement Endpoints

#### **GET /api/stock-movements**
Get all stock movements with optional filters

**Query Parameters:**
- `type` - Movement type (in, out, transfer, adjustment)
- `warehouse_id` - Filter by warehouse
- `item_id` - Filter by inventory item
- `date_from` - Start date (YYYY-MM-DD)
- `date_to` - End date (YYYY-MM-DD)

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "item_name": "Portland Cement",
      "movement_type": "in",
      "quantity": 100,
      "from_warehouse": null,
      "to_warehouse": "Warehouse A",
      "performed_by_name": "John Doe",
      "created_at": "2025-10-31 10:30:00"
    }
  ],
  "count": 1
}
```

#### **POST /api/stock-movements/in**
Record stock IN transaction

**Request Body:**
```json
{
  "item_id": 1,
  "warehouse_id": 2,
  "quantity": 100,
  "reference": "PO-2025-001",
  "notes": "Purchase order delivery"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Stock IN recorded successfully",
  "movement_id": 15,
  "new_stock": 250
}
```

#### **POST /api/stock-movements/out**
Record stock OUT transaction

**Request Body:**
```json
{
  "item_id": 1,
  "warehouse_id": 2,
  "quantity": 50,
  "reference": "DO-2025-001",
  "notes": "Delivery to construction site"
}
```

#### **POST /api/stock-movements/transfer**
Transfer stock between warehouses

**Request Body:**
```json
{
  "item_id": 1,
  "from_warehouse_id": 2,
  "to_warehouse_id": 3,
  "quantity": 25,
  "reference": "TR-2025-001",
  "notes": "Transfer to new warehouse"
}
```

#### **POST /api/stock-movements/adjustment**
Adjust stock (corrections, damages)

**Request Body:**
```json
{
  "item_id": 1,
  "warehouse_id": 2,
  "quantity": -10,
  "reference": "ADJ-2025-001",
  "notes": "Damaged items"
}
```

#### **GET /api/stock-movements/stats**
Get movement statistics

**Query Parameters:**
- `date_from` - Start date (optional)
- `date_to` - End date (optional)

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "movement_type": "in",
      "transaction_count": 45,
      "total_quantity": 5230
    },
    {
      "movement_type": "out",
      "transaction_count": 32,
      "total_quantity": 3150
    }
  ]
}
```

#### **GET /api/stock-movements/item/{id}**
Get movement history for specific item

**Response:**
```json
{
  "status": "success",
  "data": [...],
  "count": 12
}
```

---

## 👥 User Roles & Permissions

### Role Hierarchy

| Role | Code | Permissions |
|------|------|------------|
| **Top Management** | `top_management` | View all reports, analytics |
| **Warehouse Manager** | `warehouse_manager` | Full inventory control, transfers, reports |
| **Inventory Auditor** | `inventory_auditor` | View all, adjustments, reports |
| **Warehouse Staff** | `warehouse_staff` | Stock IN/OUT, view inventory |
| **Procurement Officer** | `procurement_officer` | Create purchase orders, vendor management |
| **AP Clerk** | `accounts_payable_clerk` | Manage vendor invoices, payments |
| **AR Clerk** | `accounts_receivable_clerk` | Manage client invoices, receipts |
| **IT Administrator** | `it_administrator` | System configuration, user management |

### Permission Matrix

| Feature | Manager | Staff | Auditor | Procurement | AP Clerk | AR Clerk | IT Admin |
|---------|---------|-------|---------|-------------|----------|----------|----------|
| View Inventory | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Add/Edit Items | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Stock IN/OUT | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Transfers | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Adjustments | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ✅ |
| View AP | ✅ | ❌ | ✅ | ✅ | ✅ | ❌ | ✅ |
| Manage AP | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ✅ |
| View AR | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ | ✅ |
| Manage AR | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Reports | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 📊 Development Status

### Overall Progress: **~45%**

#### Phase 1: Foundation (100% Complete) ✅
- [x] Project setup
- [x] Database design
- [x] User authentication
- [x] Basic CRUD operations

#### Phase 2: Core Inventory (90% Complete) 🚧
- [x] Inventory management
- [x] Warehouse management
- [x] Category management
- [x] Stock movement tracking
- [ ] Barcode integration

#### Phase 3: Finance Modules (30% Complete) 🚧
- [x] Database schema (AP/AR)
- [x] Vendor/Client models
- [ ] AP Controller & API
- [ ] AR Controller & API
- [ ] Payment processing
- [ ] Invoice generation

#### Phase 4: Advanced Features (10% Complete) ⏳
- [ ] Real-time updates
- [ ] Advanced reporting
- [ ] PDF generation
- [ ] Email notifications
- [ ] API authentication (JWT)

#### Phase 5: Security & Optimization (20% Complete) ⏳
- [x] Basic RBAC
- [ ] Middleware filters
- [ ] Input sanitization
- [ ] XSS protection
- [ ] CSRF protection
- [ ] Rate limiting

---

## 🛠️ Technical Stack

### Backend
- **Framework:** CodeIgniter 4.x
- **Language:** PHP 8.1+
- **Database:** MySQL 8.0+
- **ORM:** CodeIgniter Query Builder

### Frontend (Views)
- **Template Engine:** CodeIgniter Views
- **CSS Framework:** Bootstrap 5 / Custom CSS
- **JavaScript:** Vanilla JS / jQuery

### Development Tools
- **Version Control:** Git
- **Server:** XAMPP/Apache
- **Package Manager:** Composer

---

## 📝 Next Steps

### Immediate Priorities (To reach 60-75%)

1. **Complete Finance API** (Week 1-2)
   - [ ] Accounts Payable Controller
   - [ ] Accounts Receivable Controller
   - [ ] Payment processing endpoints
   - [ ] Invoice CRUD operations

2. **Implement Real-time Updates** (Week 2-3)
   - [ ] AJAX for stock updates
   - [ ] Live inventory dashboard
   - [ ] Notification system

3. **Barcode Integration** (Week 3-4)
   - [ ] QR code generation
   - [ ] Barcode scanner integration
   - [ ] Quick lookup by scan

4. **Advanced Reporting** (Week 4-5)
   - [ ] Stock level reports
   - [ ] Financial reports
   - [ ] Audit trail reports
   - [ ] PDF export

5. **Security Enhancements** (Week 5-6)
   - [ ] Auth filters/middleware
   - [ ] JWT API authentication
   - [ ] Permission matrix enforcement
   - [ ] Input validation hardening

---

## 🤝 Contributing

### Development Workflow
1. Create feature branch
2. Make changes
3. Test thoroughly
4. Submit pull request

### Code Standards
- Follow PSR-12 coding standards
- Add PHPDoc comments to all functions
- Write meaningful commit messages
- Keep controllers thin, models fat

---

## 📞 Support

For questions or issues:
- Email: support@webuild.com
- GitHub Issues: [Create Issue]
- Documentation: This README

---

## 📄 License

This project is proprietary to WeBuild Construction Company.

---

## 🎯 Project Goals

**Target Completion:** 75% by Midterm Evaluation

**Current Status:** ~45% Complete

**Remaining Work:** ~30% to reach target

---

*Last Updated: October 31, 2025*
*Version: 0.45.0*
*Maintained by: Backend Development Team*
