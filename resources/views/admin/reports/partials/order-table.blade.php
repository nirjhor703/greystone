@if (in_array($filters['report_type'], ['overview', 'orders'], true))
    <section class="report-section">
        <div class="report-section-head">
            <h3>Order Report</h3>
            <span>{{ number_format($orders->count()) }} orders</span>
        </div>

        <div class="brand-table-wrapper">
            <table class="brand-table report-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Brand</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->invoice_number }}</td>
                            <td>
                                {{ $order->customer_name }}
                                <small class="order-table-muted">{{ $order->phone }}</small>
                            </td>
                            <td>{{ $order->brand?->name ?? '-' }}</td>
                            <td>{{ $order->status }}</td>
                            <td>৳{{ number_format($order->grand_total, 2) }}</td>
                            <td>{{ $order->created_at?->format('d M Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="brand-empty-state">
                                    <strong>No orders found</strong>
                                    <span>Try a different date range.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
