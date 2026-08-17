@forelse ($categories as $category)
    <tr id="categoryRow{{ $category->id }}">
        <td><span class="brand-id">#{{ $category->id }}</span></td>

        <td>
            <div class="brand-name-cell">
                <div class="brand-table-logo">
                    @if ($category->image)
                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}">
                    @else
                        <span>{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                    @endif
                </div>

                <div>
                    <strong>{{ $category->name }}</strong>
                    <small>/{{ $category->slug }}</small>
                </div>
            </div>
        </td>

        <td>{{ $category->brand?->name ?? '-' }}</td>
        <td><code class="brand-slug">{{ $category->prefix }}</code></td>

        <td>
            <span class="brand-status-badge {{ $category->status === 'Active' ? 'active' : 'inactive' }}">
                {{ $category->status }}
            </span>
        </td>

        <td>{{ $category->description ?: '-' }}</td>

        <td>
            <div class="brand-table-actions">
                <button type="button" class="brand-action-button edit editCategoryButton" data-id="{{ $category->id }}">
                    Edit
                </button>

                <button
                    type="button"
                    class="brand-action-button delete deleteCategoryButton"
                    data-id="{{ $category->id }}"
                    data-name="{{ $category->name }}"
                >
                    Delete
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr id="emptyCategoryRow">
        <td colspan="7">
            <div class="brand-empty-state">
                <strong>No categories found</strong>
                <span>Try another search or filter.</span>
            </div>
        </td>
    </tr>
@endforelse
