<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Export | Grey Stone</title>

    <style>
        @page {
            size: A4;
            margin: 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #18181b;
            background: #e5e7eb;
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        .paper {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto;
            padding: 14mm;
            background: #ffffff;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
        }

        .report-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #18181b;
        }

        .report-top h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .report-top p,
        .report-meta p {
            margin: 5px 0 0;
            color: #52525b;
        }

        .report-meta {
            text-align: right;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin: 18px 0;
        }

        .summary-grid div {
            padding: 9px;
            background: #f4f4f5;
            border: 1px solid #d4d4d8;
        }

        .summary-grid span {
            display: block;
            color: #71717a;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary-grid strong {
            display: block;
            margin-top: 5px;
            font-size: 15px;
        }

        h2 {
            margin: 20px 0 8px;
            font-size: 14px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            padding: 7px;
            text-align: left;
            vertical-align: top;
            border: 1px solid #d4d4d8;
        }

        th {
            color: #ffffff;
            background: #18181b;
            font-size: 9px;
            text-transform: uppercase;
        }

        td small {
            display: block;
            margin-top: 3px;
            color: #71717a;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            background: #18181b;
        }

        .toolbar button,
        .toolbar a {
            padding: 10px 14px;
            color: #18181b;
            background: #ffffff;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .paper {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">
            Download / Print PDF
        </button>

        <a href="{{ route('admin.reports.index', request()->query()) }}">
            Back to Reports
        </a>
    </div>

    <main class="paper">
        <header class="report-top">
            <div>
                <h1>Grey Stone Report</h1>
                <p>{{ ucfirst($filters['report_type']) }} report for {{ $periodLabel }}</p>
            </div>

            <div class="report-meta">
                <strong>{{ now()->format('d M Y h:i A') }}</strong>
                <p>Period: {{ ucfirst($filters['period']) }}</p>
                <p>Status: {{ $filters['status'] ?: 'All' }}</p>
            </div>
        </header>

        <section class="summary-grid">
            <div>
                <span>Total Orders</span>
                <strong>{{ number_format($summary['orders']) }}</strong>
            </div>
            <div>
                <span>Revenue</span>
                <strong>BDT {{ number_format($summary['revenue'], 2) }}</strong>
            </div>
            <div>
                <span>Customers</span>
                <strong>{{ number_format($summary['customers']) }}</strong>
            </div>
            <div>
                <span>New Customers</span>
                <strong>{{ number_format($summary['new_customers']) }}</strong>
            </div>
            <div>
                <span>Repeat Customers</span>
                <strong>{{ number_format($summary['repeat_customers']) }}</strong>
            </div>
            <div>
                <span>Products Sold</span>
                <strong>{{ number_format($summary['products_sold']) }}</strong>
            </div>
        </section>

        @if (in_array($filters['report_type'], ['overview', 'revenue'], true))
            <h2>Revenue Report</h2>
            <table>
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
                            <td>BDT {{ number_format($row['items_total'], 2) }}</td>
                            <td>BDT {{ number_format($row['delivery_charge'], 2) }}</td>
                            <td>BDT {{ number_format($row['discount'], 2) }}</td>
                            <td><strong>BDT {{ number_format($row['revenue'], 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No revenue found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        @if (in_array($filters['report_type'], ['overview', 'customers'], true))
            <h2>Customer Report</h2>
            <table>
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
                            <td>{{ $row['customer_type'] }}</td>
                            <td>{{ $row['orders_count'] }}</td>
                            <td>BDT {{ number_format($row['total_spent'], 2) }}</td>
                            <td>
                                {{ $row['last_order']?->format('d M Y') }}
                                <small>{{ $row['last_status'] }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        @if (in_array($filters['report_type'], ['overview', 'products'], true))
            <h2>Product Report</h2>
            <table>
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
                            <td>BDT {{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No products sold.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        @if (in_array($filters['report_type'], ['overview', 'orders'], true))
            <h2>Order Report</h2>
            <table>
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
                                <small>{{ $order->phone }}</small>
                            </td>
                            <td>{{ $order->brand?->name ?? '-' }}</td>
                            <td>{{ $order->status }}</td>
                            <td>BDT {{ number_format($order->grand_total, 2) }}</td>
                            <td>{{ $order->created_at?->format('d M Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </main>
</body>
</html>
