@if (in_array($filters['report_type'], ['overview', 'products'], true))
    <section class="report-section">
        <div class="report-section-head">
            <h3>Product Report</h3>
            <span>Top selling products</span>
        </div>

        <div class="brand-table-wrapper">
            <table class="brand-table report-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Code</th>
                        <th>Quantity Sold</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productRows as $row)
                        <tr>
                            <td>{{ $row['product_name'] }}</td>
                            <td>{{ $row['product_code'] ?: '-' }}</td>
                            <td>{{ $row['quantity'] }}</td>
                            <td>৳{{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="brand-empty-state">
                                    <strong>No products sold</strong>
                                    <span>Product report will appear after orders.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
