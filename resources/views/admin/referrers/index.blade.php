@extends('admin.layouts.app')

@section('title', 'Referrers | Grey Stone Admin')
@section('page-title', 'Referrer Management')
@section('page-subtitle', 'Manage referral codes, commission rates and gift balances')

@section('content')
<div
    id="referrerCrudPage"
    data-store-url="{{ route('admin.referrers.store') }}"
    data-show-url="{{ route('admin.referrers.show', '__ID__') }}"
    data-check-code-url="{{ route('admin.referrers.check-code') }}"
    data-update-url="{{ route('admin.referrers.update', '__ID__') }}"
    data-delete-url="{{ route('admin.referrers.destroy', '__ID__') }}"
>
    <section class="brand-page-card">
        <div class="brand-page-header">
            <div>
                <h2>Referrers Table</h2>
                <p>Commission is calculated from product total after coupon discount. Delivery charge is excluded.</p>
            </div>

            <button type="button" class="brand-primary-button" id="openAddReferrerModal">
                <span>＋</span>
                Add Referrer
            </button>
        </div>

        <div class="customer-stat-grid">
            <article><span>Total referrers</span><strong>{{ number_format($referrers->total()) }}</strong></article>
            <article><span>Default commission</span><strong>1%</strong></article>
            <article><span>Welcome coupon</span><strong>10%</strong></article>
            <article><span>Total balance @include('admin.partials.info-help', ['key' => 'referrers_total_balance', 'text' => 'Commission balance plus gift balance total.'])</span><strong>৳{{ number_format(App\Models\Referrer::sum('balance') + App\Models\Referrer::sum('gift_balance'), 0) }}</strong></article>
        </div>

        <form class="admin-ajax-search" data-target="#referrerTableBody" action="{{ route('admin.referrers.index') }}">
            <div class="admin-search-grid">
                <div class="admin-search-field">
                    <label>Search</label>
                    <input type="search" name="search" placeholder="Search referrer, code, member or email" autocomplete="off">
                </div>

                <div class="admin-search-field">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button type="reset" class="brand-secondary-button">Reset</button>
            </div>
        </form>

        <div class="brand-table-wrapper">
            <table class="brand-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Referrer</th>
                        <th>Code</th>
                        <th>Commission @include('admin.partials.info-help', ['key' => 'referrer_table_commission', 'text' => 'Product total-er upor commission rate.'])</th>
                        <th>Successful</th>
                        <th>Commission Balance @include('admin.partials.info-help', ['key' => 'referrer_table_commission_balance', 'text' => 'Commission earning balance.'])</th>
                        <th>Gift Balance @include('admin.partials.info-help', ['key' => 'referrer_table_gift_balance', 'text' => 'Manual gift/bonus/help balance.'])</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="brand-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody id="referrerTableBody">
                    @include('admin.referrers.partials.table-rows', ['referrers' => $referrers])
                </tbody>
            </table>
        </div>
    </section>

    <div class="brand-modal" id="addReferrerModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="addReferrerModal"></div>
        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Add New Referrer</h3>
                    <p>Create a referral code with a custom commission rate.</p>
                </div>
                <button type="button" class="brand-modal-close" data-close-modal="addReferrerModal">×</button>
            </div>

            <form id="addReferrerForm">
                @csrf
                <div class="brand-modal-body">
                    @include('admin.referrers.partials.form-fields', ['formPrefix' => 'add'])
                </div>
                <div class="brand-modal-footer">
                    <button type="button" class="brand-secondary-button" data-close-modal="addReferrerModal">Cancel</button>
                    <button type="submit" class="brand-primary-button" id="addReferrerSubmitButton">Add Referrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="brand-modal" id="editReferrerModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="editReferrerModal"></div>
        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Edit Referrer</h3>
                    <p>Update commission, gift balance and status.</p>
                </div>
                <button type="button" class="brand-modal-close" data-close-modal="editReferrerModal">×</button>
            </div>

            <form id="editReferrerForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_referrer_id">
                <div class="brand-modal-body">
                    @include('admin.referrers.partials.form-fields', ['formPrefix' => 'edit'])
                </div>
                <div class="brand-modal-footer">
                    <button type="button" class="brand-secondary-button" data-close-modal="editReferrerModal">Cancel</button>
                    <button type="submit" class="brand-primary-button" id="editReferrerSubmitButton">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="brand-modal" id="deleteReferrerModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="deleteReferrerModal"></div>
        <div class="brand-modal-dialog">
            <div class="brand-modal-header">
                <div>
                    <h3>Delete Referrer</h3>
                    <p>This will remove the referral code from future use.</p>
                </div>
                <button type="button" class="brand-modal-close" data-close-modal="deleteReferrerModal">×</button>
            </div>
            <div class="brand-modal-body">
                <input type="hidden" id="delete_referrer_id">
                <p>Are you sure you want to delete <strong id="deleteReferrerName"></strong>?</p>
            </div>
            <div class="brand-modal-footer">
                <button type="button" class="brand-secondary-button" data-close-modal="deleteReferrerModal">Cancel</button>
                <button type="button" class="brand-danger-button" id="confirmDeleteReferrerButton">Delete Referrer</button>
            </div>
        </div>
    </div>

    <div class="brand-toast" id="referrerToast">
        <div class="brand-toast-icon" id="referrerToastIcon">✓</div>
        <div>
            <strong id="referrerToastTitle">Success</strong>
            <span id="referrerToastMessage">Referrer updated.</span>
        </div>
        <button type="button" id="closeReferrerToast">×</button>
    </div>
</div>
@endsection
