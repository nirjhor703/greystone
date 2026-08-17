@forelse ($brands as $brand)
    <tr id="brandRow{{ $brand->id }}">
        <td><span class="brand-id">#{{ $brand->id }}</span></td>

        <td>
            <div class="brand-name-cell">
                <div class="brand-table-logo">
                    @if ($brand->logo)
                        <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}">
                    @else
                        <span>{{ strtoupper(substr($brand->name, 0, 1)) }}</span>
                    @endif
                </div>

                <div>
                    <strong class="brand-row-name">{{ $brand->name }}</strong>
                    <small class="brand-row-email">{{ $brand->email ?? 'No email added' }}</small>
                </div>
            </div>
        </td>

        <td><code class="brand-slug">{{ $brand->slug }}</code></td>

        <td>
            <div class="brand-color-cell">
                <span class="brand-color-dot" style="background: {{ $brand->primary_color }}"></span>
                <span class="brand-row-color">{{ $brand->primary_color }}</span>
            </div>
        </td>

        <td>
            <span class="brand-row-contact">{{ $brand->contact_number ?? 'Not added' }}</span>
        </td>

        <td>
            <span class="brand-status-badge {{ $brand->is_active ? 'active' : 'inactive' }}">
                {{ $brand->is_active ? 'Active' : 'Inactive' }}
            </span>
        </td>

        <td>
            <div class="brand-table-actions">
                <a
                    href="{{ route('brand.show', $brand->slug) }}"
                    target="_blank"
                    class="brand-action-button view"
                    title="View Store"
                >
                    View
                </a>

                <button type="button" class="brand-action-button edit editBrandButton" data-id="{{ $brand->id }}">
                    Edit
                </button>

                <button
                    type="button"
                    class="brand-action-button delete deleteBrandButton"
                    data-id="{{ $brand->id }}"
                    data-name="{{ $brand->name }}"
                >
                    Delete
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr id="emptyBrandRow">
        <td colspan="7">
            <div class="brand-empty-state">
                <strong>No brands found</strong>
                <span>Try another search or filter.</span>
            </div>
        </td>
    </tr>
@endforelse
