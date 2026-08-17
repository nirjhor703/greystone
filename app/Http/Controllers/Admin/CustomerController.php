<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $customers = $this->customers($request);
        $stats = $this->stats($customers['all']);
        $paginatedCustomers = $customers['paginated'];

        if ($request->ajax()) {
            return view(
                'admin.customers.partials.table-rows',
                ['customers' => $paginatedCustomers]
            );
        }

        return view('admin.customers.index', [
            'brands' => $brands,
            'customers' => $paginatedCustomers,
            'stats' => $stats,
        ]);
    }

    private function customers(Request $request): array
    {
        $orders = Order::query()
            ->with('brand')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim($request->string('search'));

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('alternative_phone', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('area_thana', 'like', "%{$search}%")
                        ->orWhere('full_address', 'like', "%{$search}%")
                        ->orWhereHas('brand', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('brand_id'), function ($query) use ($request): void {
                $query->where('brand_id', $request->input('brand_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->input('status'));
            })
            ->latest('id')
            ->get();

        $customers = $orders
            ->groupBy(fn (Order $order): string => $this->phoneKey($order->phone))
            ->map(function (Collection $orders): array {
                $latestOrder = $orders->sortByDesc('created_at')->first();
                $firstOrder = $orders->sortBy('created_at')->first();
                $brands = $orders
                    ->pluck('brand.name')
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'name' => $latestOrder->customer_name,
                    'phone' => $latestOrder->phone,
                    'alternative_phone' => $latestOrder->alternative_phone,
                    'email' => $latestOrder->customer_email,
                    'brands' => $brands,
                    'brand_label' => $brands->take(3)->implode(', '),
                    'district' => $latestOrder->district,
                    'area_thana' => $latestOrder->area_thana,
                    'address' => $latestOrder->full_address,
                    'total_orders' => $orders->count(),
                    'total_spent' => (float) $orders->sum('grand_total'),
                    'last_order_at' => $latestOrder->created_at,
                    'first_order_at' => $firstOrder->created_at,
                    'last_order_status' => $latestOrder->status,
                    'last_invoice' => $latestOrder->invoice_number,
                    'last_order_id' => $latestOrder->id,
                    'orders_url' => route('admin.orders.index', [
                        'search' => $latestOrder->phone,
                    ]),
                ];
            })
            ->sortByDesc('last_order_at')
            ->values();

        return [
            'all' => $customers,
            'paginated' => $this->paginate($customers, $request),
        ];
    }

    private function stats(Collection $customers): array
    {
        return [
            'total_customers' => $customers->count(),
            'repeat_customers' => $customers
                ->where('total_orders', '>', 1)
                ->count(),
            'email_leads' => $customers
                ->filter(fn (array $customer): bool => filled($customer['email']))
                ->count(),
            'total_spent' => (float) $customers->sum('total_spent'),
        ];
    }

    private function paginate(
        Collection $customers,
        Request $request
    ): LengthAwarePaginator {
        $perPage = 30;
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $customers->forPage($page, $perPage)->values(),
            $customers->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function phoneKey(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?: 'unknown';
    }
}
