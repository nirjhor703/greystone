@forelse ($products as $product)
    @php
        $displayImage = $product->primaryImage
            ?? $product->images->first();
    @endphp

    <tr id="productRow{{ $product->id }}">
        <td><span class="brand-id">#{{ $product->id }}</span></td>

        <td>
            <div class="brand-name-cell">
                <div class="product-table-image">
                    @if ($displayImage)
                        <img src="{{ Storage::url($displayImage->image) }}" alt="{{ $product->name }}">
                    @else
                        <span>{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                    @endif
                </div>

                <div>
                    <strong>{{ $product->name }}</strong>
                    <small>/{{ $product->slug }}</small>
                </div>
            </div>
        </td>

        <td>{{ $product->brand?->name ?? '-' }}</td>
        <td>{{ $product->category?->name ?? '-' }}</td>
        <td><code class="brand-slug">{{ $product->product_code }}</code></td>

        <td>
            <div class="product-price-cell">
                @if ($product->sale_price)
                    <strong>৳{{ number_format($product->sale_price, 2) }}</strong>
                    <del>৳{{ number_format($product->regular_price, 2) }}</del>
                @else
                    <strong>৳{{ number_format($product->regular_price, 2) }}</strong>
                @endif
            </div>
        </td>

        <td>
            <span class="brand-status-badge {{ $product->stock_quantity > 0 ? 'active' : 'inactive' }}">
                {{ $product->stock_quantity }}
            </span>
        </td>

        <td>
            <span class="brand-status-badge {{ $product->is_featured ? 'active' : 'inactive' }}">
                {{ $product->is_featured ? 'Yes' : 'No' }}
            </span>
        </td>

        <td>
            <span class="brand-status-badge {{ $product->status === 'Active' ? 'active' : 'inactive' }}">
                {{ $product->status }}
            </span>
        </td>

        <td>
            <div class="brand-table-actions">
                <button type="button" class="brand-action-button edit editProductButton" data-id="{{ $product->id }}">
                    Edit
                </button>

                <button
                    type="button"
                    class="brand-action-button delete deleteProductButton"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                >
                    Delete
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr id="emptyProductRow">
        <td colspan="10">
            <div class="brand-empty-state">
                <strong>No products found</strong>
                <span>Try another search or filter.</span>
            </div>
        </td>
    </tr>
@endforelse
