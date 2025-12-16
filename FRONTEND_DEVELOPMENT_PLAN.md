# 📋 FRONTEND DEVELOPMENT PLAN
**CodeIgniter 4 Warehouse Inventory & Tracking Management System**

**Analysis Date**: December 16, 2025  
**Status**: Backend Complete ✅ | Frontend Partial ⚠️  
**Presentation**: TODAY

---

## 🔍 STEP 1: BACKEND ANALYSIS COMPLETE

### ✅ **AUTHENTICATION & SESSION MANAGEMENT**

**Authentication Controller**: `app/Controllers/AUTH.php`  
**Session Variables Available**:
```php
session('user_id')      // User ID
session('user_email')   // Email
session('user_role')    // Role name
session('user_fname')   // First name  
session('user_lname')   // Last name
session('user_mname')   // Middle name
session('logged_in')    // Boolean
```

**User Roles** (8 roles):
1. `warehouse_manager` → Redirects to `/inventory`
2. `warehouse_staff` → `/dashboard/staff`
3. `inventory_auditor` → `/dashboard/auditor`
4. `procurement_officer` → `/dashboard/procurement`
5. `accounts_payable_clerk` → `/dashboard/apclerk`
6. `accounts_receivable_clerk` → `/dashboard/arclerk`
7. `it_administrator` → `/dashboard/it`
8. `top_management` → `/dashboard/top`

**Permission Check Method**: Each controller has `checkPermission(array $allowedRoles)` method

---

### ✅ **API ENDPOINTS AVAILABLE**

#### **1. INVENTORY API** (`/api/inventory`)
**Controller**: `InventoryController.php` (593 lines)  
**Permissions**: `warehouse_manager`, `warehouse_staff`, `auditor`, `top_management`

| Method | Endpoint | Function | Permissions |
|--------|----------|----------|-------------|
| GET | `/api/inventory` | List all inventory (pagination, filters) | View roles |
| GET | `/api/inventory/{id}` | Get item details | View roles |
| POST | `/api/inventory` | Create item | Manager, Procurement |
| PUT | `/api/inventory/{id}` | Update item | Manager, Procurement |
| DELETE | `/api/inventory/{id}` | Delete item | Manager, IT Admin |

**Query Parameters for GET /api/inventory**:
- `warehouse_id` - Filter by warehouse
- `category_id` - Filter by category
- `status` - Filter by status (active/inactive)
- `low_stock` - Show only low stock (true)
- `page` - Page number
- `limit` - Items per page (default: 50)

**Response Format**:
```json
{
  "status": "success",
  "message": "Items retrieved successfully",
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 50,
    "total_items": 150,
    "total_pages": 3
  },
  "statistics": {
    "total_items": 150,
    "low_stock_count": 12,
    "total_value": 45678.90
  }
}
```

---

#### **2. STOCK MOVEMENTS API** (`/api/stock-movements`)
**Controller**: `StockMovementController.php` (660+ lines)  
**Permissions**: `warehouse_manager`, `warehouse_staff`, `inventory_auditor`

| Method | Endpoint | Function | Permissions |
|--------|----------|----------|-------------|
| GET | `/api/stock-movements` | List movements with filters | All warehouse roles |
| GET | `/api/stock-movements/{id}` | Get movement details | All warehouse roles |
| GET | `/api/stock-movements/stats` | Movement statistics | Manager, Auditor, Top Mgmt |
| GET | `/api/stock-movements/item/{id}` | Item movement history | All warehouse roles |
| POST | `/api/stock-movements/in` | Stock IN | Manager, Staff |
| POST | `/api/stock-movements/out` | Stock OUT | Manager, Staff |
| POST | `/api/stock-movements/transfer` | Transfer between warehouses | Manager, Staff |
| POST | `/api/stock-movements/adjustment` | Stock adjustment | Manager, Auditor |

---

#### **3. WAREHOUSE API** (`/api/warehouses`)
**Controller**: `WarehouseController.php`  
**Permissions**: Session-based (all authenticated users)

| Method | Endpoint | Function |
|--------|----------|----------|
| GET | `/api/warehouses` | List all warehouses with inventory summary |
| GET | `/api/warehouses/{id}` | Get specific warehouse details |

**Response includes**:
- Warehouse info (name, location, capacity, status)
- Item count per warehouse
- Total inventory value per warehouse

---

#### **4. APPROVALS API** (`/api/approvals`)
**Controller**: `StockApprovalController.php` (262 lines)  
**Permissions**: Session-based (warehouse_manager primarily)

| Method | Endpoint | Function |
|--------|----------|----------|
| GET | `/api/approvals/pending` | Get pending approvals |
| GET | `/api/approvals/{id}` | Get movement details for review |
| GET | `/api/approvals/stats` | Approval statistics (pending/approved/rejected) |
| GET | `/api/approvals/history` | Approval audit trail |
| POST | `/api/approvals/{id}/approve` | Approve movement |
| POST | `/api/approvals/{id}/reject` | Reject movement |

---

#### **5. BARCODE/SCANNER API** (`/api/barcode`)
**Controller**: `BarcodeController.php` (263 lines)  
**Permissions**: Session-based

| Method | Endpoint | Function |
|--------|----------|----------|
| GET | `/api/barcode/lookup?barcode={code}` | Find item by barcode |
| GET | `/api/barcode/{id}` | Get item with warehouse details |
| GET | `/api/barcode/search?term={query}` | Search items by name |
| GET | `/api/barcode/qr/{id}` | Generate QR code for item |
| POST | `/api/barcode/stock-in` | Record stock IN via scanner |
| POST | `/api/barcode/stock-out` | Record stock OUT via scanner |

---

#### **6. ACCOUNTS RECEIVABLE API** (`/api/accounts-receivable`) 🔴 NO UI
**Controller**: `AccountsReceivableController.php` (691 lines)  
**Permissions**: `accounts_receivable_clerk`, `top_management`, `it_administrator`

| Method | Endpoint | Function | Permissions |
|--------|----------|----------|-------------|
| GET | `/api/accounts-receivable` | List invoices (pagination) | AR Clerk, Top Mgmt, IT |
| GET | `/api/accounts-receivable/{id}` | Invoice details | AR Clerk, Top Mgmt, IT |
| GET | `/api/accounts-receivable/overdue` | Overdue invoices | AR Clerk, Top Mgmt |
| GET | `/api/accounts-receivable/outstanding` | Outstanding balances | AR Clerk, Top Mgmt |
| GET | `/api/accounts-receivable/stats` | AR statistics | AR Clerk, Top Mgmt |
| GET | `/api/accounts-receivable/{id}/history` | Payment history | AR Clerk, Top Mgmt |
| POST | `/api/accounts-receivable` | Create invoice | AR Clerk only |
| POST | `/api/accounts-receivable/{id}/payment` | Record payment | AR Clerk only |
| PUT | `/api/accounts-receivable/{id}` | Update invoice | AR Clerk only |
| DELETE | `/api/accounts-receivable/{id}` | Cancel invoice | AR Clerk, IT Admin |

---

#### **7. REPORTS API** (`/api/reports`) 🔴 NO UI
**Controller**: `ReportsController.php` (732 lines)  

**Inventory Reports** (Permissions: Manager, Top Mgmt, Auditor, IT):
| Endpoint | Function |
|----------|----------|
| `/api/reports/inventory/summary` | Stock summary by warehouse |
| `/api/reports/inventory/low-stock` | Low stock items with filters |
| `/api/reports/inventory/movements` | Movement history with filters |

**AR Reports** (Permissions: AR Clerk, Top Mgmt, Auditor, IT):
| Endpoint | Function |
|----------|----------|
| `/api/reports/ar/outstanding` | Outstanding AR by client |
| `/api/reports/ar/aging` | AR aging analysis (0-30, 31-60, 61-90, 90+ days) |
| `/api/reports/ar/history` | AR payment history |

**AP Reports** (Permissions: AP Clerk, Top Mgmt, Auditor, IT):
| Endpoint | Function |
|----------|----------|
| `/api/reports/ap/outstanding` | Outstanding AP by vendor |
| `/api/reports/ap/aging` | AP aging analysis |
| `/api/reports/ap/history` | AP payment history |

**Warehouse Reports** (Permissions: Manager, Top Mgmt, Auditor, IT):
| Endpoint | Function |
|----------|----------|
| `/api/reports/warehouse/usage` | Warehouse utilization dashboard |

---

## 🎨 EXISTING FRONTEND VIEWS

### ✅ **COMPLETE & WORKING**
1. **Login/Auth** - `app/Views/auth/login.php` ✅
   - Bootstrap 5.3.3
   - Session-based authentication
   - Role-based redirection

2. **Warehouse Manager Dashboard** - `app/Views/dashboard/manager/approvals.php` ✅
   - 569 lines - FULLY FUNCTIONAL
   - Bootstrap 5.3.0
   - Font Awesome 6.0.0
   - Tabbed interface (Dashboard, Inventory, Approvals, Movements, Reports, Warehouses)
   - AJAX/Fetch API for all data
   - Modal for approvals
   - **This is presentation-ready** ⭐

3. **Staff Barcode Scanner** - `app/Views/dashboard/staff/scanner.php` ✅
   - Barcode scanning interface
   - Stock IN/OUT functionality
   - Movement history
   - **This is presentation-ready** ⭐

---

### ⚠️ **BASIC VIEWS (Need Modernization)**
1. **AP Clerk Dashboards** - `app/Views/dashboard/accounts_payable/`
   - `invoice_management.php` - Basic table view
   - `payment_recording.php` - Form view
   - `supplier_management.php` - Basic CRUD
   - **Status**: HTML forms, no AJAX, old design

2. **Stock Movement Forms** - `app/Views/stock_movements/`
   - `stock_in.php`, `stock_out.php`, `transfer.php`, `index.php`
   - **Status**: Form-based, not API-integrated

3. **Inventory Manager Views** - `app/Views/dashboard/manager/`
   - `index.php` - Basic dashboard
   - `add.php`, `edit.php`, `view.php` - CRUD forms
   - **Status**: Basic forms, needs API integration

---

### 🔴 **PLACEHOLDER VIEWS (No Functionality)**
These exist but are just empty placeholders:
- `app/Views/dashboard/receivable.php` - AR Clerk (placeholder)
- `app/Views/dashboard/auditor.php` - Auditor (placeholder)
- `app/Views/dashboard/itadmin.php` - IT Admin (placeholder)
- `app/Views/dashboard/topmanagement.php` - Top Management (placeholder)
- `app/Views/dashboard/staff.php` - Staff (placeholder)
- `app/Views/dashboard/procurement.php` - Procurement (placeholder)
- `app/Views/dashboard/payable.php` - AP Clerk (placeholder)

**Structure**: All have Bootstrap 5.3.0 navbar and basic "coming soon" content

---

## 🎯 STEP 2: UI DEVELOPMENT PRIORITY

### **FOR TODAY'S PRESENTATION** (4-6 hours)

#### **Option A: Demo Existing (Recommended - 0 hours)**
✅ Show Manager Approvals (fully functional)  
✅ Show Staff Scanner (fully functional)  
⚠️ Show APIs via Postman/test page

#### **Option B: Quick AR Dashboard (4-6 hours)**
Build minimal AR Clerk dashboard:
1. Invoice list table with filters
2. Payment recording modal
3. Basic stats cards
4. Links to existing APIs

#### **Option C: Reports Dashboard (3-4 hours)**
Build central reports viewer:
1. Tabbed interface for each report type
2. Tables displaying API data
3. Date range pickers
4. Export placeholders

---

### **RECOMMENDED UI STRUCTURE**

#### **Layout Template** (Create Once, Reuse Everywhere)
```
app/Views/layouts/
  ├── main.php              (Master layout)
  ├── navbar.php            (Top navigation)
  ├── sidebar.php           (Side menu - role-based)
  └── footer.php            (Footer)
```

**Features**:
- Bootstrap 5.3.0
- Font Awesome 6.0.0
- Session-based user info
- Role-based menu items
- Responsive design

---

#### **Dashboard Components** (Reusable)
```
app/Views/components/
  ├── stat_card.php         (KPI cards)
  ├── data_table.php        (Generic table with filters)
  ├── modal_form.php        (CRUD modals)
  ├── alert.php             (Success/error messages)
  └── loading_spinner.php   (Loading states)
```

---

## 📐 STEP 3: PROPOSED PAGE STRUCTURE

### **1. ACCOUNTS RECEIVABLE MODULE** 🔴 CRITICAL

#### **Page 1: AR Dashboard** (`app/Views/dashboard/accounts_receivable/index.php`)
**Route**: `/dashboard/arclerk`  
**API**: `/api/accounts-receivable/stats`, `/api/accounts-receivable/overdue`

**Layout**:
```
┌─────────────────────────────────────────────┐
│  🧾 Accounts Receivable Dashboard          │
├─────────────────────────────────────────────┤
│ [Total Outstanding] [Overdue] [Paid] [Avg] │ ← Stat cards
├─────────────────────────────────────────────┤
│ 🔴 Overdue Invoices (Quick View)           │
│ [Table: Client, Invoice, Amount, Days]     │
├─────────────────────────────────────────────┤
│ 📋 Recent Invoices                          │
│ [Filters: Client | Status | Date Range]    │
│ [Table with: ID, Client, Amount, Status]   │
│ [Actions: View | Payment | Edit]           │
└─────────────────────────────────────────────┘
```

**Modals**:
- Create Invoice Modal
- Record Payment Modal
- View Invoice Details Modal

#### **Page 2: AR Reports** (`app/Views/dashboard/accounts_receivable/reports.php`)
**Route**: `/dashboard/arclerk/reports`  
**API**: `/api/reports/ar/outstanding`, `/api/reports/ar/aging`

**Tabs**:
- Outstanding Balances (by client)
- Aging Analysis (table with color-coded days)
- Payment History

---

### **2. REPORTS CENTRAL** 🔴 CRITICAL

#### **Reports Dashboard** (`app/Views/reports/index.php`)
**Route**: `/reports`  
**Permissions**: Role-based access to sections

**Layout**:
```
┌─────────────────────────────────────────────┐
│  📊 Reports Dashboard                       │
├─────────────────────────────────────────────┤
│ [Inventory] [AR] [AP] [Warehouse] [Audit]  │ ← Tab navigation
├─────────────────────────────────────────────┤
│  INVENTORY TAB:                             │
│  • Stock Summary by Warehouse              │
│  • Low Stock Alerts                        │
│  • Movement History                        │
│  [Date Range] [Warehouse Filter] [Export] │
│  [Data Table]                              │
└─────────────────────────────────────────────┘
```

---

### **3. INVENTORY MANAGEMENT** (Modernize Existing)

#### **Inventory List** (`app/Views/inventory/index.php`)
**Route**: `/inventory`  
**API**: `/api/inventory`

**Features**:
- Responsive table with search/filter
- Pagination controls
- Low stock indicators (badge)
- AJAX CRUD via modals
- Image thumbnails
- Export to CSV/PDF (placeholder)

---

### **4. WAREHOUSE MANAGEMENT** 🔴 NEW

#### **Warehouse List** (`app/Views/warehouses/index.php`)
**Route**: `/warehouses`  
**API**: `/api/warehouses`

**Layout**:
```
┌─────────────────────────────────────────────┐
│  🏢 Warehouse Management                    │
├─────────────────────────────────────────────┤
│ [Card: Warehouse A - 75% capacity]         │
│   Items: 120 | Value: $45,678             │
│   [View Details]                           │
├─────────────────────────────────────────────┤
│ [Card: Warehouse B - 45% capacity]         │
│   Items: 85 | Value: $23,456              │
│   [View Details]                           │
└─────────────────────────────────────────────┘
```

---

### **5. TOP MANAGEMENT DASHBOARD** (Modernize)

#### **Executive Dashboard** (`app/Views/dashboard/top_management/index.php`)
**Route**: `/dashboard/top`  
**API**: Multiple report APIs

**Layout**:
```
┌─────────────────────────────────────────────┐
│  📈 Executive Dashboard                     │
├─────────────────────────────────────────────┤
│ [Total Inventory Value] [AR Outstanding]   │
│ [AP Outstanding] [Low Stock Alerts]        │
├─────────────────────────────────────────────┤
│  📊 Charts:                                 │
│  • Inventory Value by Warehouse (Bar)      │
│  • AR vs AP Trend (Line)                   │
│  • Stock Movement Summary (Pie)            │
├─────────────────────────────────────────────┤
│  🔗 Quick Links:                            │
│  [View Full Reports] [AR Aging] [AP Aging] │
└─────────────────────────────────────────────┘
```

---

## 🛠️ STEP 4: TECHNICAL IMPLEMENTATION

### **JavaScript Architecture**

#### **API Helper** (`public/js/api-helper.js`)
```javascript
const API_BASE = '/WAREHOUSE-GROUP6/api';

async function apiGet(endpoint, params = {}) {
    const url = new URL(`${window.location.origin}${API_BASE}${endpoint}`);
    Object.keys(params).forEach(key => 
        url.searchParams.append(key, params[key])
    );
    
    const response = await fetch(url, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    });
    
    if (!response.ok) {
        throw new Error(`API Error: ${response.status}`);
    }
    
    return await response.json();
}

async function apiPost(endpoint, data) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    
    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'API Error');
    }
    
    return await response.json();
}

// Similar for apiPut, apiDelete
```

#### **UI Components** (`public/js/components.js`)
```javascript
// Show loading spinner
function showLoading(containerId) {
    document.getElementById(containerId).innerHTML = 
        '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
}

// Show alert
function showAlert(message, type = 'success') {
    const alert = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    document.getElementById('alertContainer').innerHTML = alert;
    setTimeout(() => document.querySelector('.alert')?.remove(), 4000);
}

// Format currency
function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
}

// Format date
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US');
}
```

---

### **CSS Framework**

**Use Existing**: Bootstrap 5.3.0 (already loaded)  
**Icons**: Font Awesome 6.0.0 (already loaded)  
**Custom CSS**: Minimal, only for branding

**Custom Styles** (`public/css/custom.css`):
```css
:root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
}

.stat-card {
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.badge-overdue {
    background-color: var(--danger-color);
}

.badge-pending {
    background-color: var(--warning-color);
}

.badge-paid {
    background-color: var(--success-color);
}
```

---

## ✅ STEP 5: IMPLEMENTATION CHECKLIST

### **Phase 1: Layout & Components** (1-2 hours)
- [ ] Create `app/Views/layouts/main.php` master layout
- [ ] Create `app/Views/components/` reusable components
- [ ] Create `public/js/api-helper.js` API wrapper
- [ ] Create `public/js/components.js` UI helpers
- [ ] Create `public/css/custom.css` styling

### **Phase 2: AR Module** (3-4 hours)
- [ ] `app/Views/dashboard/accounts_receivable/index.php`
- [ ] Invoice list table with AJAX
- [ ] Create invoice modal
- [ ] Record payment modal
- [ ] Stats cards from API
- [ ] Test with API endpoints

### **Phase 3: Reports Dashboard** (2-3 hours)
- [ ] `app/Views/reports/index.php`
- [ ] Tabbed interface (Inventory, AR, AP, Warehouse)
- [ ] Data tables for each report
- [ ] Date range pickers
- [ ] Export placeholders

### **Phase 4: Modernize Existing** (2-3 hours)
- [ ] Upgrade inventory list to use `/api/inventory`
- [ ] Add AJAX to stock movement forms
- [ ] Modernize warehouse view

### **Phase 5: Top Management Dashboard** (1-2 hours)
- [ ] Executive summary cards
- [ ] Chart placeholders (Chart.js integration)
- [ ] Quick links to reports

---

## 🚀 READY TO PROCEED?

**Current State**:
✅ Backend APIs: 100% Complete (38+ endpoints)  
✅ Authentication: Working  
✅ Manager/Staff UI: Presentation-ready  
⚠️ AR Module: 0% UI (API ready)  
⚠️ Reports: 0% UI (API ready)  
⚠️ Other roles: Basic placeholders  

**Recommendation for TODAY's Presentation**:

**OPTION 1** (No dev time):  
- Present Manager Approval System (fully functional)  
- Present Staff Scanner (fully functional)  
- Show API capabilities via Postman  
- Explain that "backend is 100% complete, UI layers can be added as needed"

**OPTION 2** (4-6 hours):  
Build **AR Clerk Dashboard** to demonstrate another complete module

**OPTION 3** (3-4 hours):  
Build **Reports Dashboard** to showcase analytics capabilities

---

## 📞 NEXT STEPS

**Please confirm**:
1. Which option do you prefer for today's presentation?
2. If building UI - which module should I prioritize?
3. Shall I proceed with creating the layout template first?

**I'm ready to start building as soon as you give the go-ahead!** 🚀
