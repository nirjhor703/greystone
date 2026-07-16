<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;

class DashboardController extends Controller
{
    public function index()
    {
        $brands = Brand::where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('admin.dashboard', compact('brands'));
    }
}