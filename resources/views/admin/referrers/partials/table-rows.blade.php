@forelse ($referrers as $referrer)
    <tr id="referrerRow{{ $referrer->id }}">
        <td><span class="brand-id">#{{ $referrer->id }}</span></td>
        <td>
            <div class="brand-name-cell">
                <div class="brand-table-logo">
                    <span>{{ strtoupper(substr($referrer->name, 0, 1)) }}</span>
                </div>
                <div>
                    <strong>{{ $referrer->name }}</strong>
                    @if ($referrer->member)
                        <small>{{ $referrer->member->email }}</small>
                    @else
                        <small>Not linked</small>
                    @endif
                </div>
            </div>
        </td>
        <td><code class="brand-slug">{{ $referrer->code }}</code></td>
        <td>
            {{ number_format((float) $referrer->commission_rate, 2) }}%
        </td>
        <td>{{ $referrer->successful_referrals }}</td>
        <td><strong>৳{{ number_format((float) $referrer->balance, 2) }}</strong></td>
        <td><strong>৳{{ number_format((float) $referrer->gift_balance, 2) }}</strong></td>
        <td><strong>৳{{ number_format($referrer->totalBalance(), 2) }}</strong></td>
        <td>
            <span class="brand-status-badge {{ $referrer->is_active ? 'active' : 'inactive' }}">
                {{ $referrer->is_active ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td>
            <div class="brand-table-actions">
                <button type="button" class="brand-action-button edit editReferrerButton" data-id="{{ $referrer->id }}">Edit</button>
                <button type="button" class="brand-action-button delete deleteReferrerButton" data-id="{{ $referrer->id }}" data-name="{{ $referrer->name }}">Delete</button>
            </div>
        </td>
    </tr>
@empty
    <tr id="emptyReferrerRow">
        <td colspan="10">
            <div class="brand-empty-state">
                <strong>No referrers found</strong>
                <span>Add your first referrer.</span>
            </div>
        </td>
    </tr>
@endforelse
