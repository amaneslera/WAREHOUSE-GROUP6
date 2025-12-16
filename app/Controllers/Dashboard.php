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
    
    public function auditor()    
    { 
        if (session('user_role') !== 'inventory_auditor') {
            return redirect()->to('/login');
        }
        return view('dashboard/auditor/index'); 
    }
    
    public function procurement()
    { 
        if (session('user_role') !== 'procurement_officer') {
            return redirect()->to('/login');
        }
        return view('dashboard/procurement/index'); 
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
        return view('dashboard/it_admin/index'); 
    }
    
    public function top()        
    { 
        if (session('user_role') !== 'top_management') {
            return redirect()->to('/login');
        }
        return view('dashboard/top_management/index'); 
    }
}
