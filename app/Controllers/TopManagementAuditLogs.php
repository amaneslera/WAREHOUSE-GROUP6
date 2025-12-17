<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class TopManagementAuditLogs extends BaseController
{
    private function requireTopManagement()
    {
        if (! session('logged_in')) {
            return redirect()->to('/login');
        }

        if (session('user_role') !== 'top_management') {
            return redirect()->to('/login');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $auditModel = new AuditLogModel();

        $filters = [];
        $module = $this->request->getGet('module');
        $action = $this->request->getGet('action');
        $userId = $this->request->getGet('user_id');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        if ($module) {
            $filters['module'] = $module;
        }
        if ($action) {
            $filters['action'] = $action;
        }
        if ($userId) {
            $filters['user_id'] = $userId;
        }
        if ($dateFrom) {
            $filters['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $filters['date_to'] = $dateTo;
        }

        $logs = $auditModel->getLogsWithUsers($filters);

        return view('dashboard/top_management/audit_logs', [
            'title' => 'Audit Logs',
            'logs' => $logs,
            'filters' => [
                'module' => $module,
                'action' => $action,
                'user_id' => $userId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
