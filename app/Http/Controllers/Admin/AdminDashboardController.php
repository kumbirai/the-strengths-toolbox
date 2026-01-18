<?php

namespace App\Http\Controllers\Admin;

class AdminDashboardController extends BaseController
{
    public function index()
    {
        return view('admin.dashboard');
    }
}
