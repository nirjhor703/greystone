@forelse ($notifications as $notification)
    <article class="notification-item {{ $notification->read_at ? 'read' : 'unread' }}">
        <label class="notification-select-item">
            <input
                type="checkbox"
                class="notification-checkbox"
                name="ids[]"
                value="{{ $notification->id }}"
            >
        </label>

        <div class="notification-dot"></div>

        <div>
            <div class="notification-item-head">
                <h3>{{ $notification->title }}</h3>
                <span>{{ $notification->created_at?->diffForHumans() }}</span>
            </div>

            <p>{{ $notification->message }}</p>

            <div class="notification-item-meta">
                <span>{{ str_replace('_', ' ', $notification->type) }}</span>

                @if ($notification->type === \App\Models\AdminNotification::TYPE_PERMISSION_CHANGED)
                    @foreach (($notification->meta['added_permissions'] ?? []) as $permission)
                        <span>Given {{ $permission }}</span>
                    @endforeach

                    @foreach (($notification->meta['removed_permissions'] ?? []) as $permission)
                        <span>Removed {{ $permission }}</span>
                    @endforeach

                    @if (($notification->meta['old_role'] ?? null) !== ($notification->meta['new_role'] ?? null))
                        <span>
                            Role {{ $notification->meta['old_role'] ?? '-' }}
                            to {{ $notification->meta['new_role'] ?? '-' }}
                        </span>
                    @endif

                    @if (($notification->meta['old_root'] ?? null) !== ($notification->meta['new_root'] ?? null))
                        <span>
                            Root {{ ($notification->meta['new_root'] ?? false) ? 'enabled' : 'disabled' }}
                        </span>
                    @endif
                @endif

                @if ($notification->read_at)
                    <span>Read {{ $notification->read_at->diffForHumans() }}</span>
                @else
                    <span>Unread</span>
                @endif
            </div>
        </div>

        <a
            href="{{ route('admin.notifications.open', $notification) }}"
            class="brand-action-button view"
        >
            Open
        </a>
    </article>
@empty
    <div class="brand-empty-state">
        <strong>No notifications found</strong>
        <span>
            {{ $activeCategory === 'stock'
                ? 'Low-stock reminders will appear here.'
                : 'New order and courier updates will appear here.' }}
        </span>
    </div>
@endforelse

@if ($notifications->hasPages())
    <div class="notification-pagination">
        {{ $notifications->links() }}
    </div>
@endif
