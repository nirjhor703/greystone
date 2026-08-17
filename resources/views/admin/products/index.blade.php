@extends('admin.layouts.app')

@section('title', 'Products | Grey Stone Admin')
@section('page-title', 'Product Management')
@section('page-subtitle', 'Manage multi-brand products and product galleries')

@section('content')
<div
    id="productCrudPage"
    data-store-url="{{ route('admin.products.store') }}"
    data-show-url="{{ route('admin.products.show', '__ID__') }}"
    data-update-url="{{ route('admin.products.update', '__ID__') }}"
    data-delete-url="{{ route('admin.products.destroy', '__ID__') }}"
>
    <section class="brand-page-card">
        <div class="brand-page-header">
            <div>
                <h2>Products Table</h2>

                <p>
                    Add and manage products, pricing, stock and multiple images.
                </p>
            </div>

            <button
                type="button"
                class="brand-primary-button"
                id="openAddProductModal"
            >
                <span>＋</span>
                Add Product
            </button>
        </div>

        <form
            class="admin-ajax-search"
            data-target="#productTableBody"
            action="{{ route('admin.products.index') }}"
        >
            <div class="admin-search-grid">
                <div class="admin-search-field">
                    <label>Search</label>
                    <input
                        type="search"
                        name="search"
                        placeholder="Search product, code, brand or category"
                        autocomplete="off"
                    >
                </div>

                <div class="admin-search-field">
                    <label>Brand</label>
                    <select name="brand_id">
                        <option value="">All Brands</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}">
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="admin-search-field">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="admin-search-field">
                    <label>Stock</label>
                    <select name="stock">
                        <option value="">All Stock</option>
                        <option value="low">Low Stock</option>
                        <option value="out">Out of Stock</option>
                    </select>
                </div>

                <button type="reset" class="brand-secondary-button">
                    Reset
                </button>
            </div>
        </form>

        <div class="brand-table-wrapper">
            <table class="brand-table product-admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Code</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th class="brand-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody id="productTableBody">
                    @forelse ($products as $product)
                        @php
                            $displayImage = $product->primaryImage
                                ?? $product->images->first();
                        @endphp

                        <tr id="productRow{{ $product->id }}">
                            <td>
                                <span class="brand-id">
                                    #{{ $product->id }}
                                </span>
                            </td>

                            <td>
                                <div class="brand-name-cell">
                                    <div class="product-table-image">
                                        @if ($displayImage)
                                            <img
                                                src="{{ Storage::url($displayImage->image) }}"
                                                alt="{{ $product->name }}"
                                            >
                                        @else
                                            <span>
                                                {{ strtoupper(substr($product->name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div>
                                        <strong>{{ $product->name }}</strong>

                                        <small>
                                            /{{ $product->slug }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                {{ $product->brand?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $product->category?->name ?? '-' }}
                            </td>

                            <td>
                                <code class="brand-slug">
                                    {{ $product->product_code }}
                                </code>
                            </td>

                            <td>
                                <div class="product-price-cell">
                                    @if ($product->sale_price)
                                        <strong>
                                            ৳{{ number_format($product->sale_price, 2) }}
                                        </strong>

                                        <del>
                                            ৳{{ number_format($product->regular_price, 2) }}
                                        </del>
                                    @else
                                        <strong>
                                            ৳{{ number_format($product->regular_price, 2) }}
                                        </strong>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <span
                                    class="brand-status-badge {{ $product->stock_quantity > 0 ? 'active' : 'inactive' }}"
                                >
                                    {{ $product->stock_quantity }}
                                </span>
                            </td>

                            <td>
                                <span
                                    class="brand-status-badge {{ $product->is_featured ? 'active' : 'inactive' }}"
                                >
                                    {{ $product->is_featured ? 'Yes' : 'No' }}
                                </span>
                            </td>

                            <td>
                                <span
                                    class="brand-status-badge {{ $product->status === 'Active' ? 'active' : 'inactive' }}"
                                >
                                    {{ $product->status }}
                                </span>
                            </td>

                            <td>
                                <div class="brand-table-actions">
                                    <button
                                        type="button"
                                        class="brand-action-button edit editProductButton"
                                        data-id="{{ $product->id }}"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="brand-action-button delete deleteProductButton"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyProductRow">
                            <td colspan="10">
                                <div class="brand-empty-state">
                                    <strong>No products found</strong>
                                    <span>Add your first product.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Add Product Modal --}}
    <div
        class="brand-modal"
        id="addProductModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-modal="addProductModal"
        ></div>

        <div class="brand-modal-dialog product-modal-dialog">
            <div class="brand-modal-header">
                <div>
                    <h3>Add New Product</h3>
                    <p>Create a product with multiple gallery images.</p>
                </div>

                <button
                    type="button"
                    class="brand-modal-close"
                    data-close-modal="addProductModal"
                >
                    ×
                </button>
            </div>

            <form
                id="addProductForm"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="brand-modal-body">
                    @include(
                        'admin.products.partials.form-fields',
                        ['formPrefix' => 'add']
                    )
                </div>

                <div class="brand-modal-footer">
                    <button
                        type="button"
                        class="brand-secondary-button"
                        data-close-modal="addProductModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="brand-primary-button"
                        id="addProductSubmitButton"
                    >
                        Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Product Modal --}}
    <div
        class="brand-modal"
        id="editProductModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-modal="editProductModal"
        ></div>

        <div class="brand-modal-dialog product-modal-dialog">
            <div class="brand-modal-header">
                <div>
                    <h3>Edit Product</h3>
                    <p>Update details, stock and product gallery.</p>
                </div>

                <button
                    type="button"
                    class="brand-modal-close"
                    data-close-modal="editProductModal"
                >
                    ×
                </button>
            </div>

            <form
                id="editProductForm"
                enctype="multipart/form-data"
            >
                @csrf

                <input
                    type="hidden"
                    id="edit_product_id"
                >

                <div class="brand-modal-body">
                    @include(
                        'admin.products.partials.form-fields',
                        ['formPrefix' => 'edit']
                    )
                </div>

                <div class="brand-modal-footer">
                    <button
                        type="button"
                        class="brand-secondary-button"
                        data-close-modal="editProductModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="brand-primary-button"
                        id="editProductSubmitButton"
                    >
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div
        class="brand-modal"
        id="deleteProductModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-modal="deleteProductModal"
        ></div>

        <div class="brand-modal-dialog brand-delete-dialog">
            <div class="brand-delete-icon">!</div>

            <h3>Delete Product?</h3>

            <p>
                Are you sure you want to delete
                <strong id="deleteProductName"></strong>?
            </p>

            <span>
                Product images will also be deleted.
            </span>

            <input
                type="hidden"
                id="delete_product_id"
            >

            <div class="brand-delete-actions">
                <button
                    type="button"
                    class="brand-secondary-button"
                    data-close-modal="deleteProductModal"
                >
                    No, Cancel
                </button>

                <button
                    type="button"
                    class="brand-danger-button"
                    id="confirmDeleteProductButton"
                >
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div class="brand-toast-container">
        <div class="brand-toast" id="productToast">
            <span
                class="brand-toast-icon"
                id="productToastIcon"
            >
                ✓
            </span>

            <div>
                <strong id="productToastTitle">
                    Success
                </strong>

                <p id="productToastMessage"></p>
            </div>

            <button
                type="button"
                id="closeProductToast"
            >
                ×
            </button>
        </div>
    </div>
</div>
@endsection
