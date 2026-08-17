@extends('admin.layouts.app')

@section('title', 'Coupons | Grey Stone Admin')
@section('page-title', 'Coupon Management')
@section('page-subtitle', 'Manage checkout discounts and campaign codes')

@section('content')
<div
    id="couponCrudPage"
    data-store-url="{{ route('admin.coupons.store') }}"
    data-show-url="{{ route('admin.coupons.show', '__ID__') }}"
    data-update-url="{{ route('admin.coupons.update', '__ID__') }}"
    data-delete-url="{{ route('admin.coupons.destroy', '__ID__') }}"
>
    <section class="brand-page-card">
        <div class="brand-page-header">
            <div>
                <h2>Coupons Table</h2>

                <p>
                    Add, edit and control customer checkout coupons.
                </p>
            </div>

            <button
                type="button"
                class="brand-primary-button"
                id="openAddCouponModal"
            >
                <span>＋</span>
                Add Coupon
            </button>
        </div>

        <form
            class="admin-ajax-search"
            data-target="#couponTableBody"
            action="{{ route('admin.coupons.index') }}"
        >
            <div class="admin-search-grid">
                <div class="admin-search-field">
                    <label>Search</label>
                    <input
                        type="search"
                        name="search"
                        placeholder="Search code, title or brand"
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
                    <label>Type</label>
                    <select name="discount_type">
                        <option value="">All Types</option>
                        <option value="fixed">Fixed</option>
                        <option value="percentage">Percentage</option>
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
            <table class="brand-table coupon-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Coupon</th>
                        <th>Brand</th>
                        <th>Discount</th>
                        <th>Minimum</th>
                        <th>Used</th>
                        <th>Status</th>
                        <th>Validity</th>
                        <th class="brand-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody id="couponTableBody">
                    @include('admin.coupons.partials.table-rows')
                </tbody>
            </table>
        </div>
    </section>

    <div
        class="brand-modal"
        id="addCouponModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-modal="addCouponModal"
        ></div>

        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Add New Coupon</h3>
                    <p>Create a checkout discount code.</p>
                </div>

                <button
                    type="button"
                    class="brand-modal-close"
                    data-close-modal="addCouponModal"
                >
                    ×
                </button>
            </div>

            <form id="addCouponForm">
                @csrf

                <div class="brand-modal-body">
                    @include(
                        'admin.coupons.partials.form-fields',
                        ['formPrefix' => 'add']
                    )
                </div>

                <div class="brand-modal-footer">
                    <button
                        type="button"
                        class="brand-secondary-button"
                        data-close-modal="addCouponModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="brand-primary-button"
                        id="addCouponSubmitButton"
                    >
                        Add Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        class="brand-modal"
        id="editCouponModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-modal="editCouponModal"
        ></div>

        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Edit Coupon</h3>
                    <p>Update discount rules and availability.</p>
                </div>

                <button
                    type="button"
                    class="brand-modal-close"
                    data-close-modal="editCouponModal"
                >
                    ×
                </button>
            </div>

            <form id="editCouponForm">
                @csrf
                <input type="hidden" name="id" id="edit_coupon_id">

                <div class="brand-modal-body">
                    @include(
                        'admin.coupons.partials.form-fields',
                        ['formPrefix' => 'edit']
                    )
                </div>

                <div class="brand-modal-footer">
                    <button
                        type="button"
                        class="brand-secondary-button"
                        data-close-modal="editCouponModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="brand-primary-button"
                        id="editCouponSubmitButton"
                    >
                        Update Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        class="brand-modal"
        id="deleteCouponModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-modal="deleteCouponModal"
        ></div>

        <div class="brand-modal-dialog brand-modal-small">
            <div class="brand-modal-header">
                <div>
                    <h3>Delete Coupon?</h3>
                    <p id="deleteCouponText">
                        This coupon will be removed permanently.
                    </p>
                </div>

                <button
                    type="button"
                    class="brand-modal-close"
                    data-close-modal="deleteCouponModal"
                >
                    ×
                </button>
            </div>

            <div class="brand-modal-footer">
                <button
                    type="button"
                    class="brand-secondary-button"
                    data-close-modal="deleteCouponModal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="brand-danger-button"
                    id="confirmDeleteCouponButton"
                >
                    Delete Coupon
                </button>
            </div>
        </div>
    </div>

    <div class="brand-toast" id="couponToast">
        <span id="couponToastIcon">✓</span>
        <div>
            <strong id="couponToastTitle">Success</strong>
            <small id="couponToastMessage"></small>
        </div>
    </div>
</div>
@endsection
