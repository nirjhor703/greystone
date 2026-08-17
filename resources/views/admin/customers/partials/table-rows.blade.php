@forelse ($customers as $customer)
    <tr>
        <td>
            <div class="brand-name-cell">
                <div class="brand-table-logo">
                    <span>{{ strtoupper(substr($customer['name'], 0, 1)) }}</span>
                </div>

                <div>
                    <strong>{{ $customer['name'] }}</strong>
                    <small>{{ $customer['last_invoice'] }}</small>
                </div>
            </div>
        </td>

        <td>
            <strong>{{ $customer['phone'] }}</strong>
            @if ($customer['alternative_phone'])
                <small class="order-table-muted">
                    Alt: {{ $customer['alternative_phone'] }}
                </small>
            @endif
        </td>

        <td>
            @if ($customer['email'])
                {{ $customer['email'] }}
            @else
                <span class="order-table-muted">No email</span>
            @endif
        </td>

        <td>
            {{ $customer['brand_label'] ?: '-' }}
            @if ($customer['brands']->count() > 3)
                <small class="order-table-muted">
                    +{{ $customer['brands']->count() - 3 }} more
                </small>
            @endif
        </td>

        <td>
            {{ $customer['district'] }}
            <small class="order-table-muted">
                {{ $customer['area_thana'] }}
            </small>
        </td>

        <td>
            <strong>{{ $customer['total_orders'] }}</strong>
            <small class="order-table-muted">
                {{ $customer['total_orders'] > 1 ? 'Repeat' : 'New' }}
            </small>
        </td>

        <td>
            <strong>৳{{ number_format($customer['total_spent'], 2) }}</strong>
        </td>

        <td>
            {{ $customer['last_order_at']?->format('d M Y') }}
            <small class="order-table-muted">
                {{ $customer['last_order_status'] }}
            </small>
        </td>

        <td>
            <div class="brand-table-actions customer-table-actions">
                <a
                    href="tel:{{ $customer['phone'] }}"
                    class="brand-action-button view"
                    title="Call customer"
                >
                    <i class="fa-solid fa-phone"></i>
                    Call
                </a>

                @if ($customer['email'])
                    <a
                        href="mailto:{{ $customer['email'] }}"
                        class="brand-action-button edit"
                        title="Email customer"
                    >
                        <i class="fa-solid fa-envelope"></i>
                        Mail
                    </a>
                @else
                    <button
                        type="button"
                        class="brand-action-button edit"
                        disabled
                        title="No email added"
                    >
                        <i class="fa-solid fa-envelope"></i>
                        Mail
                    </button>
                @endif

                <a
                    href="{{ $customer['orders_url'] }}"
                    class="brand-action-button view"
                    title="View customer orders"
                >
                    Orders
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr id="emptyCustomerRow">
        <td colspan="9">
            <div class="brand-empty-state">
                <strong>No customers found</strong>
                <span>Customer leads will appear after orders are placed.</span>
            </div>
        </td>
    </tr>
@endforelse

@if ($customers->hasPages())
    <tr>
        <td colspan="9">
            <div class="notification-pagination customer-pagination">
                {{ $customers->links() }}
            </div>
        </td>
    </tr>
@endif
