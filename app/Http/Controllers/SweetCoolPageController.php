<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\SweetCoolPage;
use App\Models\SweetCoolVisitBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SweetCoolPageController extends Controller
{
    public function show(): View
    {
        $page = SweetCoolPage::singleton();
        $brandSlug = (string) request()->query('brand', 'grey-stone');
        $brand = Brand::query()
            ->where('slug', $brandSlug)
            ->where('is_active', true)
            ->first()
            ?? Brand::query()
                ->where('slug', 'grey-stone')
                ->where('is_active', true)
                ->firstOrFail();

        return view('sweet-cool.show', [
            'sweetCoolPage' => $page,
            'sweetCoolRoles' => $this->roleOptions(),
            'brand' => $brand,
        ]);
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'visit_time' => ['required', 'date_format:H:i'],
        ]);

        $isBooked = SweetCoolVisitBooking::query()
            ->whereDate('visit_date', $validated['visit_date'])
            ->where('visit_time', $validated['visit_time'].':00')
            ->exists();

        return response()->json([
            'available' => !$isBooked,
            'message' => $isBooked
                ? 'That date and time is already booked. Please choose another slot.'
                : 'This slot is available for Sweet Cool.',
        ]);
    }

    public function storeBooking(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('sweet_cool_booking', [
            'customer_name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'visit_time' => ['required', 'date_format:H:i'],
            'contact_reason' => ['nullable', 'string', 'max:160'],
            'role_tags' => ['nullable', 'array', 'max:6'],
            'role_tags.*' => ['string', Rule::in($this->roleOptions())],
            'message' => ['nullable', 'string', 'max:1200'],
        ]);

        $existingBooking = SweetCoolVisitBooking::query()
            ->whereDate('visit_date', $validated['visit_date'])
            ->where('visit_time', $validated['visit_time'].':00')
            ->exists();

        if ($existingBooking) {
            return back()
                ->withErrors([
                    'visit_time' => 'This date and time is already booked. Please choose another visit slot.',
                ], 'sweet_cool_booking')
                ->withInput();
        }

        SweetCoolVisitBooking::query()->create($validated);

        return back()
            ->with('sweet_cool_booking_success', 'Your Sweet Cool visit request has been booked successfully.');
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
