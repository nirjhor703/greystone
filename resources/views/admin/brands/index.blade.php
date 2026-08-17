@extends('admin.layouts.app')

@section('title', 'Brands | Grey Stone Admin')
@section('page-title', 'Brand Management')
@section('page-subtitle', 'Manage all storefront identities')

@section('content')
<div
    id="brandCrudPage"
    data-store-url="{{ route('admin.brands.store') }}"
    data-show-url="{{ route('admin.brands.show', '__ID__') }}"
    data-update-url="{{ route('admin.brands.update', '__ID__') }}"
    data-delete-url="{{ route('admin.brands.destroy', '__ID__') }}"
>
    <section class="brand-page-card">
        <div class="brand-page-header">
            <div>
                <h2>Brands Table</h2>

                <p>
                    Add, edit and manage all storefront brands.
                </p>
            </div>

            <button
                type="button"
                class="brand-primary-button"
                id="openAddBrandModal"
            >
                <span>＋</span>
                Add Brand
            </button>
        </div>

        <form
            class="admin-ajax-search"
            data-target="#brandTableBody"
            action="{{ route('admin.brands.index') }}"
        >
            <div class="admin-search-grid">
                <div class="admin-search-field">
                    <label>Search</label>
                    <input
                        type="search"
                        name="search"
                        placeholder="Search name, slug, email or phone"
                        autocomplete="off"
                    >
                </div>

                <div class="admin-search-field">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button type="reset" class="brand-secondary-button">
                    Reset
                </button>
            </div>
        </form>

        <div class="brand-table-wrapper">
            <table class="brand-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand</th>
                        <th>Slug</th>
                        <th>Primary Color</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="brand-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody id="brandTableBody">
                    @forelse ($brands as $brand)
                        <tr id="brandRow{{ $brand->id }}">
                            <td>
                                <span class="brand-id">
                                    #{{ $brand->id }}
                                </span>
                            </td>

                            <td>
                                <div class="brand-name-cell">
                                    <div class="brand-table-logo">
                                        @if ($brand->logo)
                                            <img
                                                src="{{ Storage::url($brand->logo) }}"
                                                alt="{{ $brand->name }}"
                                            >
                                        @else
                                            <span>
                                                {{ strtoupper(substr($brand->name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div>
                                        <strong class="brand-row-name">
                                            {{ $brand->name }}
                                        </strong>

                                        <small class="brand-row-email">
                                            {{ $brand->email ?? 'No email added' }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <code class="brand-slug">
                                    {{ $brand->slug }}
                                </code>
                            </td>

                            <td>
                                <div class="brand-color-cell">
                                    <span
                                        class="brand-color-dot"
                                        style="background: {{ $brand->primary_color }}"
                                    ></span>

                                    <span class="brand-row-color">
                                        {{ $brand->primary_color }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="brand-row-contact">
                                    {{ $brand->contact_number ?? 'Not added' }}
                                </span>
                            </td>

                            <td>
                                <span
                                    class="brand-status-badge {{ $brand->is_active ? 'active' : 'inactive' }}"
                                >
                                    {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <div class="brand-table-actions">
                                    <a
                                        href="{{ route('brand.show', $brand->slug) }}"
                                        target="_blank"
                                        class="brand-action-button view"
                                        title="View Store"
                                    >
                                        View
                                    </a>

                                    <button
                                        type="button"
                                        class="brand-action-button edit editBrandButton"
                                        data-id="{{ $brand->id }}"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="brand-action-button delete deleteBrandButton"
                                        data-id="{{ $brand->id }}"
                                        data-name="{{ $brand->name }}"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyBrandRow">
                            <td colspan="7">
                                <div class="brand-empty-state">
                                    <strong>No brands found</strong>
                                    <span>Add your first brand to get started.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Add modal --}}
    <div class="brand-modal" id="addBrandModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="addBrandModal"></div>

        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Add New Brand</h3>
                    <p>Create a new storefront identity.</p>
                </div>

                <button
                    type="button"
                    class="brand-modal-close"
                    data-close-modal="addBrandModal"
                >
                    ×
                </button>
            </div>

            <form id="addBrandForm" enctype="multipart/form-data">
                @csrf

                <div class="brand-modal-body">
                    @include('admin.brands.partials.form-fields', [
                        'formPrefix' => 'add',
                    ])
                </div>

                <div class="brand-modal-footer">
                    <button
                        type="button"
                        class="brand-secondary-button"
                        data-close-modal="addBrandModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="brand-primary-button"
                        id="addBrandSubmitButton"
                    >
                        Add Brand
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit modal --}}
    <div class="brand-modal" id="editBrandModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="editBrandModal"></div>

        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Edit Brand</h3>
                    <p>Update brand identity and storefront settings.</p>
                </div>

                <button
                    type="button"
                    class="brand-modal-close"
                    data-close-modal="editBrandModal"
                >
                    ×
                </button>
            </div>

            <form id="editBrandForm" enctype="multipart/form-data">
                @csrf

                <input type="hidden" id="edit_brand_id">

                <div class="brand-modal-body">
                    @include('admin.brands.partials.form-fields', [
                        'formPrefix' => 'edit',
                    ])
                </div>

                <div class="brand-modal-footer">
                    <button
                        type="button"
                        class="brand-secondary-button"
                        data-close-modal="editBrandModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="brand-primary-button"
                        id="editBrandSubmitButton"
                    >
                        Update Brand
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete modal --}}
    <div class="brand-modal" id="deleteBrandModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="deleteBrandModal"></div>

        <div class="brand-modal-dialog brand-delete-dialog">
            <div class="brand-delete-icon">!</div>

            <h3>Delete Brand?</h3>

            <p>
                Are you sure you want to delete
                <strong id="deleteBrandName"></strong>?
            </p>

            <span>
                This action cannot be undone.
            </span>

            <input type="hidden" id="delete_brand_id">

            <div class="brand-delete-actions">
                <button
                    type="button"
                    class="brand-secondary-button"
                    data-close-modal="deleteBrandModal"
                >
                    No, Cancel
                </button>

                <button
                    type="button"
                    class="brand-danger-button"
                    id="confirmDeleteBrandButton"
                >
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div class="brand-toast-container">
        <div class="brand-toast" id="brandToast">
            <span class="brand-toast-icon" id="brandToastIcon">✓</span>

            <div>
                <strong id="brandToastTitle">Success</strong>
                <p id="brandToastMessage"></p>
            </div>

            <button type="button" id="closeBrandToast">×</button>
        </div>
    </div>
</div>
@endsection
