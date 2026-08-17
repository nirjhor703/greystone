@extends('admin.layouts.app')

@section('title', 'Orders | Grey Stone Admin')
@section('page-title', 'Order Management')
@section('page-subtitle', 'Manage customer orders, invoices and Steadfast consignments')

@section('content')
@php
    $statuses = App\Models\Order::adminStatuses();

    $paymentStatuses = [
        App\Models\Order::PAYMENT_UNPAID,
        App\Models\Order::PAYMENT_PAID,
    ];
@endphp

<div
    id="orderCrudPage"
    data-store-url="{{ route('admin.orders.store') }}"
    data-show-url="{{ route('admin.orders.show', '__ID__') }}"
    data-update-url="{{ route('admin.orders.update', '__ID__') }}"
    data-delete-url="{{ route('admin.orders.destroy', '__ID__') }}"
    data-send-url="{{ route('admin.orders.send-to-steadfast', '__ID__') }}"
    data-qc-passed-url="{{ route('admin.orders.qc-passed', '__ID__') }}"
    data-qc-issue-url="{{ route('admin.orders.qc-issue', '__ID__') }}"
    data-resolve-qc-url="{{ route('admin.orders.resolve-qc-issue', '__ID__') }}"
    data-steadfast-url="{{ route('admin.orders.steadfast', '__ID__') }}"
    data-invoice-url="{{ route('admin.orders.invoice', '__ID__') }}"
>
    <section class="brand-page-card">
        <div class="brand-page-header">
            <div>
                <h2>Orders Table</h2>

                <p>
                    Add, edit and send customer orders to Steadfast.
                </p>
            </div>

            <button
                type="button"
                class="brand-primary-button"
                id="openAddOrderModal"
            >
                <span>＋</span>
                Add Order
            </button>
        </div>

        <form
            class="admin-ajax-search"
            data-target="#orderTableBody"
            action="{{ route('admin.orders.index') }}"
        >
            <div class="admin-search-grid">
                <div class="admin-search-field">
                    <label>Search</label>
                    <input
                        type="search"
                        name="search"
                        placeholder="Search invoice, customer, phone, courier or district"
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
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="admin-search-field">
                    <label>Courier</label>
                    <select name="courier_status">
                        <option value="">All Courier</option>
                        <option value="not_sent">Not Sent</option>
                        <option value="in_review">In Review</option>
                        <option value="pending">Pending</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="admin-search-field">
                    <label>QC</label>
                    <select name="qc_status">
                        <option value="">All QC</option>
                        <option value="{{ App\Models\Order::QC_NOT_CHECKED }}">QC Not Checked</option>
                        <option value="{{ App\Models\Order::QC_PASSED }}">QC Passed</option>
                        <option value="{{ App\Models\Order::QC_ISSUE }}">QC Issue</option>
                    </select>
                </div>

                <button type="reset" class="brand-secondary-button">
                    Reset
                </button>
            </div>
        </form>

        <div class="brand-table-wrapper">
            <table class="brand-table order-admin-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Brand</th>
                        <th>Delivery</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>QC</th>
                        <th>Courier</th>
                        <th class="brand-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody id="orderTableBody">
                    @forelse ($orders as $order)
                        <tr id="orderRow{{ $order->id }}">
                            <td>
                                <code class="brand-slug">
                                    {{ $order->invoice_number }}
                                </code>

                                <small class="order-table-muted">
                                    {{ $order->created_at?->format('Y-m-d H:i') }}
                                </small>
                            </td>

                            <td>
                                <div class="brand-name-cell">
                                    <div class="brand-table-logo">
                                        <span>
                                            {{ strtoupper(substr($order->customer_name, 0, 1)) }}
                                        </span>
                                    </div>

                                    <div>
                                        <strong>{{ $order->customer_name }}</strong>
                                        <small>{{ $order->phone }}</small>
                                    </div>
                                </div>
                            </td>

                            <td>{{ $order->brand?->name ?? '-' }}</td>

                            <td>
                                {{ $order->delivery_area === 'inside_dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }}

                                <small class="order-table-muted">
                                    {{ $order->district }}
                                </small>
                            </td>

                            <td>{{ $order->items->sum('quantity') }}</td>

                            <td>
                                <strong>
                                    ৳{{ number_format($order->grand_total, 2) }}
                                </strong>
                            </td>

                            <td>
                                <span class="brand-status-badge {{ $order->statusBadgeClass() }}">
                                    {{ $order->status }}
                                </span>
                            </td>

                            <td>
                                <span class="brand-status-badge {{ $order->qcStatusBadgeClass() }}">
                                    {{ $order->qcStatusLabel() }}
                                </span>
                                @if ($order->qc_by_user_id)
                                    <small class="order-table-muted">
                                        {{ $order->qcBy?->name }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                <span class="brand-status-badge {{ $order->sent_to_steadfast_at ? 'active' : 'inactive' }}">
                                    {{ $order->sent_to_steadfast_at ? ($order->courier_status ?: 'sent') : 'Not Sent' }}
                                </span>
                            </td>

                            <td>
                                <div class="brand-table-actions order-table-actions">
                                    <a
                                        href="{{ route('admin.orders.invoice', $order) }}"
                                        target="_blank"
                                        class="brand-action-button view"
                                    >
                                        Invoice
                                    </a>

                                    <button
                                        type="button"
                                        class="brand-action-button view orderActivityButton"
                                        data-id="{{ $order->id }}"
                                    >
                                        Activity
                                    </button>

                                    @if ($order->sent_to_steadfast_at)
                                        <button
                                            type="button"
                                            class="brand-action-button view viewSteadfastButton"
                                            data-id="{{ $order->id }}"
                                        >
                                            Sent
                                        </button>
                                    @else
                                        @if ($order->status === App\Models\Order::STATUS_CONFIRMED)
                                            @if (($order->qc_status ?: App\Models\Order::QC_NOT_CHECKED) === App\Models\Order::QC_ISSUE)
                                                <button
                                                    type="button"
                                                    class="brand-action-button edit resolveQcButton"
                                                    data-id="{{ $order->id }}"
                                                    data-invoice="{{ $order->invoice_number }}"
                                                >
                                                    Resolve QC
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    class="brand-action-button edit qcPassedButton"
                                                    data-id="{{ $order->id }}"
                                                >
                                                    QC Passed
                                                </button>

                                                <button
                                                    type="button"
                                                    class="brand-action-button delete qcIssueButton"
                                                    data-id="{{ $order->id }}"
                                                    data-invoice="{{ $order->invoice_number }}"
                                                >
                                                    QC Issue
                                                </button>
                                            @endif
                                        @endif

                                        <button
                                            type="button"
                                            class="brand-action-button view sendSteadfastButton"
                                            data-id="{{ $order->id }}"
                                            data-invoice="{{ $order->invoice_number }}"
                                            @disabled(($order->qc_status ?: App\Models\Order::QC_NOT_CHECKED) !== App\Models\Order::QC_PASSED)
                                        >
                                            Send to Steadfast
                                        </button>
                                    @endif

                                    <button
                                        type="button"
                                        class="brand-action-button edit editOrderButton"
                                        data-id="{{ $order->id }}"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="brand-action-button delete deleteOrderButton"
                                        data-id="{{ $order->id }}"
                                        data-invoice="{{ $order->invoice_number }}"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyOrderRow">
                            <td colspan="10">
                                <div class="brand-empty-state">
                                    <strong>No orders found</strong>
                                    <span>Add your first order.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @foreach (['add' => 'Add New Order', 'edit' => 'Edit Order'] as $prefix => $title)
        <div
            class="brand-modal"
            id="{{ $prefix }}OrderModal"
            aria-hidden="true"
        >
            <div
                class="brand-modal-backdrop"
                data-close-modal="{{ $prefix }}OrderModal"
            ></div>

            <div class="brand-modal-dialog brand-modal-large">
                <div class="brand-modal-header">
                    <div>
                        <h3>{{ $title }}</h3>
                        <p>Use the same order fields customers submit from storefront.</p>
                    </div>

                    <button
                        type="button"
                        class="brand-modal-close"
                        data-close-modal="{{ $prefix }}OrderModal"
                    >
                        ×
                    </button>
                </div>

                <form id="{{ $prefix }}OrderForm">
                    @csrf

                    @if ($prefix === 'edit')
                        <input type="hidden" id="edit_order_id">
                    @endif

                    <div class="brand-modal-body">
                        <div class="brand-form-sections">
                            <section class="brand-form-section">
                                <div class="brand-form-section-title">
                                    <h4>Customer Details</h4>
                                    <p>Name, phone and payment status.</p>
                                </div>

                                <div class="brand-form-grid">
                                    <div class="brand-form-field">
                                        <label>Brand <span>*</span></label>
                                        <select name="brand_id" id="{{ $prefix }}_brand_id">
                                            <option value="">Select Brand</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}">
                                                    {{ $brand->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="brand-field-error brand_id_error"></small>
                                    </div>

                                    <div class="brand-form-field">
                                        <label>Customer Name <span>*</span></label>
                                        <input type="text" name="customer_name" id="{{ $prefix }}_customer_name" maxlength="100">
                                        <small class="brand-field-error customer_name_error"></small>
                                    </div>

                                    <div class="brand-form-field">
                                        <label>Phone <span>*</span></label>
                                        <input type="text" name="phone" id="{{ $prefix }}_phone" maxlength="11">
                                        <small class="brand-field-error phone_error"></small>
                                    </div>

                                    <div class="brand-form-field">
                                        <label>Alternative Phone</label>
                                        <input type="text" name="alternative_phone" id="{{ $prefix }}_alternative_phone" maxlength="11">
                                        <small class="brand-field-error alternative_phone_error"></small>
                                    </div>

                                    <div class="brand-form-field">
                                        <label>Email</label>
                                        <input type="email" name="customer_email" id="{{ $prefix }}_customer_email" maxlength="150">
                                        <small class="brand-field-error customer_email_error"></small>
                                    </div>

                                    <div class="brand-form-field">
                                        <label>Order Status <span>*</span></label>
                                        <select name="status" id="{{ $prefix }}_status">
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        <small class="brand-field-error status_error"></small>
                                    </div>

                                    <div class="brand-form-field">
                                        <label>Payment Status <span>*</span></label>
                                        <select name="payment_status" id="{{ $prefix }}_payment_status">
                                            @foreach ($paymentStatuses as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        <small class="brand-field-error payment_status_error"></small>
                                    </div>
                                </div>
                            </section>

                            @if ($prefix === 'edit')
                                <section class="brand-form-section order-qc-panel">
                                    <div class="brand-form-section-title">
                                        <h4>QC Check</h4>
                                        <p>Use this after customer confirmation and physical product check.</p>
                                    </div>

                                    <div class="order-qc-summary">
                                        <div>
                                            <span>Current QC Status</span>
                                            <strong id="edit_qc_status_label">QC Not Checked</strong>
                                        </div>

                                        <div>
                                            <span>Checked By</span>
                                            <strong id="edit_qc_by">-</strong>
                                        </div>

                                        <div>
                                            <span>Checked At</span>
                                            <strong id="edit_qc_checked_at">-</strong>
                                        </div>
                                    </div>

                                    <p class="order-qc-note" id="edit_qc_issue_note_wrap">
                                        <strong>Issue:</strong>
                                        <span id="edit_qc_issue_note">-</span>
                                    </p>

                                    <div class="order-qc-actions">
                                        <button
                                            type="button"
                                            class="brand-action-button edit qcPassedButton"
                                            id="editQcPassedButton"
                                        >
                                            QC Passed
                                        </button>

                                        <button
                                            type="button"
                                            class="brand-action-button delete qcIssueButton"
                                            id="editQcIssueButton"
                                        >
                                            QC Issue
                                        </button>

                                        <button
                                            type="button"
                                            class="brand-action-button edit resolveQcButton"
                                            id="editResolveQcButton"
                                        >
                                            Resolve QC
                                        </button>
                                    </div>

                                    <small class="order-table-muted" id="edit_qc_help_text">
                                        Order must be Confirmed before QC can be updated.
                                    </small>
                                </section>
                            @endif

                            <section class="brand-form-section">
                                <div class="brand-form-section-title">
                                    <h4>Delivery Address</h4>
                                    <p>Inside Dhaka locks district to Dhaka; outside Dhaka needs district selection.</p>
                                </div>

                                <div class="brand-form-grid">
                                    <div class="brand-form-field">
                                        <label>Delivery Area <span>*</span></label>
                                        <select name="delivery_area" id="{{ $prefix }}_delivery_area">
                                            <option value="inside_dhaka">Inside Dhaka - ৳80</option>
                                            <option value="outside_dhaka">Outside Dhaka - ৳130</option>
                                        </select>
                                        <small class="brand-field-error delivery_area_error"></small>
                                    </div>

                                    <div class="brand-form-field">
                                        <label>District <span>*</span></label>
                                        <input type="text" name="district" id="{{ $prefix }}_district" maxlength="100">
                                        <small class="brand-field-error district_error"></small>
                                    </div>

                                    <div class="brand-form-field">
                                        <label>Area / Thana <span>*</span></label>
                                        <input type="text" name="area_thana" id="{{ $prefix }}_area_thana" maxlength="150">
                                        <small class="brand-field-error area_thana_error"></small>
                                    </div>

                                    <div class="brand-form-field">
                                        <label>Road Number <span>*</span></label>
                                        <input type="text" name="road_no" id="{{ $prefix }}_road_no" maxlength="100">
                                        <small class="brand-field-error road_no_error"></small>
                                    </div>

                                    <div class="brand-form-field">
                                        <label>House Number <span>*</span></label>
                                        <input type="text" name="house_no" id="{{ $prefix }}_house_no" maxlength="100">
                                        <small class="brand-field-error house_no_error"></small>
                                    </div>

                                    <div class="brand-form-field brand-full-field">
                                        <label>Full Address <span>*</span></label>
                                        <textarea name="full_address" id="{{ $prefix }}_full_address" rows="3"></textarea>
                                        <small class="brand-field-error full_address_error"></small>
                                    </div>

                                    <div class="brand-form-field brand-full-field">
                                        <label>Order Note</label>
                                        <textarea name="order_note" id="{{ $prefix }}_order_note" rows="2"></textarea>
                                        <small class="brand-field-error order_note_error"></small>
                                    </div>
                                </div>
                            </section>

                            <section class="brand-form-section">
                                <div class="brand-form-section-title">
                                    <h4>Order Items</h4>
                                    <p>Add one or more products to this order.</p>
                                </div>

                                <div
                                    class="order-item-list"
                                    id="{{ $prefix }}_order_items"
                                ></div>

                                <button
                                    type="button"
                                    class="brand-secondary-button order-add-item-button"
                                    data-add-order-item="{{ $prefix }}"
                                >
                                    Add Item
                                </button>

                                <small class="brand-field-error items_error"></small>
                            </section>
                        </div>
                    </div>

                    <div class="brand-modal-footer">
                        <button
                            type="button"
                            class="brand-secondary-button"
                            data-close-modal="{{ $prefix }}OrderModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="brand-primary-button"
                            id="{{ $prefix }}OrderSubmitButton"
                        >
                            {{ $prefix === 'add' ? 'Add Order' : 'Update Order' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <div class="brand-modal" id="sendSteadfastModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="sendSteadfastModal"></div>
        <div class="brand-modal-dialog brand-delete-dialog">
            <div class="brand-delete-icon">!</div>
            <h3>Send to Steadfast?</h3>
            <p>
                Are you sure this order will go to Steadfast consignment?
                <strong id="sendSteadfastInvoice"></strong>
            </p>
            <input type="hidden" id="send_steadfast_order_id">
            <div class="brand-delete-actions">
                <button type="button" class="brand-secondary-button" data-close-modal="sendSteadfastModal">
                    No, Cancel
                </button>
                <button type="button" class="brand-primary-button" id="confirmSendSteadfastButton">
                    Yes, Send
                </button>
            </div>
        </div>
    </div>

    <div class="brand-modal" id="steadfastDetailsModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="steadfastDetailsModal"></div>
        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Steadfast Details</h3>
                    <p>Sent time, sender and courier response.</p>
                </div>
                <button type="button" class="brand-modal-close" data-close-modal="steadfastDetailsModal">×</button>
            </div>
            <div class="brand-modal-body">
                <div class="order-details-grid" id="steadfastDetailsBody"></div>
            </div>
        </div>
    </div>

    <div class="brand-modal" id="orderActivityModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="orderActivityModal"></div>
        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Order Activity</h3>
                    <p>Timeline of admin actions for this order.</p>
                </div>
                <button type="button" class="brand-modal-close" data-close-modal="orderActivityModal">×</button>
            </div>
            <div class="brand-modal-body">
                <div class="order-activity-list" id="orderActivityBody"></div>
            </div>
        </div>
    </div>

    <div class="brand-modal" id="qcIssueModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="qcIssueModal"></div>
        <div class="brand-modal-dialog brand-delete-dialog">
            <div class="brand-delete-icon">!</div>
            <h3>Mark QC Issue?</h3>
            <p>
                Add the issue note for
                <strong id="qcIssueInvoice"></strong>.
            </p>
            <input type="hidden" id="qc_issue_order_id">
            <textarea
                class="order-action-note"
                id="qcIssueNote"
                rows="4"
                maxlength="1000"
                placeholder="Example: Wrong size found, stock mismatch, product defect..."
            ></textarea>
            <div class="brand-delete-actions">
                <button type="button" class="brand-secondary-button" data-close-modal="qcIssueModal">
                    No, Cancel
                </button>
                <button type="button" class="brand-danger-button" id="confirmQcIssueButton">
                    Mark Issue
                </button>
            </div>
        </div>
    </div>

    <div class="brand-modal" id="resolveQcModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="resolveQcModal"></div>
        <div class="brand-modal-dialog brand-delete-dialog">
            <div class="brand-delete-icon">!</div>
            <h3>Resolve QC Issue?</h3>
            <p>
                This will send
                <strong id="resolveQcInvoice"></strong>
                back to QC queue.
            </p>
            <input type="hidden" id="resolve_qc_order_id">
            <textarea
                class="order-action-note"
                id="resolveQcNote"
                rows="4"
                maxlength="1000"
                placeholder="Example: Issue fixed, correct product picked..."
            ></textarea>
            <div class="brand-delete-actions">
                <button type="button" class="brand-secondary-button" data-close-modal="resolveQcModal">
                    No, Cancel
                </button>
                <button type="button" class="brand-primary-button" id="confirmResolveQcButton">
                    Resolve
                </button>
            </div>
        </div>
    </div>

    <div class="brand-modal" id="deleteOrderModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="deleteOrderModal"></div>
        <div class="brand-modal-dialog brand-delete-dialog">
            <div class="brand-delete-icon">!</div>
            <h3>Delete Order?</h3>
            <p>
                Are you sure you want to delete
                <strong id="deleteOrderInvoice"></strong>?
            </p>
            <input type="hidden" id="delete_order_id">
            <div class="brand-delete-actions">
                <button type="button" class="brand-secondary-button" data-close-modal="deleteOrderModal">
                    No, Cancel
                </button>
                <button type="button" class="brand-danger-button" id="confirmDeleteOrderButton">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    <template id="orderItemTemplate">
        <article class="order-item-editor" data-order-item>
            <div class="brand-form-grid">
                <div class="brand-form-field">
                    <label>Product <span>*</span></label>
                    <select data-order-product>
                        <option value="">Select Product</option>
                        @foreach ($products as $product)
                            <option
                                value="{{ $product->id }}"
                                data-brand-id="{{ $product->brand_id }}"
                                data-price="{{ $product->sale_price ?: $product->regular_price }}"
                            >
                                {{ $product->brand?->name }} - {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="brand-form-field">
                    <label>Color</label>
                    <input type="text" data-order-color maxlength="100">
                </div>
                <div class="brand-form-field">
                    <label>Size</label>
                    <input type="text" data-order-size maxlength="20">
                </div>
                <div class="brand-form-field">
                    <label>Quantity <span>*</span></label>
                    <input type="text" data-order-quantity value="1">
                </div>
                <div class="brand-form-field">
                    <label>Unit Price <span>*</span></label>
                    <input type="text" data-order-unit-price value="0">
                </div>
            </div>
            <button type="button" class="brand-action-button delete" data-remove-order-item>
                Remove
            </button>
        </article>
    </template>

    <div class="brand-toast-container">
        <div class="brand-toast" id="orderToast">
            <span class="brand-toast-icon" id="orderToastIcon">✓</span>
            <div>
                <strong id="orderToastTitle">Success</strong>
                <p id="orderToastMessage"></p>
            </div>
            <button type="button" id="closeOrderToast">×</button>
        </div>
    </div>
</div>
@endsection
