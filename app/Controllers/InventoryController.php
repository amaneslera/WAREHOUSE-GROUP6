<?php

namespace App\Controllers;

use App\Models\InventoryModel;
use App\Models\CategoryModel;
use App\Models\WarehouseModel;
use Exception;

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

    public function index()    
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
            // If there's a database error, show basic view
            log_message('error', 'Database error in inventory listing: ' . $e->getMessage());
            $data = [
                'items' => [],
                'warehouse_stats' => [],
                'total_value' => 0
            ];
        }
        
        return view('dashboard/manager/index', $data); 
    }
    
    // ========================================
    // INVENTORY CRUD OPERATIONS
    // ========================================
    public function add()
    {
        // Check if user is manager
        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }
        
        $data = [
            'categories' => $this->categoryModel->getCategoriesForDropdown(),
            'warehouses' => $this->warehouseModel->getWarehousesForDropdown()
        ];
        
        return view('dashboard/manager/add', $data);
    }
    
    public function create()
    {
        // Check if user is manager
        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }
        
        // Get form data
        $data = [
            'item_id' => $this->request->getPost('item_id'),
            'item_name' => $this->request->getPost('item_name'),
            'category_id' => $this->request->getPost('category_id'),
            'warehouse_id' => $this->request->getPost('warehouse_id'),
            'current_stock' => $this->request->getPost('current_stock'),
            'minimum_stock' => $this->request->getPost('minimum_stock'),
            'unit_price' => $this->request->getPost('unit_price'),
            'description' => $this->request->getPost('description'),
            'status' => 'active'
        ];
        
        // Check if item_id is unique
        if (!$this->inventoryModel->isItemIdUnique($data['item_id'])) {
            session()->setFlashdata('error', 'Item ID already exists. Please use a different Item ID.');
            return redirect()->back()->withInput();
        }
        
        // Validate and save
        if ($this->inventoryModel->save($data)) {
            session()->setFlashdata('success', 'Item added successfully!');
            return redirect()->to('/inventory');
        } else {
            session()->setFlashdata('error', 'Failed to add item. Please check your input.');
            session()->setFlashdata('validation', $this->inventoryModel->errors());
            return redirect()->back()->withInput();
        }
    }
    
    public function view($id)
    {
        // Check if user is manager
        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }
        
        // Get item with relations
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
        
        return view('dashboard/manager/view', ['item' => $item]);
    }
    
    public function edit($id)
    {
        // Check if user is manager
        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }
        
        // Get item with relations
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
    
    public function update($id)
    {
        // Check if user is manager
        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }
        
        // Check if item exists
        $item = $this->inventoryModel->find($id);
        if (!$item) {
            session()->setFlashdata('error', 'Item not found.');
            return redirect()->to('/inventory');
        }
        
        // Get form data
        $data = [
            'item_id' => $this->request->getPost('item_id'),
            'item_name' => $this->request->getPost('item_name'),
            'category_id' => $this->request->getPost('category_id'),
            'warehouse_id' => $this->request->getPost('warehouse_id'),
            'current_stock' => $this->request->getPost('current_stock'),
            'minimum_stock' => $this->request->getPost('minimum_stock'),
            'unit_price' => $this->request->getPost('unit_price'),
            'description' => $this->request->getPost('description')
        ];
        
        // Check if item_id is unique (exclude current record)
        if (!$this->inventoryModel->isItemIdUnique($data['item_id'], $id)) {
            session()->setFlashdata('error', 'Item ID already exists. Please use a different Item ID.');
            return redirect()->back()->withInput();
        }
        
        // Basic validation rules (without uniqueness check)
        $validationRules = [
            'item_id'       => 'required',
            'item_name'     => 'required|min_length[3]|max_length[255]',
            'category_id'   => 'required|integer',
            'warehouse_id'  => 'required|integer',
            'current_stock' => 'required|integer|greater_than_equal_to[0]',
            'minimum_stock' => 'required|integer|greater_than_equal_to[0]',
            'unit_price'    => 'required|decimal|greater_than[0]',
        ];
        
        // Validate the data
        if (!$this->validate($validationRules)) {
            session()->setFlashdata('error', 'Failed to update item. Please check your input.');
            session()->setFlashdata('validation', $this->validator->getErrors());
            return redirect()->back()->withInput();
        }
        
        // Update the item (skip model validation since we did it manually)
        $this->inventoryModel->skipValidation(true);
        if ($this->inventoryModel->update($id, $data)) {
            session()->setFlashdata('success', 'Item updated successfully!');
            return redirect()->to('/inventory/view/' . $id);
        } else {
            session()->setFlashdata('error', 'Failed to update item. Please try again.');
            return redirect()->back()->withInput();
        }
    }
    
    public function delete($id)
    {
        // Check if user is manager
        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }
        
        // Check if item exists
        $item = $this->inventoryModel->find($id);
        if (!$item) {
            session()->setFlashdata('error', 'Item not found.');
            return redirect()->to('/inventory');
        }
        
        // Delete item
        if ($this->inventoryModel->delete($id)) {
            session()->setFlashdata('success', 'Item deleted successfully!');
        } else {
            session()->setFlashdata('error', 'Failed to delete item.');
        }
        
        return redirect()->to('/inventory');
    }
}