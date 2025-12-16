<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AuditLogModel
 *
 * Manages audit logs for system activities
 *
 * @package App\Models
 */
class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'user_id',
        'action',
        'module',
        'record_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;

    /**
     * Log an action
     *
     * @param string $action
     * @param string $module
     * @param int|null $recordId
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param string|null $description
     * @return bool
     */
    public function logAction($action, $module, $recordId = null, $oldValues = null, $newValues = null, $description = null)
    {
        $data = [
            'user_id' => session('user_id'),
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'description' => $description,
            'ip_address' => $this->request ? $this->request->getIPAddress() : null,
            'user_agent' => $this->request ? $this->request->getUserAgent()->getAgentString() : null
        ];

        return $this->insert($data);
    }

    /**
     * Get audit logs with user details
     *
     * @param array $filters
     * @return array
     */
    public function getLogsWithUsers($filters = [])
    {
        $builder = $this->select('audit_logs.*, users.first_name, users.last_name, users.email')
                        ->join('users', 'users.id = audit_logs.user_id');

        // Apply filters
        if (isset($filters['module'])) {
            $builder->where('audit_logs.module', $filters['module']);
        }

        if (isset($filters['action'])) {
            $builder->where('audit_logs.action', $filters['action']);
        }

        if (isset($filters['user_id'])) {
            $builder->where('audit_logs.user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $builder->where('audit_logs.created_at >=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $builder->where('audit_logs.created_at <=', $filters['date_to']);
        }

        return $builder->orderBy('audit_logs.created_at', 'DESC')->findAll();
    }

    /**
     * Get audit statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_logs' => $this->countAllResults(false),
            'today_logs' => $this->where('DATE(created_at)', date('Y-m-d'))->countAllResults(false),
            'unique_users' => $this->select('user_id')->distinct()->countAllResults(false),
            'modules_count' => $this->select('module')->distinct()->countAllResults(false)
        ];
    }
}
