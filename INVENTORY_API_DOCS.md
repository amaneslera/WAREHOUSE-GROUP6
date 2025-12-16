# Inventory Module - RESTful API Documentation

## Overview
Complete CRUD API for Inventory Management System with JSON responses.

**Base URL:** `/api/inventory`

**Authentication:** Session-based (user must be logged in)

**Response Format:** JSON

---

## API Endpoints

### 1. List All Inventory Items
**GET** `/api/inventory`

Retrieve all inventory items with optional filtering and pagination.

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `warehouse_id` | int | No | Filter by warehouse ID |
| `category_id` | int | No | Filter by category ID |
| `status` | string | No | Filter by status (`active`, `inactive`) |
| `low_stock` | boolean | No | Show only low stock items (`true`/`false`) |
| `page` | int | No | Page number (default: 1) |
| `limit` | int | No | Items per page (default: 50) |

#### Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Inventory items retrieved successfully",
  "data": [
    {
      "id": 1,
      "item_id": "ITEM-001",
      "item_name": "Product A",
      "category_id": 1,
      "category_name": "Electronics",
      "warehouse_id": 1,
      "warehouse_name": "Main Warehouse",
      "current_stock": 150,
      "minimum_stock": 20,
      "unit_price": 50.00,
      "unit_of_measure": "pcs",
      "description": "Product description",
      "status": "active",
      "created_at": "2025-11-01 10:00:00",
      "updated_at": "2025-11-06 14:30:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 50,
    "total_items": 150,
    "total_pages": 3
  },
  "statistics": {
    "total_items": 150,
    "total_value": 125000.00,
    "warehouse_stats": {
      "Main Warehouse": {
        "total_items": 5000,
        "total_value": 75000.00,
        "item_count": 100
      }
    },
    "low_stock_count": 5
  }
}
```

#### Error Response (403 Forbidden)
```json
{
  "status": "error",
  "message": "Access denied. Insufficient permissions.",
  "required_roles": ["warehouse_manager", "warehouse_staff", "auditor", "top_management"]
}
```

#### Allowed Roles
- `warehouse_manager`
- `warehouse_staff`
- `auditor`
- `top_management`

---

### 2. Show Single Inventory Item
**GET** `/api/inventory/{id}`

Retrieve detailed information about a specific inventory item.

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | Yes | Inventory item ID |

#### Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Inventory item retrieved successfully",
  "data": {
    "id": 1,
    "item_id": "ITEM-001",
    "item_name": "Product A",
    "category_id": 1,
    "category_name": "Electronics",
    "warehouse_id": 1,
    "warehouse_name": "Main Warehouse",
    "warehouse_location": "Building A, Floor 2",
    "current_stock": 150,
    "minimum_stock": 20,
    "unit_price": 50.00,
    "unit_of_measure": "pcs",
    "description": "High-quality product",
    "supplier_info": "Supplier XYZ",
    "status": "active",
    "stock_value": 7500.00,
    "is_low_stock": false,
    "stock_percentage": 750.00,
    "created_at": "2025-11-01 10:00:00",
    "updated_at": "2025-11-06 14:30:00"
  }
}
```

#### Error Response (404 Not Found)
```json
{
  "status": "error",
  "message": "Inventory item not found",
  "item_id": 999
}
```

#### Allowed Roles
- `warehouse_manager`
- `warehouse_staff`
- `auditor`
- `top_management`

---

### 3. Create New Inventory Item
**POST** `/api/inventory`

Create a new inventory item in the system.

#### Request Headers
```
Content-Type: application/json
```

#### Request Body
```json
{
  "item_id": "ITEM-002",
  "item_name": "Product B",
  "category_id": 2,
  "warehouse_id": 1,
  "current_stock": 100,
  "minimum_stock": 20,
  "unit_price": 75.50,
  "unit_of_measure": "pcs",
  "description": "Product description",
  "supplier_info": "Supplier ABC"
}
```

#### Field Validations
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `item_id` | string | Yes | Must be unique |
| `item_name` | string | Yes | Min 3 chars, max 255 chars |
| `category_id` | int | Yes | Must exist in categories table |
| `warehouse_id` | int | Yes | Must exist in warehouses table |
| `current_stock` | int | Yes | Must be >= 0 |
| `minimum_stock` | int | Yes | Must be >= 0 |
| `unit_price` | decimal | Yes | Must be > 0 |
| `unit_of_measure` | string | No | Default: "pcs" |
| `description` | string | No | Optional |
| `supplier_info` | string | No | Optional |

#### Success Response (201 Created)
```json
{
  "status": "success",
  "message": "Inventory item created successfully",
  "data": {
    "id": 2,
    "item_id": "ITEM-002",
    "item_name": "Product B",
    "category_id": 2,
    "warehouse_id": 1,
    "current_stock": 100,
    "minimum_stock": 20,
    "unit_price": 75.50,
    "unit_of_measure": "pcs",
    "description": "Product description",
    "supplier_info": "Supplier ABC",
    "status": "active",
    "created_at": "2025-11-06 15:00:00",
    "updated_at": "2025-11-06 15:00:00"
  }
}
```

#### Error Response (409 Conflict - Duplicate Item ID)
```json
{
  "status": "error",
  "message": "Item ID already exists",
  "item_id": "ITEM-002"
}
```

#### Error Response (400 Bad Request - Validation Failed)
```json
{
  "status": "error",
  "message": "Failed to create inventory item",
  "errors": {
    "item_name": "Item name must be at least 3 characters long",
    "unit_price": "Unit price must be greater than 0"
  }
}
```

#### Error Response (404 Not Found - Invalid Category)
```json
{
  "status": "error",
  "message": "Category not found",
  "category_id": 99
}
```

#### Allowed Roles
- `warehouse_manager`
- `procurement_officer`

---

### 4. Update Inventory Item
**PUT/PATCH** `/api/inventory/{id}`

Update an existing inventory item. All fields are optional - only send fields you want to update.

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | Yes | Inventory item ID to update |

#### Request Headers
```
Content-Type: application/json
```

#### Request Body (Partial Update Example)
```json
{
  "current_stock": 200,
  "unit_price": 80.00,
  "status": "active"
}
```

#### Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Inventory item updated successfully",
  "data": {
    "id": 1,
    "item_id": "ITEM-001",
    "item_name": "Product A",
    "category_id": 1,
    "warehouse_id": 1,
    "current_stock": 200,
    "minimum_stock": 20,
    "unit_price": 80.00,
    "unit_of_measure": "pcs",
    "description": "Updated description",
    "supplier_info": "Supplier XYZ",
    "status": "active",
    "created_at": "2025-11-01 10:00:00",
    "updated_at": "2025-11-06 15:30:00"
  }
}
```

#### Error Response (404 Not Found)
```json
{
  "status": "error",
  "message": "Inventory item not found",
  "item_id": 999
}
```

#### Error Response (409 Conflict - Duplicate Item ID)
```json
{
  "status": "error",
  "message": "Item ID already exists",
  "item_id": "ITEM-002"
}
```

#### Allowed Roles
- `warehouse_manager`
- `procurement_officer`

---

### 5. Delete Inventory Item
**DELETE** `/api/inventory/{id}`

Delete an inventory item from the system.

**Important:** Items with existing stock (current_stock > 0) cannot be deleted. Stock must be transferred or adjusted to zero first.

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | Yes | Inventory item ID to delete |

#### Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Inventory item deleted successfully",
  "deleted_item": {
    "id": 5,
    "item_id": "ITEM-005",
    "item_name": "Old Product"
  }
}
```

#### Error Response (404 Not Found)
```json
{
  "status": "error",
  "message": "Inventory item not found",
  "item_id": 999
}
```

#### Error Response (409 Conflict - Item Has Stock)
```json
{
  "status": "error",
  "message": "Cannot delete item with existing stock. Please transfer or adjust stock to zero first.",
  "current_stock": 50
}
```

#### Allowed Roles
- `warehouse_manager`
- `it_administrator`

---

## Common Error Responses

### 403 Forbidden - Access Denied
```json
{
  "status": "error",
  "message": "Access denied. Only warehouse managers and procurement officers can create items."
}
```

### 400 Bad Request - No Data Provided
```json
{
  "status": "error",
  "message": "No data provided"
}
```

### 500 Internal Server Error
```json
{
  "status": "error",
  "message": "Failed to retrieve inventory items",
  "error": "Database connection failed"
}
```

---

## HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET, PUT, DELETE operations |
| 201 | Created | Successful POST (resource created) |
| 400 | Bad Request | Validation error or missing data |
| 403 | Forbidden | User lacks required permissions |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Duplicate entry or business rule violation |
| 500 | Internal Server Error | Server-side error |

---

## Usage Examples

### Example 1: Get All Items with Filters
```bash
GET /api/inventory?warehouse_id=1&status=active&page=1&limit=20
```

### Example 2: Get Low Stock Items
```bash
GET /api/inventory?low_stock=true
```

### Example 3: Create New Item (cURL)
```bash
curl -X POST http://localhost/WAREHOUSE-GROUP6/api/inventory \
  -H "Content-Type: application/json" \
  -d '{
    "item_id": "ITEM-NEW",
    "item_name": "New Product",
    "category_id": 1,
    "warehouse_id": 1,
    "current_stock": 50,
    "minimum_stock": 10,
    "unit_price": 99.99,
    "unit_of_measure": "pcs"
  }'
```

### Example 4: Update Item Stock (cURL)
```bash
curl -X PUT http://localhost/WAREHOUSE-GROUP6/api/inventory/1 \
  -H "Content-Type: application/json" \
  -d '{
    "current_stock": 300,
    "minimum_stock": 50
  }'
```

### Example 5: Delete Item (cURL)
```bash
curl -X DELETE http://localhost/WAREHOUSE-GROUP6/api/inventory/5
```

---

## Role-Based Access Control

| Endpoint | Method | Allowed Roles |
|----------|--------|---------------|
| `/api/inventory` | GET | warehouse_manager, warehouse_staff, auditor, top_management |
| `/api/inventory/{id}` | GET | warehouse_manager, warehouse_staff, auditor, top_management |
| `/api/inventory` | POST | warehouse_manager, procurement_officer |
| `/api/inventory/{id}` | PUT/PATCH | warehouse_manager, procurement_officer |
| `/api/inventory/{id}` | DELETE | warehouse_manager, it_administrator |

---

## Testing Guide

### 1. Test Authentication
Ensure user is logged in with appropriate role before making API requests.

### 2. Test Validation
- Try creating item with duplicate `item_id`
- Try invalid `category_id` or `warehouse_id`
- Try negative stock values
- Try zero or negative unit price

### 3. Test Permissions
- Test each endpoint with different user roles
- Verify 403 responses for unauthorized roles

### 4. Test Business Rules
- Try deleting item with stock > 0
- Verify low stock detection
- Test pagination with large datasets

---

## Notes

1. **Legacy Routes:** Web view routes (`/inventory`, `/inventory/add`, etc.) are still available for backward compatibility
2. **Logging:** All create, update, delete operations are logged to `writable/logs/`
3. **Validation:** Model-level validation is applied automatically
4. **Transactions:** Future versions will include database transactions for critical operations
5. **Soft Deletes:** Not currently implemented, but recommended for future enhancement

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-11-06 | Initial API implementation with full CRUD |

