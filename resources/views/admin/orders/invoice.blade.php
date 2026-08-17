<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->invoice_number }}</title>

    <style>
        body {
            margin: 0;
            color: #18181b;
            background: #f4f4f5;
            font-family: Arial, sans-serif;
        }

        .invoice-page {
            width: min(860px, calc(100% - 32px));
            margin: 28px auto;
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: 16px;
            overflow: hidden;
        }

        .invoice-head,
        .invoice-section,
        .invoice-footer {
            padding: 28px;
        }

        .invoice-head {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            background: #111113;
            color: #ffffff;
        }

        .invoice-head h1 {
            margin: 0;
            font-size: 28px;
        }

        .invoice-head p,
        .invoice-footer p {
            margin: 7px 0 0;
            color: #a1a1aa;
            font-size: 13px;
        }

        .invoice-code {
            text-align: right;
        }

        .invoice-code strong {
            display: block;
            margin-top: 8px;
            font-size: 18px;
        }

        .invoice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .invoice-box {
            padding: 16px;
            background: #fafafa;
            border: 1px solid #e4e4e7;
            border-radius: 12px;
        }

        .invoice-box h3 {
            margin: 0 0 12px;
            font-size: 15px;
        }

        .invoice-box p {
            margin: 6px 0;
            color: #3f3f46;
            font-size: 13px;
            line-height: 1.5;
        }

        table {
            width: 100%;
            margin-top: 22px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px;
            border-bottom: 1px solid #e4e4e7;
            font-size: 13px;
            text-align: left;
        }

        th {
            color: #71717a;
            background: #fafafa;
            font-size: 11px;
            text-transform: uppercase;
        }

        td:last-child,
        th:last-child {
            text-align: right;
        }

        .invoice-total {
            width: min(330px, 100%);
            margin: 22px 0 0 auto;
        }

        .invoice-total div {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            color: #52525b;
            font-size: 13px;
        }

        .invoice-total .grand {
            margin-top: 6px;
            padding-top: 14px;
            color: #111113;
            border-top: 1px solid #d4d4d8;
            font-size: 17px;
            font-weight: 800;
        }

        .invoice-actions {
            display: flex;
            justify-content: flex-end;
            padding: 18px 28px;
            background: #fafafa;
            border-top: 1px solid #e4e4e7;
        }

        .invoice-actions button {
            min-height: 42px;
            padding: 10px 17px;
            color: #ffffff;
            background: #18181b;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .invoice-page {
                width: 100%;
                margin: 0;
                border: 0;
                border-radius: 0;
            }

            .invoice-actions {
                display: none;
            }
        }
    </style>
</head>

<body>
    <article class="invoice-page">
        <header class="invoice-head">
            <div>
                <h1>{{ $order->brand?->name ?? 'Store' }}</h1>
                <p>{{ $order->brand?->address }}</p>
                <p>{{ $order->brand?->contact_number }}</p>
            </div>

            <div class="invoice-code">
                <span>Invoice</span>
                <strong>{{ $order->invoice_number }}</strong>
                <p>{{ $order->created_at?->format('Y-m-d H:i') }}</p>
            </div>
        </header>

        <section class="invoice-section">
            <div class="invoice-grid">
                <div class="invoice-box">
                    <h3>Customer</h3>
                    <p><strong>{{ $order->customer_name }}</strong></p>
                    <p>{{ $order->phone }}</p>
                    @if ($order->alternative_phone)
                        <p>{{ $order->alternative_phone }}</p>
                    @endif
                    @if ($order->customer_email)
                        <p>{{ $order->customer_email }}</p>
                    @endif
                </div>

                <div class="invoice-box">
                    <h3>Delivery</h3>
                    <p>
                        {{ $order->delivery_area === 'inside_dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }}
                    </p>
                    <p>{{ $order->full_address }}</p>
                    @if ($order->order_note)
                        <p>{{ $order->order_note }}</p>
                    @endif
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                <br>
                                <small>{{ $item->product_code }}</small>
                            </td>
                            <td>
                                {{ $item->color ?: '-' }}
                                /
                                {{ $item->size ?: '-' }}
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>৳{{ number_format($item->unit_price, 2) }}</td>
                            <td>৳{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="invoice-total">
                <div>
                    <span>Items Total</span>
                    <strong>৳{{ number_format($order->items_total, 2) }}</strong>
                </div>
                <div>
                    <span>Delivery Charge</span>
                    <strong>৳{{ number_format($order->delivery_charge, 2) }}</strong>
                </div>
                @if ((float) $order->coupon_discount_amount > 0)
                    <div>
                        <span>
                            Coupon Discount
                            @if ($order->coupon_code)
                                ({{ $order->coupon_code }})
                            @endif
                        </span>
                        <strong>-৳{{ number_format($order->coupon_discount_amount, 2) }}</strong>
                    </div>
                @endif
                <div class="grand">
                    <span>Grand Total</span>
                    <strong>৳{{ number_format($order->grand_total, 2) }}</strong>
                </div>
            </div>
        </section>

        <footer class="invoice-footer">
            <p>Payment Method: Cash on Delivery</p>
            <p>Order Number: {{ $order->order_number }}</p>
        </footer>

        <div class="invoice-actions">
            <button type="button" onclick="window.print()">
                Download / Print Invoice
            </button>
        </div>
    </article>
</body>
</html>
