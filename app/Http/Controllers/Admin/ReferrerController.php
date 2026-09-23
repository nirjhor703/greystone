<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\ReferralSetting;
use App\Models\Referrer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReferrerController extends Controller
{
    public function index(Request $request): View
    {
        $referrers = $this->referrerQuery($request)
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        if ($request->ajax()) {
            return view(
                'admin.referrers.partials.table-rows',
                compact('referrers')
            );
        }

        return view('admin.referrers.index', [
            'referrers' => $referrers,
            'members' => Member::orderBy('name')->get(['id', 'name', 'email']),
            'settings' => ReferralSetting::current(),
        ]);
    }

    public function show(Referrer $referrer): JsonResponse
    {
        $referrer->load('member');

        return response()->json([
            'status' => 'success',
            'referrer' => $this->tableData($referrer),
        ]);
    }

    public function checkCode(Request $request): JsonResponse
    {
        $code = mb_strtoupper(trim((string) $request->query('code', '')));
        $ignoreId = (int) $request->query('ignore_id', 0);

        if ($code === '') {
            return response()->json([
                'available' => false,
                'message' => '',
                'code' => $code,
            ]);
        }

        $exists = Referrer::query()
            ->whereRaw('UPPER(code) = ?', [$code])
            ->when($ignoreId > 0, function (Builder $query) use ($ignoreId): void {
                $query->where('id', '!=', $ignoreId);
            })
            ->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Already taken' : 'Good to go',
            'code' => $code,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validateReferrer($request);
        $data['code'] = mb_strtoupper($data['code']);
        $data['gift_balance'] = $data['gift_balance'] ?? 0;
        $data['is_active'] = true;

        $referrer = Referrer::create($data);
        $referrer->load('member');

        return $this->successResponse(
            $request,
            'Referrer created successfully.',
            $referrer
        );
    }

    public function update(Request $request, Referrer $referrer): JsonResponse|RedirectResponse
    {
        $data = $this->validateReferrer($request, $referrer);
        $data['code'] = mb_strtoupper($data['code']);
        $data['gift_balance'] = $data['gift_balance'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        $referrer->update($data);
        $referrer->load('member');

        return $this->successResponse(
            $request,
            'Referrer updated successfully.',
            $referrer
        );
    }

    public function destroy(Request $request, Referrer $referrer): JsonResponse|RedirectResponse
    {
        $referrer->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Referrer deleted successfully.',
            ]);
        }

        return back()->with('status', 'Referrer deleted successfully.');
    }

    public function settings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reward_amount' => ['required', 'numeric', 'min:0', 'max:100000'],
        ]);
        ReferralSetting::current()->update($data);
        return back()->with('status', 'Referral rewards updated.');
    }

    private function validateReferrer(Request $request, ?Referrer $referrer = null): array
    {
        return $request->validate([
            'member_id' => [
                'nullable',
                'exists:members,id',
                Rule::unique('referrers', 'member_id')->ignore($referrer),
            ],
            'name' => ['required', 'string', 'max:120'],
            'code' => [
                'required',
                'alpha_dash',
                'max:40',
                Rule::unique('referrers', 'code')->ignore($referrer),
            ],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'gift_balance' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function referrerQuery(Request $request): Builder
    {
        return Referrer::query()
            ->with('member')
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = trim((string) $request->input('search'));

                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('member', function (Builder $memberQuery) use ($search): void {
                            $memberQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), function (Builder $query) use ($request): void {
                $query->where('is_active', $request->input('status') === 'active');
            });
    }

    private function tableData(Referrer $referrer): array
    {
        return [
            'id' => $referrer->id,
            'member_id' => $referrer->member_id,
            'member_name' => $referrer->member?->name,
            'member_email' => $referrer->member?->email,
            'name' => $referrer->name,
            'code' => $referrer->code,
            'commission_rate' => (float) $referrer->commission_rate,
            'successful_referrals' => $referrer->successful_referrals,
            'balance' => (float) $referrer->balance,
            'gift_balance' => (float) $referrer->gift_balance,
            'total_balance' => $referrer->totalBalance(),
            'is_active' => (bool) $referrer->is_active,
            'status_label' => $referrer->is_active ? 'Active' : 'Inactive',
        ];
    }

    private function successResponse(
        Request $request,
        string $message,
        Referrer $referrer
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'referrer' => $this->tableData($referrer),
            ]);
        }

        return back()->with('status', $message);
    }
}
