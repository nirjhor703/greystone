<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\SweetCoolInquiry;
use App\Models\SweetCoolVisitBooking;
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
            'source_page' => ['required', Rule::in(['storefront', 'product', 'sweet-cool'])],
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
                    'factory-visit',
                    'buyer-meeting',
                ]),
            ],
            'preferred_contact' => ['nullable', Rule::in(['phone', 'email', 'whatsapp'])],
            'role_tags' => ['required', 'array', 'min:1', 'max:6'],
            'role_tags.*' => ['string', Rule::in($this->roleOptions())],
            'visit_date' => ['nullable', 'date', 'after_or_equal:today', 'required_with:visit_time'],
            'visit_time' => ['nullable', 'date_format:H:i', 'required_with:visit_date'],
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

        $hasVisitBooking = filled($validated['visit_date'] ?? null)
            && filled($validated['visit_time'] ?? null);

        if ($hasVisitBooking) {
            $visitAlreadyBooked = SweetCoolVisitBooking::query()
                ->whereDate('visit_date', $validated['visit_date'])
                ->where('visit_time', $validated['visit_time'].':00')
                ->exists();

            if ($visitAlreadyBooked) {
                return back()
                    ->withErrors([
                        'visit_time' => 'This date and time is already booked. Please choose another slot for your factory visit.',
                    ])
                    ->withInput();
            }
        }

        $inquiryPayload = $validated;
        unset($inquiryPayload['visit_date'], $inquiryPayload['visit_time']);

        SweetCoolInquiry::query()->create($inquiryPayload);

        if ($hasVisitBooking) {
            SweetCoolVisitBooking::query()->create([
                'customer_name' => $validated['customer_name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'visit_date' => $validated['visit_date'],
                'visit_time' => $validated['visit_time'],
                'contact_reason' => $validated['interest_type'] ?? null,
                'role_tags' => $validated['role_tags'] ?? [],
                'message' => $validated['message'],
                'status' => 'booked',
            ]);
        }

        $redirectUrl = $validated['page_url'] ?? url()->previous();
        $successMessage = $hasVisitBooking
            ? 'Thanks. Your Sweet Cool form has been sent and your visit slot request has been booked.'
            : 'Thanks. Your Sweet Cool form has been sent.';

        return redirect()->to($redirectUrl.'#sweet-cool')
            ->with(
                'sweet_cool_success',
                $successMessage
            );
    }

    private function roleOptions(): array
    {
        return [
            'entrepreneur',
            'buyer',
            'others',
            'retailer',
            'wholesaler',
            'sourcing-agent',
        ];
    }
}
