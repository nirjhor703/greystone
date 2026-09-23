<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentEntry;
use App\Models\InvestmentInvestor;
use App\Models\Member;
use App\Models\PeopleProfile;
use App\Services\InvestmentSettlementCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function index(Request $request, InvestmentSettlementCalculator $settlementCalculator): View
    {
        $activeInvestmentTab = (string) $request->input('investment_tab', 'overview');
        $activeInvestmentTab = in_array($activeInvestmentTab, ['overview', 'investors', 'investor_history', 'ledger', 'settlement', 'reports'], true)
            ? $activeInvestmentTab
            : 'overview';
        $profitPreview = max(0, (float) $request->input('monthly_profit', $request->input('profit_preview', 0)));
        $settlementMonth = (string) $request->input('settlement_month', now()->format('Y-m'));
        $settlementMonth = preg_match('/^\d{4}-\d{2}$/', $settlementMonth) ? $settlementMonth : now()->format('Y-m');
        $ledgerSearch = trim((string) $request->input('ledger_search', ''));
        $ledgerType = (string) $request->input('ledger_type', '');
        $ledgerChannel = (string) $request->input('ledger_channel', '');
        $overviewSearch = trim((string) $request->input('overview_search', ''));
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $today = now()->toDateString();

        $investors = InvestmentInvestor::query()
            ->with(['member', 'peopleProfile'])
            ->with('entries')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));

                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('agreement_note', 'like', "%{$search}%"));
            })
            ->orderByDesc('is_active')
            ->latest('id')
            ->get();
        $investors->each(function (InvestmentInvestor $investor) use ($today): void {
            $investor->setAttribute('active_capital', $investor->is_active ? $this->activeCapitalForInvestor($investor, $today) : 0);
            $investor->setAttribute('returnable_capital', $this->activeCapitalForInvestor($investor, $today));
        });

        $settlementInvestors = InvestmentInvestor::query()
            ->whereIn('lifecycle_status', ['active', 'inactive'])
            ->orderBy('name')
            ->get();
        $settlementEntries = InvestmentEntry::query()
            ->whereDate('entry_date', '<=', CarbonImmutable::parse($settlementMonth.'-01')->endOfMonth()->toDateString())
            ->get();
        $settlementRows = $settlementCalculator->calculate($settlementInvestors, $settlementEntries, $settlementMonth, $profitPreview);
        $settlementTotalPayable = collect($settlementRows)->sum('payable');
        $settlementEndDate = CarbonImmutable::parse($settlementMonth.'-01')->endOfMonth()->toDateString();
        $settlementPaidInvestorIds = InvestmentEntry::query()
            ->where('entry_type', 'profit_payout')
            ->whereDate('entry_date', $settlementEndDate)
            ->where('purpose', 'Monthly profit settlement - '.CarbonImmutable::parse($settlementMonth.'-01')->format('M Y'))
            ->whereNotNull('investment_investor_id')
            ->pluck('investment_investor_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $totalActiveCapital = (float) InvestmentEntry::query()
            ->where('entry_type', 'investor_investment')
            ->where('status', 'active')
            ->whereHas('investor', fn ($query) => $query->whereIn('lifecycle_status', ['active', 'inactive']))
            ->where(fn ($query) => $query->whereNull('active_date')->orWhereDate('active_date', '<=', $today))
            ->sum('amount')
            - (float) InvestmentEntry::query()
                ->where('entry_type', 'capital_return')
                ->whereHas('investor', fn ($query) => $query->whereIn('lifecycle_status', ['active', 'inactive']))
                ->sum('amount');
        $totalActiveCapital = max(0, $totalActiveCapital);

        $entryQuery = InvestmentEntry::query()
            ->with('investor')
            ->when($ledgerSearch !== '', function ($query) use ($ledgerSearch): void {
                $query->where(function ($query) use ($ledgerSearch): void {
                    $query->where('purpose', 'like', "%{$ledgerSearch}%")
                        ->orWhere('note', 'like', "%{$ledgerSearch}%")
                        ->orWhereHas('investor', fn ($query) => $query->where('name', 'like', "%{$ledgerSearch}%"));
                });
            })
            ->when($ledgerType !== '' && array_key_exists($ledgerType, InvestmentEntry::TYPES), fn ($query) => $query->where('entry_type', $ledgerType))
            ->when($ledgerChannel !== '' && array_key_exists($ledgerChannel, InvestmentEntry::CHANNELS), fn ($query) => $query->where('investment_channel', $ledgerChannel))
            ->when($dateFrom, fn ($query) => $query->whereDate('entry_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('entry_date', '<=', $dateTo));

        $reportEntries = (clone $entryQuery)->get();
        $reportInvestment = (float) $reportEntries
            ->whereIn('entry_type', ['investor_investment', 'loan_received'])
            ->sum('amount');
        $reportOnlineInvestment = (float) $reportEntries
            ->where('investment_channel', 'online')
            ->whereIn('entry_type', ['investor_investment', 'loan_received'])
            ->sum('amount');
        $reportOfflineInvestment = (float) $reportEntries
            ->where('investment_channel', 'offline')
            ->whereIn('entry_type', ['investor_investment', 'loan_received'])
            ->sum('amount');
        $reportCost = (float) $reportEntries
            ->whereIn('entry_type', ['business_cost', 'loan_payment'])
            ->sum('amount');
        $reportOnlineCost = (float) $reportEntries
            ->where('investment_channel', 'online')
            ->whereIn('entry_type', ['business_cost', 'loan_payment'])
            ->sum('amount');
        $reportOfflineCost = (float) $reportEntries
            ->where('investment_channel', 'offline')
            ->whereIn('entry_type', ['business_cost', 'loan_payment'])
            ->sum('amount');
        $reportPaidOut = (float) $reportEntries
            ->whereIn('entry_type', ['profit_payout', 'capital_return'])
            ->sum('amount');
        $reportProfit = $reportInvestment - $reportCost;

        $entries = $entryQuery
            ->latest('entry_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();
        $investorOptions = InvestmentInvestor::query()
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email']);
        $historyInvestorId = (int) $request->input('history_investor_id', 0);
        $historyInvestor = $historyInvestorId > 0
            ? InvestmentInvestor::query()->find($historyInvestorId)
            : null;
        $historyInvestmentEntries = $historyInvestor
            ? InvestmentEntry::query()
                ->where('investment_investor_id', $historyInvestor->id)
                ->where('entry_type', 'investor_investment')
                ->latest('entry_date')
                ->latest('id')
                ->get()
            : collect();
        $historyMonthlyTotals = $historyInvestmentEntries
            ->groupBy(fn (InvestmentEntry $entry) => $entry->entry_date?->format('Y-m') ?: 'Unknown')
            ->map(fn ($monthEntries, $month) => [
                'month' => $month === 'Unknown' ? 'Unknown' : CarbonImmutable::parse($month.'-01')->format('F Y'),
                'count' => $monthEntries->count(),
                'amount' => (float) $monthEntries->sum('amount'),
            ])
            ->values();
        $overviewInvestorResults = $overviewSearch !== ''
            ? InvestmentInvestor::query()
                ->where(fn ($query) => $query
                    ->where('name', 'like', "%{$overviewSearch}%")
                    ->orWhere('phone', 'like', "%{$overviewSearch}%")
                    ->orWhere('email', 'like', "%{$overviewSearch}%")
                    ->orWhere('agreement_note', 'like', "%{$overviewSearch}%"))
                ->latest('id')
                ->limit(8)
                ->get()
            : collect();
        $overviewEntryResults = $overviewSearch !== ''
            ? InvestmentEntry::query()
                ->with('investor')
                ->where(fn ($query) => $query
                    ->where('purpose', 'like', "%{$overviewSearch}%")
                    ->orWhere('note', 'like', "%{$overviewSearch}%")
                    ->orWhere('amount', 'like', "%{$overviewSearch}%")
                    ->orWhereHas('investor', fn ($query) => $query->where('name', 'like', "%{$overviewSearch}%")))
                ->latest('entry_date')
                ->latest('id')
                ->limit(8)
                ->get()
            : collect();

        return view('admin.investments.index', [
            'investors' => $investors,
            'investorOptions' => $investorOptions,
            'entries' => $entries,
            'members' => Member::orderBy('name')->get(['id', 'name', 'email', 'mobile']),
            'peopleProfiles' => PeopleProfile::orderBy('name')->get(['id', 'name', 'title', 'phone', 'email']),
            'investorTypes' => InvestmentInvestor::TYPES,
            'entryTypes' => InvestmentEntry::TYPES,
            'entryStatuses' => InvestmentEntry::STATUSES,
            'entryChannels' => InvestmentEntry::CHANNELS,
            'ledgerSearch' => $ledgerSearch,
            'overviewSearch' => $overviewSearch,
            'overviewInvestorResults' => $overviewInvestorResults,
            'overviewEntryResults' => $overviewEntryResults,
            'ledgerType' => $ledgerType,
            'ledgerChannel' => $ledgerChannel,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'settlementMonth' => $settlementMonth,
            'settlementRows' => $settlementRows,
            'settlementTotalPayable' => $settlementTotalPayable,
            'settlementPaidInvestorIds' => $settlementPaidInvestorIds,
            'reportInvestment' => $reportInvestment,
            'reportOnlineInvestment' => $reportOnlineInvestment,
            'reportOfflineInvestment' => $reportOfflineInvestment,
            'reportCost' => $reportCost,
            'reportOnlineCost' => $reportOnlineCost,
            'reportOfflineCost' => $reportOfflineCost,
            'reportPaidOut' => $reportPaidOut,
            'reportProfit' => $reportProfit,
            'reportEntryCount' => $reportEntries->count(),
            'totalActiveCapital' => $totalActiveCapital,
            'totalPendingInvestment' => InvestmentEntry::where('entry_type', 'investor_investment')->where('status', 'pending')->sum('amount'),
            'totalDueAmount' => InvestmentInvestor::where('lifecycle_status', 'due_pending')->sum('due_total'),
            'totalReturnedCapital' => InvestmentEntry::where('entry_type', 'capital_return')->sum('amount'),
            'totalBusinessCost' => InvestmentEntry::where('entry_type', 'business_cost')->sum('amount'),
            'totalPaidOut' => InvestmentEntry::where('entry_type', 'profit_payout')->sum('amount'),
            'profitPreview' => $profitPreview,
            'activeInvestmentTab' => $activeInvestmentTab,
            'historyInvestorId' => $historyInvestorId,
            'historyInvestor' => $historyInvestor,
            'historyInvestmentEntries' => $historyInvestmentEntries,
            'historyMonthlyTotals' => $historyMonthlyTotals,
        ]);
    }

    public function storeInvestor(Request $request): RedirectResponse
    {
        $data = $this->validatedInvestor($request);
        $data['lifecycle_status'] = $data['is_active'] ? 'active' : 'inactive';

        $investor = InvestmentInvestor::create($data);
        $this->syncInvestorAttachment($request, $investor);
        $this->createInvestorCapitalEntry($request, $investor);

        return back()->with('status', 'Investor added.');
    }

    public function updateInvestor(Request $request, InvestmentInvestor $investor): RedirectResponse
    {
        $data = $this->validatedInvestor($request);

        if (! in_array($investor->lifecycle_status, ['due_pending', 'settled'], true)) {
            $pausingInvestor = $investor->is_active && ! $data['is_active'];

            if ($pausingInvestor && ! $request->boolean('pause_confirmed')) {
                throw ValidationException::withMessages([
                    'is_active' => 'Please confirm inactive settlement first.',
                ]);
            }

            if ($pausingInvestor) {
                $pauseData = $request->validate([
                    'pause_profit_due' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
                    'pause_note' => ['nullable', 'string', 'max:5000'],
                ]);

                $pauseProfitDue = (float) ($pauseData['pause_profit_due'] ?? 0);

                if ($pauseProfitDue > 0) {
                    InvestmentEntry::create([
                        'investment_investor_id' => $investor->id,
                        'entry_type' => 'profit_payout',
                        'investment_channel' => 'online',
                        'entry_date' => now()->toDateString(),
                        'active_date' => now()->toDateString(),
                        'amount' => $pauseProfitDue,
                        'purpose' => 'Pause settlement payout',
                        'note' => $pauseData['pause_note'] ?: 'Investor paused after settling current account.',
                        'status' => 'settled',
                    ]);
                }
            }

            $data['lifecycle_status'] = $data['is_active'] ? 'active' : 'inactive';
            $data['closed_at'] = null;
        } else {
            $data['is_active'] = false;
        }

        $investor->update($data);
        $this->syncInvestorAttachment($request, $investor);
        $this->createInvestorCapitalEntry($request, $investor);

        return back()->with('status', 'Investor updated.');
    }

    public function destroyInvestor(InvestmentInvestor $investor): RedirectResponse
    {
        if ($investor->entries()->exists()) {
            return back()->withErrors('This investor has ledger entries. Make them inactive instead of deleting.');
        }

        $investor->delete();

        return back()->with('status', 'Investor deleted.');
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $entry = InvestmentEntry::create($this->validatedEntry($request));
        $this->syncAttachment($request, $entry);

        return back()->with('status', 'Investment entry added.');
    }

    public function updateEntry(Request $request, InvestmentEntry $entry): RedirectResponse
    {
        $entry->update($this->validatedEntry($request));
        $this->syncAttachment($request, $entry);

        return back()->with('status', 'Investment entry updated.');
    }

    public function destroyEntry(InvestmentEntry $entry): RedirectResponse
    {
        $entry->delete();

        return back()->with('status', 'Investment entry deleted.');
    }

    public function storeSettlement(Request $request, InvestmentSettlementCalculator $settlementCalculator): RedirectResponse
    {
        $data = $request->validate([
            'settlement_month' => ['required', 'date_format:Y-m'],
            'monthly_profit' => ['required', 'numeric', 'min:0.01', 'max:1000000000'],
            'investment_investor_id' => ['required', 'exists:investment_investors,id'],
        ]);

        $month = (string) $data['settlement_month'];
        $profit = (float) $data['monthly_profit'];
        $investor = InvestmentInvestor::query()
            ->where('is_active', true)
            ->findOrFail($data['investment_investor_id']);
        $monthEndDate = CarbonImmutable::parse($month.'-01')->endOfMonth()->toDateString();

        $alreadyPaid = InvestmentEntry::query()
            ->where('entry_type', 'profit_payout')
            ->where('investment_investor_id', $investor->id)
            ->whereDate('entry_date', $monthEndDate)
            ->where('purpose', 'Monthly profit settlement - '.CarbonImmutable::parse($month.'-01')->format('M Y'))
            ->exists();

        if ($alreadyPaid) {
            return back()->withErrors('This investor already has a payout for this month.');
        }

        $investors = InvestmentInvestor::query()->where('is_active', true)->get();
        $entries = InvestmentEntry::query()
            ->whereDate('entry_date', '<=', $monthEndDate)
            ->get();

        $rows = $settlementCalculator->calculate($investors, $entries, $month, $profit);
        $row = collect($rows)->first(fn ($row) => $row['investor']->id === $investor->id);

        if (! $row || $row['payable'] <= 0) {
            return back()->withErrors('No payable amount found for this investor.');
        }

        InvestmentEntry::create([
            'investment_investor_id' => $investor->id,
            'entry_type' => 'profit_payout',
            'investment_channel' => 'online',
            'entry_date' => $monthEndDate,
            'active_date' => $monthEndDate,
            'amount' => $row['payable'],
            'purpose' => 'Monthly profit settlement - '.CarbonImmutable::parse($month.'-01')->format('M Y'),
            'note' => 'Monthly profit: ৳'.number_format($profit, 2).'. Share: '.number_format($row['share_percent'], 4).'%.',
            'status' => 'active',
        ]);

        return back()->with('status', $investor->name.' payout entry created.');
    }

    private function validatedInvestor(Request $request): array
    {
        $data = $request->validate([
            'member_id' => ['nullable', 'exists:members,id'],
            'people_profile_id' => ['nullable', 'exists:people_profiles,id'],
            'name' => ['nullable', 'string', 'max:160'],
            'type' => ['required', Rule::in(array_keys(InvestmentInvestor::TYPES))],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:160'],
            'term_months' => ['required', 'integer', 'min:1', 'max:240'],
            'agreement_note' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
            'investment_amount' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['type'] === 'member') {
            $member = Member::findOrFail($data['member_id'] ?? null);
            $data['people_profile_id'] = null;
            $data['name'] = $member->name;
            $data['phone'] = $member->mobile;
            $data['email'] = $member->email;
        } elseif ($data['type'] === 'employee') {
            $profile = PeopleProfile::findOrFail($data['people_profile_id'] ?? null);
            $data['member_id'] = null;
            $data['name'] = $profile->name;
            $data['phone'] = $profile->phone;
            $data['email'] = $profile->email;
        } else {
            $request->validate([
                'name' => ['required', 'string', 'max:160'],
                'phone' => ['nullable', 'string', 'max:30'],
            ]);

            $data['member_id'] = null;
            $data['people_profile_id'] = null;
        }

        $data['is_active'] = $request->boolean('is_active');
        unset($data['attachment'], $data['investment_amount']);

        return $data;
    }

    private function validatedEntry(Request $request): array
    {
        $data = $request->validate([
            'investment_investor_id' => ['nullable', 'exists:investment_investors,id'],
            'entry_type' => ['required', Rule::in(array_keys(InvestmentEntry::TYPES))],
            'investment_channel' => ['required', Rule::in(array_keys(InvestmentEntry::CHANNELS))],
            'entry_date' => ['required', 'date'],
            'active_date' => ['nullable', 'date'],
            'maturity_date' => ['nullable', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000000'],
            'purpose' => ['nullable', 'string', 'max:180'],
            'note' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
            'status' => ['required', Rule::in(array_keys(InvestmentEntry::STATUSES))],
        ]);

        $investorRequiredTypes = ['investor_investment', 'profit_payout', 'capital_return'];

        if (in_array($data['entry_type'], $investorRequiredTypes, true) && empty($data['investment_investor_id'])) {
            throw ValidationException::withMessages([
                'investment_investor_id' => 'This entry needs an investor.',
            ]);
        }

        if (! in_array($data['entry_type'], $investorRequiredTypes, true)) {
            $data['investment_investor_id'] = null;
        }

        if ($data['entry_type'] !== 'investor_investment') {
            $data['active_date'] = $data['entry_date'];
        }

        if ($data['entry_type'] === 'investor_investment' && $data['status'] === 'active' && empty($data['active_date'])) {
            $data['active_date'] = $data['entry_date'];
        }

        unset($data['attachment']);

        return $data;
    }

    private function syncAttachment(Request $request, InvestmentEntry $entry): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        $directory = public_path('investment-attachments');
        File::ensureDirectoryExists($directory);

        $file = $request->file('attachment');
        $originalName = $file->getClientOriginalName();
        $name = 'entry-'.$entry->id.'-'.time().'-'.Str::slug(pathinfo($originalName, PATHINFO_FILENAME)).'.'.$file->extension();
        $file->move($directory, $name);

        $entry->update([
            'attachment_path' => 'investment-attachments/'.$name,
            'attachment_name' => $originalName,
        ]);
    }

    private function syncInvestorAttachment(Request $request, InvestmentInvestor $investor): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        $directory = public_path('investor-attachments');
        File::ensureDirectoryExists($directory);

        $file = $request->file('attachment');
        $originalName = $file->getClientOriginalName();
        $name = 'investor-'.$investor->id.'-'.time().'-'.Str::slug(pathinfo($originalName, PATHINFO_FILENAME)).'.'.$file->extension();
        $file->move($directory, $name);

        $investor->update([
            'attachment_path' => 'investor-attachments/'.$name,
            'attachment_name' => $originalName,
        ]);
    }

    private function createInvestorCapitalEntry(Request $request, InvestmentInvestor $investor): void
    {
        $amount = (float) $request->input('investment_amount', 0);

        if ($amount <= 0) {
            return;
        }

        InvestmentEntry::create([
            'investment_investor_id' => $investor->id,
            'entry_type' => 'investor_investment',
            'investment_channel' => 'online',
            'entry_date' => now()->toDateString(),
            'active_date' => now()->toDateString(),
            'maturity_date' => now()->addMonths((int) $investor->term_months)->toDateString(),
            'amount' => $amount,
            'purpose' => 'Investor capital',
            'note' => 'Added from investor form.',
            'status' => 'active',
        ]);
    }

    private function activeCapitalForInvestor(InvestmentInvestor $investor, string $asOfDate): float
    {
        $entries = $investor->relationLoaded('entries')
            ? $investor->entries
            : $investor->entries()->get();

        $invested = (float) $entries
            ->where('entry_type', 'investor_investment')
            ->where('status', 'active')
            ->filter(fn (InvestmentEntry $entry) => ! $entry->active_date || $entry->active_date->toDateString() <= $asOfDate)
            ->sum('amount');
        $returned = (float) $entries
            ->where('entry_type', 'capital_return')
            ->sum('amount');

        return max(0, $invested - $returned);
    }
}
