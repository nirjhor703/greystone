<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $activeCategory = $request->query(
            'category',
            AdminNotification::CATEGORY_MAIN
        );

        if (
            !in_array(
                $activeCategory,
                [
                    AdminNotification::CATEGORY_MAIN,
                    AdminNotification::CATEGORY_STOCK,
                ],
                true
            )
        ) {
            $activeCategory = AdminNotification::CATEGORY_MAIN;
        }

        $this->authorizeCategory($request, $activeCategory, 'view');

        $notifications = AdminNotification::query()
            ->where('category', $activeCategory)
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim($request->string('search'));

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('read_status'), function ($query) use ($request): void {
                if ($request->input('read_status') === 'unread') {
                    $query->whereNull('read_at');
                }

                if ($request->input('read_status') === 'read') {
                    $query->whereNotNull('read_at');
                }
            })
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        if ($request->ajax()) {
            return view(
                'admin.notifications.partials.list',
                compact('notifications', 'activeCategory')
            );
        }

        $counts = [
            'main' => AdminNotification::query()
                ->category(AdminNotification::CATEGORY_MAIN)
                ->unread()
                ->count(),
            'stock' => AdminNotification::query()
                ->category(AdminNotification::CATEGORY_STOCK)
                ->unread()
                ->count(),
        ];

        return view('admin.notifications.index', compact(
            'activeCategory',
            'notifications',
            'counts'
        ));
    }

    public function open(
        Request $request,
        AdminNotification $notification
    ): RedirectResponse {
        $this->authorizeCategory($request, $notification->category, 'view');

        if (!$notification->read_at) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return redirect()->to(
            $this->notificationTargetUrl($notification)
        );
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $category = $request->input(
            'category',
            AdminNotification::CATEGORY_MAIN
        );

        $this->authorizeCategory($request, $category, 'update');

        AdminNotification::query()
            ->where('category', $category)
            ->unread()
            ->update([
                'read_at' => now(),
            ]);

        return back();
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => [
                'required',
                Rule::in([
                    AdminNotification::CATEGORY_MAIN,
                    AdminNotification::CATEGORY_STOCK,
                ]),
            ],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:admin_notifications,id'],
        ]);

        $this->authorizeCategory(
            $request,
            $validated['category'],
            'delete'
        );

        $deleted = AdminNotification::query()
            ->where('category', $validated['category'])
            ->whereIn('id', $validated['ids'])
            ->delete();

        return back()->with(
            'notification_status',
            "{$deleted} notification(s) deleted."
        );
    }

    private function authorizeCategory(
        Request $request,
        string $category,
        string $action
    ): void {
        $module = $category === AdminNotification::CATEGORY_STOCK
            ? 'stock_notifications'
            : 'notifications';

        abort_unless(
            $request->user()?->hasAdminPermission("{$module}.{$action}"),
            403
        );
    }

    private function notificationTargetUrl(
        AdminNotification $notification
    ): string {
        $fallback = route('admin.notifications.index', [
            'category' => $notification->category,
        ], false);

        $linkUrl = trim((string) $notification->link_url);

        if ($linkUrl === '') {
            return $fallback;
        }

        if (str_starts_with($linkUrl, '/')) {
            return $linkUrl;
        }

        $parts = parse_url($linkUrl);

        if (!is_array($parts) || empty($parts['path'])) {
            return $fallback;
        }

        $path = $parts['path'];

        if (!str_starts_with($path, '/admin/')) {
            return $fallback;
        }

        $query = isset($parts['query']) && $parts['query'] !== ''
            ? "?{$parts['query']}"
            : '';

        return "{$path}{$query}";
    }
}
