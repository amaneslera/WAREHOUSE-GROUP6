# Database Sample Data

## Summary
The database has been successfully populated with sample data for testing the Warehouse Management system.

## Data Created

### 1. Users (UsersSeeder)
- **Total Records**: 8 users
- **Roles**:
  - 1 Warehouse Manager (manager@example.com)
  - 1 Warehouse Staff
  - 1 Inventory Auditor
  - 1 Procurement Officer
  - 1 Accounts Payable Clerk
  - 1 Accounts Receivable Clerk
  - 1 IT Administrator
  - 1 Top Management

**Note**: All users have password: (their role) + "123"
- Example: warehouse_manager password = "manager123"

### 2. Warehouses (WarehouseSeeder)
- **Total Records**: 3 warehouses
  - Building A (North Wing) - Capacity: 1000 units - Active
  - Building B (South Wing) - Capacity: 800 units - Active
  - Building C (East Wing) - Capacity: 600 units - Active

### 3. Categories (CategorySeeder)
- **Total Records**: 5 categories
  - Construction materials
  - Tools
  - Safety
  - Electrical
  - Equipment

### 4. Inventory Items (InventorySeeder)
- **Total Records**: 6 items

| Item ID | Item Name | Category | Warehouse | Stock | Min Stock | Unit Price | Status |
|---------|-----------|----------|-----------|-------|-----------|------------|--------|
| INV001 | Steel | Construction | Building A | 150 | 50 | $450.00 | Active |
| INV002 | Cement | Construction | Building B | 200 | 75 | $25.50 | Active |
| INV003 | Power Drill | Tools | Building A | 15 | 20 | $125.00 | Active |
| INV004 | Safety Helmet | Safety | Building C | 45 | 30 | $35.75 | Active |
| INV005 | Electrical Wire | Electrical | Building B | 0 | 100 | $2.75 | Active |
| INV006 | Forklift | Equipment | Building A | 3 | 2 | $25,000.00 | Active |

**Total Inventory Value**: ~$151,083.75

### 5. Stock Movements (StockMovementSeeder)
- **Total Records**: 7 movements

#### Pending Approvals (3)
1. **Steel Stock IN** (2 hours ago)
   - Quantity: 50 units → Building A
   - Performed by: Staff Member
   - Reference: PO-2025-001
   - Notes: New stock received from supplier

2. **Cement Stock IN** (1 hour ago)
   - Quantity: 75 units → Building B
   - Performed by: Staff Member
   - Reference: PO-2025-002
   - Notes: Cement delivery from vendor

3. **Electrical Wire Stock IN** (30 minutes ago)
   - Quantity: 150 units → Building B
   - Performed by: Staff Member
   - Reference: PO-2025-003
   - Notes: Emergency stock of electrical wire

#### Approved Movements (3)
1. **Power Drill Stock OUT** (1 day ago)
   - Quantity: 5 units from Building A
   - Approved by: Manager
   - Reference: DO-2025-001
   - Notes: Drills sent to Site A for project work

2. **Safety Helmet Stock OUT** (2 days ago)
   - Quantity: 20 units from Building C
   - Approved by: Manager
   - Reference: DO-2025-002
   - Notes: Safety helmets for new worker onboarding

3. **Steel Transfer** (3 days ago)
   - Quantity: 25 units from Building A → Building B
   - Approved by: Manager
   - Reference: TR-2025-001
   - Notes: Transfer to balance stock levels

#### Rejected Movements (1)
1. **Cement Stock OUT** (4 days ago)
   - Quantity: 100 units from Building B
   - Rejected by: Manager
   - Reference: DO-2025-003
   - Rejection Reason: Stock insufficient - only 200 available but requesting 100. Insufficient safety stock.

## Testing the APIs

### Manager Authentication
To test as a manager:
- **Email**: manager@example.com
- **Password**: manager123

### Staff Authentication
To test as a staff member:
- **Email**: staff@example.com
- **Password**: staff123

### Available API Endpoints
1. **GET /api/warehouses** - Get all warehouses with inventory summary
2. **GET /api/warehouses/:id** - Get specific warehouse details
3. **GET /api/approvals/pending** - Get pending approvals (Manager only)
4. **GET /api/approvals/stats** - Get approval statistics
5. **GET /api/approvals/history** - Get approval history
6. **POST /api/approvals/:id/approve** - Approve a movement
7. **POST /api/approvals/:id/reject** - Reject a movement
8. **GET /api/barcode/lookup?barcode=ID** - Look up item by barcode/ID
9. **POST /api/barcode/stock-in** - Record stock in
10. **POST /api/barcode/stock-out** - Record stock out

## Notes

- All timestamps are set to current time for consistent testing
- Foreign key relationships are properly maintained
- Stock levels have been set to create realistic scenarios:
  - Some items below minimum stock (Power Drill)
  - Some items with zero stock (Electrical Wire)
  - Pending movements to demonstrate approval workflow
- Approval workflow demonstrates all states: pending, approved, rejected

## Re-seeding Data

To reset all data and reseed from scratch:
```bash
php spark db:seed DatabaseSeeder
```

To seed individual tables:
```bash
php spark db:seed UsersSeeder
php spark db:seed WarehouseSeeder
php spark db:seed CategorySeeder
php spark db:seed InventorySeeder
php spark db:seed StockMovementSeeder
```
