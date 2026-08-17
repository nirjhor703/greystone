@extends('admin.layouts.app')

@section('title', 'Edit '.$brand->name)
@section('page-title', 'Edit '.$brand->name)
@section('page-subtitle', 'Update theme and contact settings')

@push('styles')
<style>
    .brand-form-card {
        max-width: 900px;
        padding: 26px;
        background: #ffffff;
        border: 1px solid var(--admin-border);
        border-radius: var(--admin-radius);
    }

    .brand-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .brand-form-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .brand-form-field.full {
        grid-column: 1 / -1;
    }

    .brand-form-field label {
        font-size: 13px;
        font-weight: 700;
    }

    .brand-form-field input,
    .brand-form-field textarea {
        width: 100%;
        padding: 13px 14px;
        border: 1px solid #d8d8dc;
        border-radius: 10px;
        outline: none;
    }

    .brand-form-field textarea {
        min-height: 110px;
        resize: vertical;
    }

    .brand-form-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    @media (max-width: 700px) {
        .brand-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <form
        method="POST"
        action="{{ route('admin.brands.update', $brand) }}"
        class="brand-form-card"
    >
        @csrf
        @method('PUT')

        <div class="brand-form-grid">
            <div class="brand-form-field">
                <label for="name">Brand name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $brand->name) }}"
                    required
                >
            </div>

            <div class="brand-form-field">
                <label for="font_family">Font family</label>
                <input
                    id="font_family"
                    type="text"
                    name="font_family"
                    value="{{ old('font_family', $brand->font_family) }}"
                    placeholder="Figtree, sans-serif"
                >
            </div>

            @foreach ([
                'primary_color' => 'Primary color',
                'secondary_color' => 'Secondary color',
                'background_color' => 'Background color',
                'button_color' => 'Button color',
                'text_color' => 'Text color',
            ] as $field => $label)
                <div class="brand-form-field">
                    <label for="{{ $field }}">{{ $label }}</label>
                    <input
                        id="{{ $field }}"
                        type="color"
                        name="{{ $field }}"
                        value="{{ old($field, $brand->{$field}) }}"
                        required
                    >
                </div>
            @endforeach

            <div class="brand-form-field">
                <label for="contact_number">Contact number</label>
                <input
                    id="contact_number"
                    type="text"
                    name="contact_number"
                    value="{{ old('contact_number', $brand->contact_number) }}"
                >
            </div>

            <div class="brand-form-field">
                <label for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $brand->email) }}"
                >
            </div>

            <div class="brand-form-field">
                <label for="facebook_link">Facebook link</label>
                <input
                    id="facebook_link"
                    type="url"
                    name="facebook_link"
                    value="{{ old('facebook_link', $brand->facebook_link) }}"
                >
            </div>

            <div class="brand-form-field">
                <label for="instagram_link">Instagram link</label>
                <input
                    id="instagram_link"
                    type="url"
                    name="instagram_link"
                    value="{{ old('instagram_link', $brand->instagram_link) }}"
                >
            </div>

            <div class="brand-form-field">
                <label for="whatsapp_link">WhatsApp link</label>
                <input
                    id="whatsapp_link"
                    type="url"
                    name="whatsapp_link"
                    value="{{ old('whatsapp_link', $brand->whatsapp_link) }}"
                >
            </div>

            <div class="brand-form-field full">
                <label for="address">Address</label>
                <textarea
                    id="address"
                    name="address"
                >{{ old('address', $brand->address) }}</textarea>
            </div>

            <div class="brand-form-field full">
                <label for="meta_title">Meta title</label>
                <input
                    id="meta_title"
                    type="text"
                    name="meta_title"
                    value="{{ old('meta_title', $brand->meta_title) }}"
                >
            </div>

            <div class="brand-form-field full">
                <label for="meta_description">Meta description</label>
                <textarea
                    id="meta_description"
                    name="meta_description"
                >{{ old('meta_description', $brand->meta_description) }}</textarea>
            </div>

            <div class="brand-form-field full">
                <label>
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $brand->is_active) ? 'checked' : '' }}
                    >
                    Active storefront
                </label>
            </div>
        </div>

        <div class="brand-form-actions">
            <button type="submit" class="admin-logout-button">
                Save Changes
            </button>

            <a
                href="{{ route('admin.brands.index') }}"
                class="admin-store-link"
            >
                Cancel
            </a>
        </div>
    </form>
@endsection