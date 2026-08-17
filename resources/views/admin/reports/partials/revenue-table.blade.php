@if (in_array($filters['report_type'], ['overview', 'revenue'], true))
    <section class="report-section">
        <div class="report-section-head">
            <h3>Revenue Report</h3>
            <span>{{ $periodLabel }}</span>
        </div>

        <div class="brand-table-wrapper">
            <table class="brand-table report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Orders</th>
                        <th>New Customers</th>
                        <th>Repeat Customers</th>
                        <th>Items Total</th>
                        <th>Delivery</th>
                        <th>Discount</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($revenueRows as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['orders'] }}</td>
                            <td>{{ $row['new_customers'] }}</td>
                            <td>{{ $row['repeat_customers'] }}</td>
                            <td>৳{{ number_format($row['items_total'], 2) }}</td>
                            <td>৳{{ number_format($row['delivery_charge'], 2) }}</td>
                            <td>৳{{ number_format($row['discount'], 2) }}</td>
                            <td><strong>৳{{ number_format($row['revenue'], 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="brand-empty-state">
                                    <strong>No revenue found</strong>
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
