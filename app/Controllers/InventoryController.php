<?php

namespace App\Controllers;

use App\Models\InventoryModel;
use App\Models\CategoryModel;
use App\Models\WarehouseModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * InventoryController
 * 
 * Handles all inventory CRUD operations with JSON API responses
 * Follows RESTful conventions: index, show, store, update, delete
 */
class InventoryController extends BaseController
{
    protected $inventoryModel;
    protected $categoryModel;
    protected $warehouseModel;
    
    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->categoryModel = new CategoryModel();
        $this->warehouseModel = new WarehouseModel();
    }

    // ========================================
    // RESTFUL CRUD OPERATIONS - JSON RESPONSES
    // ========================================
    
    /**
     * GET /api/inventory
     * List all inventory items with pagination and filtering
     * 
     * Query Parameters:
     * - warehouse_id: Filter by warehouse
     * - category_id: Filter by category
     * - status: Filter by status (active/inactive)
     * - low_stock: true to show only low stock items
     * - page: Page number for pagination
     * - limit: Items per page (default: 50)
     * 
     * @return ResponseInterface JSON response
     */
    public function index(): ResponseInterface
    { 
        try {
            // Check user permission
            if (!$this->checkPermission(['warehouse_manager', 'warehouse_staff', 'auditor', 'top_management'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied. Insufficient permissions.',
                    'required_roles' => ['warehouse_manager', 'warehouse_staff', 'auditor', 'top_management']
                ], 403);
            }

            // Get query parameters for filtering
            $warehouseId = $this->request->getGet('warehouse_id');
            $categoryId = $this->request->getGet('category_id');
            $status = $this->request->getGet('status');
            $lowStock = $this->request->getGet('low_stock');
            $page = (int) ($this->request->getGet('page') ?? 1);
            $limit = (int) ($this->request->getGet('limit') ?? 50);

            // Build query
            $builder = $this->inventoryModel->db->table('inventory_items i');
            $builder->select('i.*, c.category_name, w.warehouse_name');
            $builder->join('categories c', 'c.id = i.category_id', 'left');
            $builder->join('warehouses w', 'w.id = i.warehouse_id', 'left');

            // Apply filters
            if ($warehouseId) {
                $builder->where('i.warehouse_id', $warehouseId);
            }
            if ($categoryId) {
                $builder->where('i.category_id', $categoryId);
            }
            if ($status) {
                $builder->where('i.status', $status);
            }
            if ($lowStock === 'true') {
                $builder->where('i.current_stock <=', 'i.minimum_stock', false);
            }

            // Get total count for pagination
            $totalItems = $builder->countAllResults(false);
            
            // Apply pagination
            $offset = ($page - 1) * $limit;
            $builder->limit($limit, $offset);
            $builder->orderBy('i.created_at', 'DESC');
            
            $items = $builder->get()->getResultArray();

            // Get additional statistics
            $stats = [
                'total_items' => $totalItems,
                'total_value' => $this->inventoryModel->getTotalValue(),
                'warehouse_stats' => $this->inventoryModel->getWarehouseStats(),
                'low_stock_count' => count($this->inventoryModel->getLowStockItems())
            ];

            return $this->respond([
                'status' => 'success',
                'message' => 'Inventory items retrieved successfully',
                'data' => $items,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_items' => $totalItems,
                    'total_pages' => ceil($totalItems / $limit)
                ],
                'statistics' => $stats
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'Inventory index error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve inventory items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/inventory/{id}
     * Show a specific inventory item by ID
     * 
     * @param int $id Inventory item ID
     * @return ResponseInterface JSON response
     */
    public function show($id): ResponseInterface
    {
        try {
            // Check user permission
            if (!$this->checkPermission(['warehouse_manager', 'warehouse_staff', 'auditor', 'top_management'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied. Insufficient permissions.'
                ], 403);
            }

            // Get item with relations
            $builder = $this->inventoryModel->db->table('inventory_items i');
            $builder->select('i.*, c.category_name, w.warehouse_name, w.location as warehouse_location');
            $builder->join('categories c', 'c.id = i.category_id', 'left');
            $builder->join('warehouses w', 'w.id = i.warehouse_id', 'left');
            $builder->where('i.id', $id);
            $item = $builder->get()->getRowArray();

            if (!$item) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Inventory item not found',
                    'item_id' => $id
                ], 404);
            }

            // Calculate additional information
            $item['stock_value'] = $item['current_stock'] * $item['unit_price'];
            $item['is_low_stock'] = $item['current_stock'] <= $item['minimum_stock'];
            $item['stock_percentage'] = $item['minimum_stock'] > 0 
                ? round(($item['current_stock'] / $item['minimum_stock']) * 100, 2) 
                : 100;

            return $this->respond([
                'status' => 'success',
                'message' => 'Inventory item retrieved successfully',
                'data' => $item
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'Inventory show error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve inventory item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/inventory
     * Create a new inventory item
     * 
     * Request Body (JSON):
     * {
     *   "item_id": "ITEM-001",
     *   "item_name": "Product Name",
     *   "category_id": 1,
     *   "warehouse_id": 1,
     *   "current_stock": 100,
     *   "minimum_stock": 20,
     *   "unit_price": 50.00,
     *   "unit_of_measure": "pcs",
     *   "description": "Product description",
     *   "supplier_info": "Supplier details"
     * }
     * 
     * @return ResponseInterface JSON response
     */
    public function store(): ResponseInterface
    {
        try {
            // Check user permission - only manager and procurement can create items
            if (!$this->checkPermission(['warehouse_manager', 'procurement_officer'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied. Only warehouse managers and procurement officers can create items.'
                ], 403);
            }

            // Get JSON input
            $input = $this->request->getJSON(true) ?? $this->request->getPost();

            if (empty($input)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'No data provided'
                ], 400);
            }

            // Prepare data
            $data = [
                'item_id' => $input['item_id'] ?? '',
                'item_name' => $input['item_name'] ?? '',
                'category_id' => $input['category_id'] ?? null,
                'warehouse_id' => $input['warehouse_id'] ?? null,
                'current_stock' => $input['current_stock'] ?? 0,
                'minimum_stock' => $input['minimum_stock'] ?? 0,
                'unit_price' => $input['unit_price'] ?? 0,
                'unit_of_measure' => $input['unit_of_measure'] ?? 'pcs',
                'description' => $input['description'] ?? '',
                'supplier_info' => $input['supplier_info'] ?? '',
                'status' => 'active'
            ];

            // Check if item_id is unique
            if (!$this->inventoryModel->isItemIdUnique($data['item_id'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Item ID already exists',
                    'item_id' => $data['item_id']
                ], 409); // 409 Conflict
            }

            // Validate category exists
            if (!$this->categoryModel->find($data['category_id'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Category not found',
                    'category_id' => $data['category_id']
                ], 404);
            }

            // Validate warehouse exists
            if (!$this->warehouseModel->find($data['warehouse_id'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Warehouse not found',
                    'warehouse_id' => $data['warehouse_id']
                ], 404);
            }

            // Save to database
            if ($this->inventoryModel->save($data)) {
                $insertId = $this->inventoryModel->getInsertID();
                $createdItem = $this->inventoryModel->find($insertId);

                log_message('info', "Inventory item created: ID={$insertId}, Item={$data['item_name']} by User=" . session()->get('user_id'));

                return $this->respond([
                    'status' => 'success',
                    'message' => 'Inventory item created successfully',
                    'data' => $createdItem
                ], 201); // 201 Created

            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to create inventory item',
                    'errors' => $this->inventoryModel->errors()
                ], 400);
            }

        } catch (Exception $e) {
            log_message('error', 'Inventory store error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to create inventory item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT/PATCH /api/inventory/{id}
     * Update an existing inventory item
     * 
     * Request Body (JSON): Same as store(), all fields optional
     * 
     * @param int $id Inventory item ID
     * @return ResponseInterface JSON response
     */
    public function update($id = null): ResponseInterface
    {
        try {
            // Check user permission
            if (!$this->checkPermission(['warehouse_manager', 'procurement_officer'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied. Only warehouse managers and procurement officers can update items.'
                ], 403);
            }

            // Check if item exists
            $existingItem = $this->inventoryModel->find($id);
            if (!$existingItem) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Inventory item not found',
                    'item_id' => $id
                ], 404);
            }

            // Get JSON input
            $input = $this->request->getJSON(true) ?? $this->request->getPost();

            if (empty($input)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'No data provided for update'
                ], 400);
            }

            // Prepare update data (only update fields that are provided)
            $data = [];
            $allowedFields = [
                'item_id', 'item_name', 'category_id', 'warehouse_id',
                'current_stock', 'minimum_stock', 'unit_price',
                'unit_of_measure', 'description', 'supplier_info', 'status'
            ];

            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $data[$field] = $input[$field];
                }
            }

            // Check if item_id is unique (exclude current record)
            if (isset($data['item_id']) && !$this->inventoryModel->isItemIdUnique($data['item_id'], $id)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Item ID already exists',
                    'item_id' => $data['item_id']
                ], 409);
            }

            // Validate category if provided
            if (isset($data['category_id']) && !$this->categoryModel->find($data['category_id'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Category not found',
                    'category_id' => $data['category_id']
                ], 404);
            }

            // Validate warehouse if provided
            if (isset($data['warehouse_id']) && !$this->warehouseModel->find($data['warehouse_id'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Warehouse not found',
                    'warehouse_id' => $data['warehouse_id']
                ], 404);
            }

            // Update the item
            if ($this->inventoryModel->update($id, $data)) {
                $updatedItem = $this->inventoryModel->find($id);

                log_message('info', "Inventory item updated: ID={$id} by User=" . session()->get('user_id'));

                return $this->respond([
                    'status' => 'success',
                    'message' => 'Inventory item updated successfully',
                    'data' => $updatedItem
                ], 200);

            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to update inventory item',
                    'errors' => $this->inventoryModel->errors()
                ], 400);
            }

        } catch (Exception $e) {
            log_message('error', 'Inventory update error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to update inventory item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/inventory/{id}
     * Delete an inventory item
     * 
     * @param int $id Inventory item ID
     * @return ResponseInterface JSON response
     */
    public function delete($id = null): ResponseInterface
    {
        try {
            // Check user permission - only manager can delete
            if (!$this->checkPermission(['warehouse_manager', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied. Only warehouse managers and IT administrators can delete items.'
                ], 403);
            }

            // Check if item exists
            $item = $this->inventoryModel->find($id);
            if (!$item) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Inventory item not found',
                    'item_id' => $id
                ], 404);
            }

            // Check if item has stock - prevent deletion if it has stock
            if ($item['current_stock'] > 0) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Cannot delete item with existing stock. Please transfer or adjust stock to zero first.',
                    'current_stock' => $item['current_stock']
                ], 409);
            }

            // Delete the item
            if ($this->inventoryModel->delete($id)) {
                log_message('info', "Inventory item deleted: ID={$id}, Item={$item['item_name']} by User=" . session()->get('user_id'));

                return $this->respond([
                    'status' => 'success',
                    'message' => 'Inventory item deleted successfully',
                    'deleted_item' => [
                        'id' => $id,
                        'item_id' => $item['item_id'],
                        'item_name' => $item['item_name']
                    ]
                ], 200);

            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to delete inventory item'
                ], 500);
            }

        } catch (Exception $e) {
            log_message('error', 'Inventory delete error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to delete inventory item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // LEGACY METHODS (Keep for backward compatibility with views)
    // ========================================
    
    /**
     * Legacy: Display inventory management page
     */
    public function indexView()    
    { 
        // Check if user is manager
        if (session('user_role') !== 'warehouse_manager') {
            session()->setFlashdata('error', 'Access denied. Role required: warehouse_manager. Your role: ' . session('user_role'));
            return redirect()->to('/login');
        }
        
        try {
            $data = [
                'items' => $this->inventoryModel->getItemsWithRelations(),
                'warehouse_stats' => $this->inventoryModel->getWarehouseStats(),
                'total_value' => $this->inventoryModel->getTotalValue()
            ];
        } catch (Exception $e) {
            log_message('error', 'Database error in inventory listing: ' . $e->getMessage());
            $data = [
                'items' => [],
                'warehouse_stats' => [],
                'total_value' => 0
            ];
        }
        
        return view('dashboard/manager/index', $data); 
    }

    /**
     * Legacy: Create form view
     */
    public function create()
    {
        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }
        
        $data = [
            'categories' => $this->categoryModel->getCategoriesForDropdown(),
            'warehouses' => $this->warehouseModel->getWarehousesForDropdown()
        ];
        
        return view('dashboard/manager/add', $data);
    }

    /**
     * Legacy: Edit form view
     */
    public function edit($id)
    {
        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }
        
        $builder = $this->inventoryModel->db->table('inventory_items i');
        $builder->select('i.*, c.category_name, w.warehouse_name');
        $builder->join('categories c', 'c.id = i.category_id');
        $builder->join('warehouses w', 'w.id = i.warehouse_id');
        $builder->where('i.id', $id);
        $item = $builder->get()->getRowArray();
        
        if (!$item) {
            session()->setFlashdata('error', 'Item not found.');
            return redirect()->to('/inventory');
        }
        
        $data = [
            'item' => $item,
            'categories' => $this->categoryModel->getCategoriesForDropdown(),
            'warehouses' => $this->warehouseModel->getWarehousesForDropdown()
        ];
        
        return view('dashboard/manager/edit', $data);
    }

    // ========================================
    // HELPER METHODS
    // ========================================
    
    /**
     * Check if user has required permission
     * 
     * @param array $allowedRoles Array of role names
     * @return bool
     */
    private function checkPermission(array $allowedRoles): bool
    {
        $userRole = session()->get('user_role');
        
        if (!$userRole) {
            return false;
        }

        return in_array($userRole, $allowedRoles);
    }

    /**
     * Standard JSON response helper
     * 
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     * @return ResponseInterface
     */
    private function respond(array $data, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($data);
    }
}