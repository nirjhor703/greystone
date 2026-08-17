@if (in_array($filters['report_type'], ['overview', 'customers'], true))
    <section class="report-section">
        <div class="report-section-head">
            <h3>Customer Report</h3>
            <span>
                {{ number_format($summary['new_customers']) }} new ·
                {{ number_format($summary['repeat_customers']) }} repeat
            </span>
        </div>

        <div class="brand-table-wrapper">
            <table class="brand-table report-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Brands</th>
                        <th>Type</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Last Order</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customerRows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['phone'] }}</td>
                            <td>{{ $row['email'] ?: '-' }}</td>
                            <td>{{ $row['brands'] ?: '-' }}</td>
                            <td>
                                <span class="status-pill {{ $row['customer_type'] === 'Repeat' ? 'status-confirmed' : 'status-delivered' }}">
                                    {{ $row['customer_type'] }}
                                </span>
                            </td>
                            <td>{{ $row['orders_count'] }}</td>
                            <td>৳{{ number_format($row['total_spent'], 2) }}</td>
                            <td>
                                {{ $row['last_order']?->format('d M Y') }}
                                <small class="order-table-muted">{{ $row['last_status'] }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="brand-empty-state">
                                    <strong>No customers found</strong>
                                    <span>Customer report will appear after orders.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
