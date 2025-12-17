<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function staff()      
    { 
        if (session('user_role') !== 'warehouse_staff') {
            return redirect()->to('/login');
        }
        return view('dashboard/staff/index'); 
    }

    public function staffScanner()
    {
        if (session('user_role') !== 'warehouse_staff') {
            return redirect()->to('/login');
        }
        $warehouseModel = new \App\Models\WarehouseModel();
        $data['warehouses'] = $warehouseModel->findAll();
        return view('dashboard/staff/scanner', $data);
    }
    
    public function manager()
    {
        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }
        return view('dashboard/manager/index');
    }

    public function managerApprovals()
    {
        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }
        return view('dashboard/manager/approvals');
    }
    
    public function auditor()    
    { 
        if (session('user_role') !== 'inventory_auditor') {
            return redirect()->to('/login');
        }
        return view('dashboard/auditor/index'); 
    }
    
    public function procurement()
    { 
        $role = session('user_role');
        if ($role !== 'procurement_officer' && $role !== 'PROCUREMENT_OFFICER') {
            return redirect()->to('/login');
        }
        return redirect()->to('/procurement'); 
    }
    
    public function apclerk()    
    { 
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }
        return view('dashboard/accounts_payable/index'); 
    }
    
    public function arclerk()    
    { 
        if (session('user_role') !== 'accounts_receivable_clerk') {
            return redirect()->to('/login');
        }
        return view('dashboard/accounts_receivable/index'); 
    }
    
    public function it()         
    { 
        if (session('user_role') !== 'it_administrator') {
            return redirect()->to('/login');
        }
        return redirect()->to('/it-admin'); 
    }
    
    public function top()        
    { 
        if (session('user_role') !== 'top_management') {
            return redirect()->to('/login');
        }
        return redirect()->to('/top-management'); 
    }
}
