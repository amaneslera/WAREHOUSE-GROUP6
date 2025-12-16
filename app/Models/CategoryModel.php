<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'category_name',
        'description'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'category_name' => 'required|min_length[3]|max_length[100]|is_unique[categories.category_name,id,{id}]',
    ];

    protected $validationMessages = [
        'category_name' => [
            'required'   => 'Category name is required',
            'min_length' => 'Category name must be at least 3 characters long',
            'is_unique'  => 'Category name already exists'
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getCategoriesForDropdown()
    {
        $categories = $this->findAll();
        $dropdown = [];
        foreach ($categories as $category) {
            $dropdown[$category['id']] = $category['category_name'];
        }
        return $dropdown;
    }
}
