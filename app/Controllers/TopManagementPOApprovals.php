<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\PurchaseOrderModel;

class TopManagementPOApprovals extends BaseController
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

    public function purchaseOrders()
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        return redirect()->to('/top-management')->with('error', 'PO approvals are disabled. Top Management approves PR only.');
    }

    public function approvePO($id)
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        return redirect()->to('/top-management')->with('error', 'PO approvals are disabled. Top Management approves PR only.');
    }

    public function rejectPO($id)
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        return redirect()->to('/top-management')->with('error', 'PO approvals are disabled. Top Management approves PR only.');
    }
}
