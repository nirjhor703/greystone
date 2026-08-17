@forelse ($orders as $order)
    <tr id="orderRow{{ $order->id }}">
        <td>
            <code class="brand-slug">{{ $order->invoice_number }}</code>
            <small class="order-table-muted">{{ $order->created_at?->format('Y-m-d H:i') }}</small>
        </td>

        <td>
            <div class="brand-name-cell">
                <div class="brand-table-logo">
                    <span>{{ strtoupper(substr($order->customer_name, 0, 1)) }}</span>
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
            <small class="order-table-muted">{{ $order->district }}</small>
        </td>

        <td>{{ $order->items->sum('quantity') }}</td>

        <td><strong>৳{{ number_format($order->grand_total, 2) }}</strong></td>

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
                <small class="order-table-muted">{{ $order->qcBy?->name }}</small>
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
                    <button type="button" class="brand-action-button view viewSteadfastButton" data-id="{{ $order->id }}">
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

                <button type="button" class="brand-action-button edit editOrderButton" data-id="{{ $order->id }}">
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
                <span>Try another search or filter.</span>
            </div>
        </td>
    </tr>
@endforelse
