@forelse ($coupons as $coupon)
    <tr id="couponRow{{ $coupon->id }}">
        <td>
            <span class="brand-id">#{{ $coupon->id }}</span>
        </td>

        <td>
            <div>
                <strong>{{ $coupon->code }}</strong>
                <small>{{ $coupon->title ?: 'Untitled coupon' }}</small>
            </div>
        </td>

        <td>{{ $coupon->brand?->name ?? 'All Brands' }}</td>

        <td>
            @if ($coupon->discount_type === 'percentage')
                {{ rtrim(rtrim(number_format($coupon->discount_value, 2), '0'), '.') }}%
                @if ($coupon->max_discount_amount)
                    <small>Max ৳{{ number_format($coupon->max_discount_amount, 2) }}</small>
                @endif
            @else
                ৳{{ number_format($coupon->discount_value, 2) }}
            @endif
        </td>

        <td>৳{{ number_format($coupon->min_order_amount, 2) }}</td>

        <td>
            {{ $coupon->used_count }}
            /
            {{ $coupon->usage_limit ?: 'Unlimited' }}
        </td>

        <td>
            <span class="brand-status-badge {{ $coupon->status === 'Active' ? 'active' : 'inactive' }}">
                {{ $coupon->status }}
            </span>
        </td>

        <td>
            <small>
                {{ $coupon->starts_at?->format('d M Y h:i A') ?? 'Anytime' }}
                -
                {{ $coupon->expires_at?->format('d M Y h:i A') ?? 'No expiry' }}
            </small>
        </td>

        <td>
            <div class="brand-table-actions">
                <button
                    type="button"
                    class="brand-action-button edit editCouponButton"
                    data-id="{{ $coupon->id }}"
                >
                    Edit
                </button>

                <button
                    type="button"
                    class="brand-action-button delete deleteCouponButton"
                    data-id="{{ $coupon->id }}"
                    data-code="{{ $coupon->code }}"
                >
                    Delete
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr id="emptyCouponRow">
        <td colspan="9">
            <div class="brand-empty-state">
                <strong>No coupons found</strong>
                <span>Add your first checkout coupon.</span>
            </div>
        </td>
    </tr>
@endforelse
