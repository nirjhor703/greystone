@extends('admin.layouts.app')

@section('title', 'Categories | Grey Stone Admin')
@section('page-title', 'Category Management')
@section('page-subtitle', 'Manage brand-specific product categories')

@section('content')
<div
    id="categoryCrudPage"
    data-store-url="{{ route('admin.categories.store') }}"
    data-show-url="{{ route('admin.categories.show', '__ID__') }}"
    data-update-url="{{ route('admin.categories.update', '__ID__') }}"
    data-delete-url="{{ route('admin.categories.destroy', '__ID__') }}"
>
    <section class="brand-page-card">
        <div class="brand-page-header">
            <div>
                <h2>Categories Table</h2>

                <p>
                    Add, edit and organize categories for every brand.
                </p>
            </div>

            <button
                type="button"
                class="brand-primary-button"
                id="openAddCategoryModal"
            >
                <span>＋</span>
                Add Category
            </button>
        </div>

        <form
            class="admin-ajax-search"
            data-target="#categoryTableBody"
            action="{{ route('admin.categories.index') }}"
        >
            <div class="admin-search-grid">
                <div class="admin-search-field">
                    <label>Search</label>
                    <input
                        type="search"
                        name="search"
                        placeholder="Search category, prefix, brand or description"
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

                <button type="reset" class="brand-secondary-button">
                    Reset
                </button>
            </div>
        </form>

        <div class="brand-table-wrapper">
            <table class="brand-table category-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Prefix</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th class="brand-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody id="categoryTableBody">
                    @forelse ($categories as $category)
                        <tr id="categoryRow{{ $category->id }}">
                            <td>
                                <span class="brand-id">
                                    #{{ $category->id }}
                                </span>
                            </td>

                            <td>
                                <div class="brand-name-cell">
                                    <div class="brand-table-logo">
                                        @if ($category->image)
                                            <img
                                                src="{{ Storage::url($category->image) }}"
                                                alt="{{ $category->name }}"
                                            >
                                        @else
                                            <span>
                                                {{ strtoupper(substr($category->name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div>
                                        <strong>{{ $category->name }}</strong>
                                        <small>/{{ $category->slug }}</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                {{ $category->brand?->name ?? '-' }}
                            </td>

                            <td>
                                <code class="brand-slug">
                                    {{ $category->prefix }}
                                </code>
                            </td>

                            <td>
                                <span
                                    class="brand-status-badge {{ $category->status === 'Active' ? 'active' : 'inactive' }}"
                                >
                                    {{ $category->status }}
                                </span>
                            </td>

                            <td>
                                {{ $category->description ?: '-' }}
                            </td>

                            <td>
                                <div class="brand-table-actions">
                                    <button
                                        type="button"
                                        class="brand-action-button edit editCategoryButton"
                                        data-id="{{ $category->id }}"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="brand-action-button delete deleteCategoryButton"
                                        data-id="{{ $category->id }}"
                                        data-name="{{ $category->name }}"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyCategoryRow">
                            <td colspan="7">
                                <div class="brand-empty-state">
                                    <strong>No categories found</strong>
                                    <span>Add your first category.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Add Modal --}}
    <div
        class="brand-modal"
        id="addCategoryModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-modal="addCategoryModal"
        ></div>

        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Add New Category</h3>
                    <p>Create a brand-specific product category.</p>
                </div>

                <button
                    type="button"
                    class="brand-modal-close"
                    data-close-modal="addCategoryModal"
                >
                    ×
                </button>
            </div>

            <form
                id="addCategoryForm"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="brand-modal-body">
                    @include(
                        'admin.categories.partials.form-fields',
                        ['formPrefix' => 'add']
                    )
                </div>

                <div class="brand-modal-footer">
                    <button
                        type="button"
                        class="brand-secondary-button"
                        data-close-modal="addCategoryModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="brand-primary-button"
                        id="addCategorySubmitButton"
                    >
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div
        class="brand-modal"
        id="editCategoryModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-modal="editCategoryModal"
        ></div>

        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Edit Category</h3>
                    <p>Update category details.</p>
                </div>

                <button
                    type="button"
                    class="brand-modal-close"
                    data-close-modal="editCategoryModal"
                >
                    ×
                </button>
            </div>

            <form
                id="editCategoryForm"
                enctype="multipart/form-data"
            >
                @csrf

                <input
                    type="hidden"
                    id="edit_category_id"
                >

                <div class="brand-modal-body">
                    @include(
                        'admin.categories.partials.form-fields',
                        ['formPrefix' => 'edit']
                    )
                </div>

                <div class="brand-modal-footer">
                    <button
                        type="button"
                        class="brand-secondary-button"
                        data-close-modal="editCategoryModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="brand-primary-button"
                        id="editCategorySubmitButton"
                    >
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div
        class="brand-modal"
        id="deleteCategoryModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-modal="deleteCategoryModal"
        ></div>

        <div class="brand-modal-dialog brand-delete-dialog">
            <div class="brand-delete-icon">!</div>

            <h3>Delete Category?</h3>

            <p>
                Are you sure you want to delete
                <strong id="deleteCategoryName"></strong>?
            </p>

            <span>This action cannot be undone.</span>

            <input
                type="hidden"
                id="delete_category_id"
            >

            <div class="brand-delete-actions">
                <button
                    type="button"
                    class="brand-secondary-button"
                    data-close-modal="deleteCategoryModal"
                >
                    No, Cancel
                </button>

                <button
                    type="button"
                    class="brand-danger-button"
                    id="confirmDeleteCategoryButton"
                >
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div class="brand-toast-container">
        <div class="brand-toast" id="categoryToast">
            <span
                class="brand-toast-icon"
                id="categoryToastIcon"
            >
                ✓
            </span>

            <div>
                <strong id="categoryToastTitle">
                    Success
                </strong>

                <p id="categoryToastMessage"></p>
            </div>

            <button
                type="button"
                id="closeCategoryToast"
            >
                ×
            </button>
        </div>
    </div>
</div>
@endsection
