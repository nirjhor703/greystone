<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\SweetCoolPage;
use App\Models\SweetCoolInquiry;
use App\Models\SweetCoolVisitBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SweetCoolInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $page = SweetCoolPage::singleton();

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

        $bookings = SweetCoolVisitBooking::query()
            ->latest('visit_date')
            ->latest('visit_time')
            ->paginate(20, ['*'], 'bookings_page')
            ->withQueryString();

        $statsQuery = SweetCoolInquiry::query();

        return view('admin.sweet-cool.index', [
            'page' => $page,
            'brands' => $brands,
            'inquiries' => $inquiries,
            'bookings' => $bookings,
            'roleOptions' => $this->roleOptions(),
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
                'bookings' => SweetCoolVisitBooking::query()->count(),
            ],
        ]);
    }

    public function updateContent(Request $request): RedirectResponse
    {
        $page = SweetCoolPage::singleton();

        $validated = $request->validate([
            'teaser_kicker' => ['nullable', 'string', 'max:80'],
            'teaser_title' => ['nullable', 'string', 'max:120'],
            'teaser_description' => ['nullable', 'string', 'max:400'],
            'teaser_button_text' => ['nullable', 'string', 'max:120'],
            'hero_kicker' => ['nullable', 'string', 'max:80'],
            'hero_title' => ['nullable', 'string', 'max:140'],
            'hero_description' => ['nullable', 'string', 'max:700'],
            'hero_button_text' => ['nullable', 'string', 'max:120'],
            'intro_heading' => ['nullable', 'string', 'max:180'],
            'intro_description' => ['nullable', 'string', 'max:700'],
            'section_one_title' => ['nullable', 'string', 'max:120'],
            'section_one_body' => ['nullable', 'string', 'max:500'],
            'section_two_title' => ['nullable', 'string', 'max:120'],
            'section_two_body' => ['nullable', 'string', 'max:500'],
            'section_three_title' => ['nullable', 'string', 'max:120'],
            'section_three_body' => ['nullable', 'string', 'max:500'],
            'promo_badges' => ['nullable', 'array', 'max:6'],
            'promo_badges.*' => ['nullable', 'string', 'max:60'],
            'logo_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'gallery_uploads' => ['nullable', 'array', 'max:12'],
            'gallery_uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'factory_uploads' => ['nullable', 'array', 'max:12'],
            'factory_uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'product_uploads' => ['nullable', 'array', 'max:12'],
            'product_uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remove_gallery_images' => ['nullable', 'array'],
            'remove_gallery_images.*' => ['string'],
            'remove_factory_images' => ['nullable', 'array'],
            'remove_factory_images.*' => ['string'],
            'remove_product_images' => ['nullable', 'array'],
            'remove_product_images.*' => ['string'],
        ]);

        $validated['promo_badges'] = collect($request->input('promo_badges', []))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        if ($request->hasFile('logo_image')) {
            if ($page->logo_path) {
                Storage::disk('public')->delete($page->logo_path);
            }

            $validated['logo_path'] = $request->file('logo_image')
                ->store('sweet-cool', 'public');
        }

        if ($request->hasFile('hero_image')) {
            if ($page->hero_image_path) {
                Storage::disk('public')->delete($page->hero_image_path);
            }

            $validated['hero_image_path'] = $request->file('hero_image')
                ->store('sweet-cool', 'public');
        }

        $validated['gallery_images'] = $this->syncImageCollection(
            current: $page->gallery_images ?? [],
            remove: $request->input('remove_gallery_images', []),
            uploads: $request->file('gallery_uploads', []),
            directory: 'sweet-cool/gallery'
        );

        $validated['factory_images'] = $this->syncImageCollection(
            current: $page->factory_images ?? [],
            remove: $request->input('remove_factory_images', []),
            uploads: $request->file('factory_uploads', []),
            directory: 'sweet-cool/factory'
        );

        $validated['product_images'] = $this->syncImageCollection(
            current: $page->product_images ?? [],
            remove: $request->input('remove_product_images', []),
            uploads: $request->file('product_uploads', []),
            directory: 'sweet-cool/products'
        );

        unset(
            $validated['logo_image'],
            $validated['hero_image'],
            $validated['gallery_uploads'],
            $validated['factory_uploads'],
            $validated['product_uploads'],
            $validated['remove_gallery_images'],
            $validated['remove_factory_images'],
            $validated['remove_product_images']
        );

        $page->update($validated);

        return back()->with('sweet_cool_admin_success', 'Sweet Cool page content updated successfully.');
    }

    private function syncImageCollection(
        array $current,
        array $remove,
        array $uploads,
        string $directory
    ): array {
        $images = collect($current)
            ->filter()
            ->reject(function (string $path) use ($remove): bool {
                return in_array($path, $remove, true);
            })
            ->values();

        foreach ($remove as $path) {
            if ($path !== '') {
                Storage::disk('public')->delete($path);
            }
        }

        foreach ($uploads as $file) {
            $images->push($file->store($directory, 'public'));
        }

        return $images
            ->unique()
            ->take(18)
            ->values()
            ->all();
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
