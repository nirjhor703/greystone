<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\SweetCoolInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SweetCoolInquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'source_page' => ['required', Rule::in(['storefront', 'product'])],
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:120'],
            'interest_type' => [
                'required',
                Rule::in([
                    'bulk-order',
                    'factory-sourcing',
                    'custom-production',
                    'wholesale-partnership',
                ]),
            ],
            'quantity_note' => ['nullable', 'string', 'max:120'],
            'preferred_contact' => ['nullable', Rule::in(['phone', 'email', 'whatsapp'])],
            'message' => ['required', 'string', 'max:2000'],
            'page_url' => ['nullable', 'string', 'max:255'],
        ]);

        $brand = null;
        if (!empty($validated['brand_id'])) {
            $brand = Brand::query()->find($validated['brand_id']);
        }

        $product = null;
        if (!empty($validated['product_id'])) {
            $product = Product::query()->find($validated['product_id']);
        }

        if ($brand && $product && (int) $product->brand_id !== (int) $brand->id) {
            return back()
                ->withErrors(['sweet_cool' => 'Selected product does not belong to this brand.'])
                ->withInput();
        }

        SweetCoolInquiry::query()->create($validated);

        $redirectUrl = $validated['page_url'] ?? url()->previous();

        return redirect()->to($redirectUrl.'#sweet-cool')
            ->with(
                'sweet_cool_success',
                'Thanks. Your Sweet Cool inquiry has been sent to the team.'
            );
    }
}
