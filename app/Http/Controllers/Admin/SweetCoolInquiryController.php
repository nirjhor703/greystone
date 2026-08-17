<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\SweetCoolInquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SweetCoolInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $inquiries = SweetCoolInquiry::query()
            ->with(['brand', 'product'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('brand', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('product', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('brand_id'), function ($query) use ($request): void {
                $query->where('brand_id', $request->integer('brand_id'));
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $statsQuery = SweetCoolInquiry::query();

        return view('admin.sweet-cool.index', [
            'brands' => $brands,
            'inquiries' => $inquiries,
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'factory' => (clone $statsQuery)
                    ->where('interest_type', 'factory-sourcing')
                    ->count(),
                'bulk' => (clone $statsQuery)
                    ->where('interest_type', 'bulk-order')
                    ->count(),
                'this_week' => (clone $statsQuery)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
            ],
        ]);
    }
}
