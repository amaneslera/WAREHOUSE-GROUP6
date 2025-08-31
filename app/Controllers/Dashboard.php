<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function manager()    { return view('dashboard/manager'); }
    public function staff()      { return view('dashboard/staff'); }
    public function auditor()    { return view('dashboard/auditor'); }
    public function procurement(){ return view('dashboard/procurement'); }
    public function apclerk()    { return view('dashboard/apclerk'); }
    public function arclerk()    { return view('dashboard/arclerk'); }
    public function it()         { return view('dashboard/it'); }
    public function top()        { return view('dashboard/top'); }
}