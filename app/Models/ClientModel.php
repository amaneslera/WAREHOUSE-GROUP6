<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ClientModel
 * 
 * Manages client/customer information for Accounts Receivable
 * 
 * @package App\Models
 */
class ClientModel extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'client_code',
        'client_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_id',
        'credit_limit',
        'payment_terms',
        'status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'client_code' => 'required|is_unique[clients.client_code,id,{id}]',
        'client_name' => 'required|min_length[3]|max_length[255]',
        'email'       => 'permit_empty|valid_email',
        'status'      => 'required|in_list[active,inactive,blocked]'
    ];

    protected $validationMessages = [
        'client_code' => [
            'required'  => 'Client code is required',
            'is_unique' => 'Client code already exists'
        ],
        'client_name' => [
            'required' => 'Client name is required'
        ]
    ];

    /**
     * Get active clients for dropdown
     * 
     * @return array
     */
    public function getClientsForDropdown()
    {
        $clients = $this->where('status', 'active')->findAll();
        $dropdown = [];
        foreach ($clients as $client) {
            $dropdown[$client['id']] = $client['client_name'];
        }
        return $dropdown;
    }

    /**
     * Get client with AR statistics
     * 
     * @param int $clientId
     * @return array|null
     */
    public function getClientWithStats($clientId)
    {
        $client = $this->find($clientId);
        if (!$client) {
            return null;
        }

        // Get AR stats
        $builder = $this->db->table('accounts_receivable');
        $builder->select('
            COUNT(*) as total_invoices,
            SUM(invoice_amount) as total_amount,
            SUM(balance) as total_balance
        ');
        $builder->where('client_id', $clientId);
        $stats = $builder->get()->getRowArray();

        $client['stats'] = $stats;
        return $client;
    }
}
