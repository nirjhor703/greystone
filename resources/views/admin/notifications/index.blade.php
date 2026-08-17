@extends('admin.layouts.app')

@section('title', 'Notifications | Grey Stone Admin')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Track order updates and stock reminders')

@section('content')
<section class="brand-page-card notification-page-card">
    <div class="brand-page-header">
        <div>
            <h2>
                {{ $activeCategory === 'stock' ? 'Stock Notifications' : 'Main Notifications' }}
            </h2>

            <p>
                {{ $activeCategory === 'stock'
                    ? 'Daily low-stock reminders for products under 10 units.'
                    : 'New orders and Steadfast courier status updates.' }}
            </p>
        </div>

        <div class="notification-header-actions">
            <label class="notification-select-all">
                <input
                    type="checkbox"
                    id="notificationSelectAll"
                >
                Select All
            </label>

            <button
                type="button"
                class="brand-danger-button"
                id="notificationBulkDeleteButton"
                disabled
            >
                Delete Selected
            </button>

            <form
                method="POST"
                action="{{ route('admin.notifications.mark-all-read') }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="category"
                    value="{{ $activeCategory }}"
                >

                <button
                    type="submit"
                    class="brand-secondary-button"
                >
                    Mark All Read
                </button>
            </form>
        </div>
    </div>

    @if (session('notification_status'))
        <div class="notification-status-message">
            {{ session('notification_status') }}
        </div>
    @endif

    <form
        class="admin-ajax-search"
        data-target="#notificationSearchResults"
        action="{{ route('admin.notifications.index') }}"
    >
        <input
            type="hidden"
            name="category"
            value="{{ $activeCategory }}"
        >

        <div class="admin-search-grid">
            <div class="admin-search-field">
                <label>Search</label>
                <input
                    type="search"
                    name="search"
                    placeholder="Search title, message or type"
                    autocomplete="off"
                >
            </div>

            <div class="admin-search-field">
                <label>Read Status</label>
                <select name="read_status">
                    <option value="">All</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                </select>
            </div>

            <button type="reset" class="brand-secondary-button">
                Reset
            </button>
        </div>
    </form>

    <div class="notification-tabs">
        <a
            href="{{ route('admin.notifications.index', ['category' => 'main']) }}"
            class="{{ $activeCategory === 'main' ? 'active' : '' }}"
        >
            Main Notifications

            @if ($counts['main'] > 0)
                <span>{{ $counts['main'] }}</span>
            @endif
        </a>

        <a
            href="{{ route('admin.notifications.index', ['category' => 'stock']) }}"
            class="{{ $activeCategory === 'stock' ? 'active' : '' }}"
        >
            Stock Notifications

            @if ($counts['stock'] > 0)
                <span>{{ $counts['stock'] }}</span>
            @endif
        </a>
    </div>

    <form
        id="notificationBulkDeleteForm"
        method="POST"
        action="{{ route('admin.notifications.bulk-destroy') }}"
    >
        @csrf
        @method('DELETE')

        <input
            type="hidden"
            name="category"
            value="{{ $activeCategory }}"
        >

        <div class="notification-list" id="notificationSearchResults">
            @include('admin.notifications.partials.list')
        </div>
    </form>

    <div
        class="brand-modal"
        id="deleteNotificationsModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-notification-delete-modal
        ></div>

        <div class="brand-modal-dialog brand-delete-dialog">
            <div class="brand-delete-icon">!</div>

            <h3>Delete Notifications?</h3>

            <p>
                Are you sure you want to delete
                <strong id="deleteNotificationCount">0</strong>
                selected notification(s)?
            </p>

            <span>This action cannot be undone.</span>

            <div class="brand-delete-actions">
                <button
                    type="button"
                    class="brand-secondary-button"
                    data-close-notification-delete-modal
                >
                    No, Cancel
                </button>

                <button
                    type="button"
                    class="brand-danger-button"
                    id="confirmDeleteNotificationsButton"
                >
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('notificationBulkDeleteForm');
        const selectAll = document.getElementById('notificationSelectAll');
        const deleteButton = document.getElementById('notificationBulkDeleteButton');
        const deleteModal = document.getElementById('deleteNotificationsModal');
        const deleteCount = document.getElementById('deleteNotificationCount');
        const confirmDeleteButton = document.getElementById('confirmDeleteNotificationsButton');

        function selectedBoxes() {
            return Array.from(
                form?.querySelectorAll('.notification-checkbox:checked') || []
            );
        }

        function allBoxes() {
            return Array.from(
                form?.querySelectorAll('.notification-checkbox') || []
            );
        }

        function syncBulkState() {
            const boxes = allBoxes();
            const selected = selectedBoxes();

            if (deleteButton) {
                deleteButton.disabled = selected.length === 0;
            }

            if (selectAll) {
                selectAll.checked =
                    boxes.length > 0 && selected.length === boxes.length;
                selectAll.indeterminate =
                    selected.length > 0 && selected.length < boxes.length;
            }
        }

        function openDeleteModal() {
            const selected = selectedBoxes();

            if (selected.length === 0) {
                return;
            }

            if (deleteCount) {
                deleteCount.textContent = selected.length;
            }

            deleteModal?.classList.add('open');
            deleteModal?.setAttribute('aria-hidden', 'false');
            document.body.classList.add('brand-modal-open');
        }

        function closeDeleteModal() {
            deleteModal?.classList.remove('open');
            deleteModal?.setAttribute('aria-hidden', 'true');

            if (!document.querySelector('.brand-modal.open')) {
                document.body.classList.remove('brand-modal-open');
            }
        }

        selectAll?.addEventListener('change', function () {
            allBoxes().forEach(function (box) {
                box.checked = selectAll.checked;
            });

            syncBulkState();
        });

        form?.addEventListener('change', function (event) {
            if (event.target.classList.contains('notification-checkbox')) {
                syncBulkState();
            }
        });

        form?.addEventListener('submit', function (event) {
            if (selectedBoxes().length === 0) {
                event.preventDefault();
            }
        });

        deleteButton?.addEventListener('click', openDeleteModal);

        confirmDeleteButton?.addEventListener('click', function () {
            form?.submit();
        });

        document
            .querySelectorAll('[data-close-notification-delete-modal]')
            .forEach(function (button) {
                button.addEventListener('click', closeDeleteModal);
            });

        document.addEventListener('admin-search:updated', syncBulkState);
        syncBulkState();
    });
</script>
@endpush
