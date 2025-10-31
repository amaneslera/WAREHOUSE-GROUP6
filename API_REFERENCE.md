# WITMS API Quick Reference

## 📡 Base Information

**Base URL:** `http://localhost:8080/api`

**Authentication:** Session-based (JWT planned)

**Content-Type:** `application/json`

**Date Format:** `YYYY-MM-DD` or `YYYY-MM-DD HH:MM:SS`

---

## 🔐 Authentication Endpoints

### POST /login
Login to the system
```json
{
  "email": "manager@webuild.com",
  "password": "password123"
}
```

### GET /logout
Logout from the system

---

## 📦 Stock Movement API

### GET /api/stock-movements
Get all movements with filters

**Query Params:**
- `type`: in|out|transfer|adjustment
- `warehouse_id`: integer
- `item_id`: integer
- `date_from`: YYYY-MM-DD
- `date_to`: YYYY-MM-DD

**Example:**
```
GET /api/stock-movements?type=in&warehouse_id=2&date_from=2025-10-01
```

**Response:**
```json
{
  "status": "success",
  "data": [...],
  "count": 25
}
```

### POST /api/stock-movements/in
Record stock IN (receiving)

**Body:**
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

### POST /api/stock-movements/out
Record stock OUT (dispatch)

**Body:**
```json
{
  "item_id": 1,
  "warehouse_id": 2,
  "quantity": 50,
  "reference": "DO-2025-001",
  "notes": "Delivery to Site A"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Stock OUT recorded successfully",
  "movement_id": 16,
  "new_stock": 200
}
```

### POST /api/stock-movements/transfer
Transfer between warehouses

**Body:**
```json
{
  "item_id": 1,
  "from_warehouse_id": 2,
  "to_warehouse_id": 3,
  "quantity": 25,
  "reference": "TR-2025-001",
  "notes": "Rebalancing stock"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Transfer completed successfully",
  "movement_id": 17,
  "source_stock": 175,
  "destination_stock": 25
}
```

### POST /api/stock-movements/adjustment
Stock adjustment (corrections)

**Body:**
```json
{
  "item_id": 1,
  "warehouse_id": 2,
  "quantity": -10,
  "reference": "ADJ-2025-001",
  "notes": "Damaged items removed"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Stock adjustment recorded successfully",
  "movement_id": 18,
  "old_stock": 175,
  "adjustment": -10,
  "new_stock": 165
}
```

### GET /api/stock-movements/stats
Get movement statistics

**Query Params:**
- `date_from`: YYYY-MM-DD (optional)
- `date_to`: YYYY-MM-DD (optional)

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

### GET /api/stock-movements/item/{id}
Get movement history for item

**Example:**
```
GET /api/stock-movements/item/1
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 15,
      "movement_type": "in",
      "quantity": 100,
      "reference": "PO-2025-001",
      "created_at": "2025-10-31 10:30:00"
    }
  ],
  "count": 12
}
```

---

## 🏭 Inventory API (Planned)

### GET /api/inventory
Get all inventory items

### GET /api/inventory/{id}
Get specific item

### POST /api/inventory
Create new item

### PUT /api/inventory/{id}
Update item

### DELETE /api/inventory/{id}
Delete item

### GET /api/inventory/low-stock
Get items below minimum stock

---

## 💰 Accounts Payable API (Planned)

### GET /api/ap
Get all AP invoices

### POST /api/ap
Create new AP invoice

### POST /api/ap/{id}/payment
Record payment

### GET /api/ap/overdue
Get overdue invoices

---

## 💵 Accounts Receivable API (Planned)

### GET /api/ar
Get all AR invoices

### POST /api/ar
Create new AR invoice

### POST /api/ar/{id}/payment
Record receipt

### GET /api/ar/overdue
Get overdue invoices

---

## ❌ Error Responses

### 400 Bad Request
```json
{
  "status": "error",
  "message": "Missing required fields: item_id, warehouse_id"
}
```

### 403 Forbidden
```json
{
  "status": "error",
  "message": "Unauthorized access"
}
```

### 404 Not Found
```json
{
  "status": "error",
  "message": "Inventory item not found"
}
```

### 500 Internal Server Error
```json
{
  "status": "error",
  "message": "Transaction failed",
  "errors": [...]
}
```

---

## 🧪 Testing with cURL

### Stock IN Example
```bash
curl -X POST http://localhost:8080/api/stock-movements/in \
  -H "Content-Type: application/json" \
  -d '{
    "item_id": 1,
    "warehouse_id": 2,
    "quantity": 100,
    "reference": "PO-2025-001",
    "notes": "Test delivery"
  }'
```

### Get Movements Example
```bash
curl -X GET "http://localhost:8080/api/stock-movements?type=in&warehouse_id=2"
```

### Transfer Example
```bash
curl -X POST http://localhost:8080/api/stock-movements/transfer \
  -H "Content-Type: application/json" \
  -d '{
    "item_id": 1,
    "from_warehouse_id": 2,
    "to_warehouse_id": 3,
    "quantity": 25,
    "reference": "TR-2025-001"
  }'
```

---

## 📝 Testing with Postman

1. **Import Collection:** Create new collection "WITMS API"
2. **Set Base URL:** Configure environment variable
3. **Add Requests:** Create requests for each endpoint
4. **Test Scenarios:**
   - Stock IN → Verify quantity increase
   - Stock OUT → Verify quantity decrease
   - Transfer → Verify both warehouses update
   - Adjustment → Verify correction applied

---

## 🔑 Permission Requirements

| Endpoint | Required Roles |
|----------|---------------|
| GET /api/stock-movements | manager, auditor, staff, top_management |
| POST /api/stock-movements/in | manager, staff |
| POST /api/stock-movements/out | manager, staff |
| POST /api/stock-movements/transfer | manager, staff |
| POST /api/stock-movements/adjustment | manager, auditor |
| GET /api/stock-movements/stats | manager, auditor, top_management |

---

*Last Updated: October 31, 2025*
