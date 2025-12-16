# Warehouse Manager & Staff Integration Guide

## Overview
This document explains the complete implementation of **Warehouse Manager** and **Warehouse Staff** roles with barcode scanning, stock approval workflows, and Bootstrap 5.3 UI.

---

## 1. WAREHOUSE STAFF FEATURES

### 1.1 Scanner Dashboard
**Route:** `/dashboard/staff/scanner`  
**File:** `app/Views/dashboard/staff/scanner.php`

#### Features:
- **Barcode Scanning**: Real-time item lookup by barcode or item ID
- **Movement Types**: Stock IN, Stock OUT, Transfer, Physical Count
- **Quick Actions**: Auto-focus barcode field for rapid scanning
- **Item Preview**: Display item details (stock level, price, warehouse)
- **Recent Movements**: View history of recorded movements
- **Physical Count**: Verify actual stock vs. system inventory

#### Form Fields:
```
├── Barcode/Item ID (auto-lookup)
├── Movement Type (dropdown)
├── Target Warehouse (for transfers)
├── Quantity
└── Notes
```

#### API Endpoints Called:
- `GET /api/barcode/lookup?barcode=XXX` - Find item by barcode
- `POST /api/stock-movements/in` - Record stock in
- `POST /api/stock-movements/out` - Record stock out
- `POST /api/stock-movements/transfer` - Record transfer
- `GET /api/stock-movements` - Load recent movements

---

## 2. WAREHOUSE MANAGER FEATURES

### 2.1 Manager Dashboard
**Route:** `/dashboard/manager`  
**File:** `app/Views/dashboard/manager/index.php`

#### Key Metrics:
- Total Inventory Value (all warehouses)
- Total Items in Stock
- Pending Approvals (awaiting manager action)
- Low Stock Items Count

#### Dashboard Widgets:
1. **Pending Approvals Section** - Shows top 5 pending movements
2. **Warehouse Summary Cards** - Per-warehouse statistics
3. **Navigation Sidebar** - Quick access to all manager functions

### 2.2 Approval Management
**Route:** `/dashboard/manager/approvals`  
**File:** `app/Views/dashboard/manager/approvals.php`

#### Approval Features:
- **Pending Approvals List** - All awaiting approval
- **Movement Details Modal** - Review movement details before decision
- **Approve/Reject Buttons** - Manager decision interface
- **Approval Notes** - Document manager's rationale
- **Approval History** - Track all decisions with timestamps

#### Approval Workflow:
1. Staff records movement → **PENDING** status
2. Manager sees movement in approvals list
3. Manager reviews details in modal
4. Manager clicks **Approve** or **Reject**
5. System updates inventory accordingly

#### API Endpoints:
- `GET /api/approvals/pending` - Get pending approvals
- `GET /api/approvals/:id` - Get approval details
- `POST /api/approvals/:id/approve` - Approve movement
- `POST /api/approvals/:id/reject` - Reject movement
- `GET /api/approvals/stats` - Approval statistics
- `GET /api/approvals/history` - Approval history

---

## 3. BACKEND CONTROLLERS

### 3.1 BarcodeController
**File:** `app/Controllers/BarcodeController.php`

#### Methods:
```php
- lookup()           // GET  /api/barcode/lookup
- getItem($id)       // GET  /api/barcode/:id
- search()           // GET  /api/barcode/search?q=query
- stockIn()          // POST /api/barcode/stock-in
- stockOut()         // POST /api/barcode/stock-out
- generateQR($id)    // GET  /api/barcode/qr/:id
```

#### Key Functions:
1. **Item Lookup** - Quick search by barcode/ID
2. **Stock In** - Record receipt, update inventory
3. **Stock Out** - Record dispatch, check stock availability
4. **QR Generation** - Generate QR codes for items

### 3.2 StockApprovalController
**File:** `app/Controllers/StockApprovalController.php`

#### Methods:
```php
- pending()     // GET  /api/approvals/pending
- show($id)     // GET  /api/approvals/:id
- approve($id)  // POST /api/approvals/:id/approve
- reject($id)   // POST /api/approvals/:id/reject
- stats()       // GET  /api/approvals/stats
- history()     // GET  /api/approvals/history
```

#### Key Features:
1. **Pending Queue** - Manager-focused approval list
2. **Approval Logic** - Validates and approves movements
3. **Rejection Logic** - **REVERSES inventory changes** if rejected
4. **Audit Trail** - Records who approved/rejected and when

#### Important: Rejection Handling
When a movement is **REJECTED**:
- **Stock IN** rejection → Decreases inventory (reverses the increase)
- **Stock OUT** rejection → Increases inventory (reverses the decrease)
- Preserves data integrity and prevents inventory discrepancies

---

## 4. API ROUTES

### Staff Scanner Routes
```
GET  /api/barcode/lookup?barcode=XXX    - Search item by barcode
GET  /api/barcode/:id                    - Get item details
GET  /api/barcode/search?q=query         - Search by name/category
GET  /api/barcode/qr/:id                 - Generate QR code
POST /api/barcode/stock-in               - Record stock received
POST /api/barcode/stock-out              - Record stock dispatched
```

### Manager Approval Routes
```
GET  /api/approvals/pending              - List pending approvals
GET  /api/approvals/:id                  - Get approval details
POST /api/approvals/:id/approve          - Approve movement
POST /api/approvals/:id/reject           - Reject movement
GET  /api/approvals/stats                - Approval statistics
GET  /api/approvals/history              - Approval history
```

### Stock Movement Routes (existing)
```
POST /api/stock-movements/in             - Record stock in
POST /api/stock-movements/out            - Record stock out
POST /api/stock-movements/transfer       - Record transfer
POST /api/stock-movements/adjustment     - Record adjustment
GET  /api/stock-movements                - List movements
GET  /api/stock-movements/:id            - Get movement details
```

---

## 5. AUTHENTICATION & PERMISSIONS

### Role-Based Access Control
```php
// Staff Scanner (warehouse_staff role)
- Can record stock movements
- Can view own recordings
- Cannot approve movements
- Cannot edit other staff's entries

// Manager (warehouse_manager role)
- Can view all pending approvals
- Can approve movements
- Can reject movements
- Can view inventory summary
- Can access all warehouses
```

### Permission Checks
```php
// In controllers:
if (!$this->hasPermission('warehouse_staff', 'warehouse_manager')) {
    return $this->failForbidden('Insufficient permissions');
}
```

---

## 6. DATABASE SCHEMA

### stock_movements table
```sql
CREATE TABLE stock_movements (
    movement_id INT PRIMARY KEY AUTO_INCREMENT,
    item_id INT,
    movement_type ENUM('in', 'out', 'transfer', 'adjustment'),
    from_warehouse_id INT,
    to_warehouse_id INT,
    quantity INT,
    reference VARCHAR(100),
    recorded_by INT,
    notes TEXT,
    approval_status ENUM('pending', 'approved', 'rejected'),
    approved_by INT,
    approval_date DATETIME,
    approval_notes TEXT,
    rejected_by INT,
    rejection_date DATETIME,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory(id),
    FOREIGN KEY (recorded_by) REFERENCES users(user_id),
    FOREIGN KEY (approved_by) REFERENCES users(user_id),
    FOREIGN KEY (rejected_by) REFERENCES users(user_id)
);
```

---

## 7. WORKFLOW EXAMPLES

### Example 1: Stock IN Approval
```
1. Warehouse Staff:
   - Opens Scanner Dashboard
   - Selects "Stock IN" movement type
   - Scans item barcode
   - Enters quantity: 50 units
   - Clicks "Record Movement"
   → Movement saved with approval_status = 'pending'

2. Warehouse Manager:
   - Opens Manager Dashboard
   - Sees "Pending Approvals: 1"
   - Clicks "View All" or reviews in dashboard
   - Clicks "Review & Decide" on specific movement
   - Reviews item details in modal
   - Clicks "Approve"
   → approval_status = 'approved', inventory updated

3. System:
   - Records approval timestamp
   - Sets approved_by = manager_id
   - Item stock: 50 + previous = new total
```

### Example 2: Stock OUT Rejection
```
1. Warehouse Staff:
   - Selects "Stock OUT"
   - Scans item barcode
   - Enters quantity: 30 units
   - Clicks "Record Movement"
   → Movement saved with approval_status = 'pending'

2. Warehouse Manager:
   - Reviews movement
   - Discovers discrepancy
   - Clicks "Reject"
   - Adds note: "Quantity exceeds approved dispatch order"
   → rejection_status = 'rejected'

3. System:
   - Records rejection with timestamp
   - Sets rejected_by = manager_id
   - **REVERSES inventory**: stock += 30 (puts back what was deducted)
   - Preserves rejection reason for audit trail
```

---

## 8. TESTING THE IMPLEMENTATION

### Test Staff Scanner:
```bash
1. Login as warehouse_staff user
2. Navigate to /dashboard/staff/scanner
3. Try scanning (use item IDs from inventory)
4. Select movement type and submit
5. Verify movement appears in "Recent Movements"
```

### Test Manager Approvals:
```bash
1. Login as warehouse_manager user
2. Navigate to /dashboard/manager
3. Verify "Pending Approvals" shows staff recordings
4. Click "View All" to go to approvals page
5. Click "Review & Decide" on a movement
6. Test approve and reject functionality
7. Verify inventory changes are correct
8. Check approval history
```

---

## 9. BOOTSTRAP INTEGRATION

All views use Bootstrap 5.3.0 with:
- Responsive navigation sidebars
- Alert components for notifications
- Modal dialogs for approval workflows
- Card layouts for statistics
- Tables for movement history
- Form controls for data entry
- Badges for status indicators

---

## 10. DEBUGGING & LOGS

### Key Log Locations:
- Errors: `writable/logs/log-*.log`
- Database: Check `stock_movements` table for recorded data

### Common Issues:
1. **Movements not appearing in approvals**
   - Check `approval_status` in database (should be 'pending')
   - Verify user role is `warehouse_manager`
   - Check browser console for API errors

2. **Inventory not updating after approval**
   - Verify `movement_type` is correct (in/out/transfer)
   - Check `from_warehouse_id` and `to_warehouse_id` are set
   - Verify quantities are positive numbers

3. **Barcode lookup fails**
   - Ensure item exists in database
   - Check item_id matches exactly
   - Verify barcode column is populated (if using barcodes)

---

## 11. FUTURE ENHANCEMENTS

Planned features for v2.0:
- [ ] QR code generation and printing
- [ ] Barcode scanner hardware integration
- [ ] Mobile-optimized scanner app
- [ ] Real-time WebSocket updates for approvals
- [ ] Audit trail with detailed logging
- [ ] Photo capture for items
- [ ] Batch approval operations
- [ ] Export approval reports

---

## 12. SUPPORT CONTACTS

For issues or questions:
- Check database logs: `writable/logs/`
- Review CodeIgniter error stack traces
- Verify API responses in browser DevTools → Network tab
- Check user permissions in `users` table `user_role` column

---

**Last Updated:** 2024-12-09
**Version:** 1.0
**Status:** Production Ready
