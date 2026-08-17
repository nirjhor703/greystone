<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = $this->brandQuery(request())
            ->latest('id')
            ->get();

        if (request()->ajax()) {
            return view(
                'admin.brands.partials.table-rows',
                compact('brands')
            );
        }

        return view('admin.brands.index', compact('brands'));
    }

    public function show(Brand $brand): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'primary_color' => $brand->primary_color,
                'secondary_color' => $brand->secondary_color,
                'background_color' => $brand->background_color,
                'button_color' => $brand->button_color,
                'text_color' => $brand->text_color,
                'font_family' => $brand->font_family,
                'header_style' => $brand->header_style,
                'footer_style' => $brand->footer_style,
                'contact_number' => $brand->contact_number,
                'email' => $brand->email,
                'facebook_link' => $brand->facebook_link,
                'instagram_link' => $brand->instagram_link,
                'whatsapp_link' => $brand->whatsapp_link,
                'address' => $brand->address,
                'meta_title' => $brand->meta_title,
                'meta_description' => $brand->meta_description,
                'is_active' => $brand->is_active,

                'logo_url' => $brand->logo
                    ? Storage::url($brand->logo)
                    : null,

                'mobile_logo_url' => $brand->mobile_logo
                    ? Storage::url($brand->mobile_logo)
                    : null,

                'favicon_url' => $brand->favicon
                    ? Storage::url($brand->favicon)
                    : null,

                'offer_banner_urls' => collect($brand->offer_banners ?? [])
                    ->map(fn ($banner) => Storage::url($banner))
                    ->values(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateBrand($request);

        $validated['is_active'] = $request->boolean('is_active');

        $validated = $this->storeUploadedFiles(
            request: $request,
            data: $validated
        );

        $brand = Brand::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Brand added successfully!',
            'brand' => $this->brandTableData($brand),
        ]);
    }

    public function update(Request $request, Brand $brand): JsonResponse
    {
        $validated = $this->validateBrand($request, $brand);

        $validated['is_active'] = $request->boolean('is_active');

        $validated = $this->storeUploadedFiles(
            request: $request,
            data: $validated,
            brand: $brand
        );

        $brand->update($validated);
        $brand->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Brand updated successfully!',
            'brand' => $this->brandTableData($brand),
        ]);
    }

    public function destroy(Brand $brand): JsonResponse
    {
        $this->deleteBrandFiles($brand);

        $brand->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Brand deleted successfully!',
        ]);
    }

    private function validateBrand(
        Request $request,
        ?Brand $brand = null
    ): array {
        $brandId = $brand?->id;

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->ignore($brandId),
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('brands', 'slug')->ignore($brandId),
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,svg',
                'max:2048',
            ],

            'mobile_logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,svg',
                'max:2048',
            ],

            'favicon' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,ico',
                'max:1024',
            ],

            'offer_banners' => [
                'nullable',
                'array',
                'max:12',
            ],

            'offer_banners.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'primary_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'secondary_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'background_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'button_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'text_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'font_family' => [
                'nullable',
                'string',
                'max:255',
            ],

            'header_style' => [
                'nullable',
                'string',
                'max:100',
            ],

            'footer_style' => [
                'nullable',
                'string',
                'max:100',
            ],

            'contact_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'facebook_link' => [
                'nullable',
                'url',
                'max:500',
            ],

            'instagram_link' => [
                'nullable',
                'url',
                'max:500',
            ],

            'whatsapp_link' => [
                'nullable',
                'url',
                'max:500',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);
    }

    private function brandQuery(Request $request)
    {
        return Brand::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim($request->string('search'));

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where(
                    'is_active',
                    $request->input('status') === 'active'
                );
            });
    }

    private function storeUploadedFiles(
        Request $request,
        array $data,
        ?Brand $brand = null
    ): array {
        foreach (['logo', 'mobile_logo', 'favicon'] as $field) {
            if (!$request->hasFile($field)) {
                unset($data[$field]);
                continue;
            }

            if ($brand && $brand->{$field}) {
                Storage::disk('public')->delete($brand->{$field});
            }

            $data[$field] = $request
                ->file($field)
                ->store('brands', 'public');
        }

        unset($data['offer_banners']);

        if ($request->hasFile('offer_banners')) {
            $banners = collect($brand?->offer_banners ?? []);

            foreach ($request->file('offer_banners') as $banner) {
                $banners->push($banner->store('brands/offers', 'public'));
            }

            $data['offer_banners'] = $banners
                ->filter()
                ->values()
                ->all();
        }

        return $data;
    }

    private function deleteBrandFiles(Brand $brand): void
    {
        foreach (['logo', 'mobile_logo', 'favicon'] as $field) {
            if ($brand->{$field}) {
                Storage::disk('public')->delete($brand->{$field});
            }
        }

        foreach ($brand->offer_banners ?? [] as $banner) {
            Storage::disk('public')->delete($banner);
        }
    }

    private function brandTableData(Brand $brand): array
    {
        return [
            'id' => $brand->id,
            'name' => $brand->name,
            'slug' => $brand->slug,
            'primary_color' => $brand->primary_color,
            'email' => $brand->email,
            'contact_number' => $brand->contact_number,
            'is_active' => $brand->is_active,
            'logo_url' => $brand->logo
                ? Storage::url($brand->logo)
                : null,
            'store_url' => route('brand.show', $brand->slug),
        ];
    }
}
