<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Member;
use App\Models\Order;
use App\Models\PurchaseMilestone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseMilestoneController extends Controller
{
    public function index(): View
    {
        return view('admin.milestones.index', [
            'milestones' => PurchaseMilestone::with('coupon')->orderBy('step')->get(),
            'coupons' => Coupon::orderBy('title')->orderBy('code')->get(),
            'members' => Member::withCount(['orders' => fn ($query) => $query->where('status', '!=', Order::STATUS_CANCELLED)])
                ->latest()->paginate(20),
        ]);
    }

    public function update(Request $request, PurchaseMilestone $milestone): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:180'],
            'coupon_id' => ['nullable', 'exists:coupons,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $milestone->update($data);

        return back()->with('status', 'Purchase step '.$milestone->step.' updated.');
    }

    public function store(): RedirectResponse
    {
        $step = (int) PurchaseMilestone::max('step') + 1;
        PurchaseMilestone::create([
            'step' => $step,
            'title' => 'Reward '.$step,
            'description' => 'A new surprise is waiting at this step.',
            'is_active' => true,
        ]);

        return back()->with('status', 'Purchase step '.$step.' added.');
    }

    public function destroy(PurchaseMilestone $milestone): RedirectResponse
    {
        if (PurchaseMilestone::count() <= 1) {
            return back()->withErrors(['milestone' => 'At least one purchase step must remain.']);
        }

        $removedStep = $milestone->step;
        DB::transaction(function () use ($milestone, $removedStep): void {
            $milestone->delete();
            PurchaseMilestone::where('step', '>', $removedStep)->orderBy('step')->get()->each(function (PurchaseMilestone $item): void {
                DB::table('member_coupons')->where('source', 'milestone:'.$item->step)->update(['source' => 'milestone:'.($item->step - 1)]);
                $item->decrement('step');
            });
        });

        return back()->with('status', 'Step removed and the journey was reordered. Earned coupons were kept.');
    }
}
