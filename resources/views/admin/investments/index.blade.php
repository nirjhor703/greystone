@extends('admin.layouts.app')

@section('title', 'Investments & Investors | Grey Stone Admin')
@section('page-title', 'Investments & Investors')
@section('page-subtitle', 'Track business investment, investor capital, costs, profit share and returns')

@section('content')
@php
    $investmentTabs = [
        'overview' => 'Overview',
        'investors' => 'Investors',
        'investor_history' => 'Investor History',
        'ledger' => 'Ledger',
        'settlement' => 'Monthly Profit Settlement',
        'reports' => 'Reports',
    ];
    $canEditInfo = auth()->user()?->hasAdminPermission('info.editing');
    $investmentHelpTexts = [
        'active_capital' => 'Currently active investor capital total. Inactive/paused investor-er capital share calculation-e off thakbe.',
        'pending_investment' => 'Je investment receive kora hoyeche but active date ekhono ashe nai, seta pending investment.',
        'inactive_paused' => 'Investor temporarily inactive thakle ekhane count hoy. Later abar active kora jabe.',
        'due_pending' => 'Investor-er taka/profit ekhono ferot dewa baki thakle due pending hishabe count hoy.',
        'business_costs' => 'Business purpose-e kora khoroch, jemon fabric, packaging, hosting, transport, marketing.',
        'paid_out' => 'Investor profit payout ba capital return hishabe already paid amount.',
        'investors_total' => 'System-e total investor profile count.',
        'search_everything' => 'Investor name, phone, amount, purpose ba note diye quick search kora jay.',
        'settlement_month' => 'Kon month-er profit share calculate korben seta select korun.',
        'monthly_profit' => 'Salary, commission, cost, tax adjust korar por final profit amount ekhane diben.',
        'total_payable' => 'Selected month-er calculation onujayi investors-der total payable.',
        'paid_investors' => 'Ei month-e jader payout already create hoyeche tader count.',
        'investor_source' => 'Investor external, member, na employee source theke ashche seta.',
        'investor_contact' => 'Investor-er phone/email contact details.',
        'note_attachment' => 'Agreement, receipt, PDF, note ba personal reminder ekhane rakha hoy.',
        'term' => 'Investor koto month-er jonno taka rakhche.',
        'current_share' => 'Current active capital-er moddhe ei investor-er percentage share.',
        'monthly_payable' => 'Monthly profit dile ei investor-er estimated payable amount.',
        'status' => 'Investor active, inactive/paused, due ba settled kina seta.',
        'history_investor' => 'Dropdown theke investor select korle tar shob investment row dekha jabe.',
        'history_total_invested' => 'Selected investor total koto taka invest koreche.',
        'history_rows' => 'Selected investor-er total investment entry count.',
        'history_months' => 'Koto different month-e investment hoyeche.',
        'ledger_total_investment' => 'Selected report range-e total investor investment amount.',
        'ledger_total_cost' => 'Selected report range-e total business cost.',
        'ledger_net_profit' => 'Simple summary: total investment minus total cost.',
        'ledger_paid_returned' => 'Profit payout plus returned capital related paid ledger total.',
        'ledger_search' => 'Date range, type, investor, purpose ba note diye ledger filter kora jay.',
        'entry_type' => 'Ei ledger row investment, cost, payout, na capital return seta select korun.',
        'source' => 'Money online source na offline source theke ashche/ber hoyeche seta.',
        'investor_select' => 'Investor related entry hole investor select korte hobe.',
        'received_date' => 'Money receive/payment/cost hoyeche je date.',
        'active_date' => 'Investment kon date theke profit share calculation-e active hobe.',
        'entry_status' => 'Entry active, pending, returned etc. status.',
        'amount' => 'Ei row-er taka amount.',
        'purpose' => 'Ei taka keno receive/cost/payout holo tar short reason.',
        'file' => 'Agreement, receipt ba proof file upload kore rakhun.',
        'note' => 'Extra details, tax/VAT/agreement reminder ba personal note.',
        'investor_type' => 'External, member ba employee theke investor add korben seta select korun.',
        'investor_name' => 'External investor hole name ekhane likhun.',
        'investment_amount' => 'Investor prothom/currently koto taka invest korche.',
        'term_months' => 'Investor taka koto month-er jonno rakhbe.',
    ];
    $infoHelp = function (string $key) use ($investmentHelpTexts, $canEditInfo) {
        static $helpCounts = [];
        $helpCounts[$key] = ($helpCounts[$key] ?? 0) + 1;
        $id = 'investmentHelp'.str_replace(' ', '', ucwords(str_replace('_', ' ', $key))).$helpCounts[$key];
        $editButton = $canEditInfo ? '<button type="button" class="investment-help-edit-button">Edit</button>' : '';

        return new \Illuminate\Support\HtmlString(
            '<button type="button" class="investment-info-button" onclick="const box=document.getElementById(\''.$id.'\'); if(box){ box.hidden = !box.hidden; }" aria-label="Help">i</button><small id="'.$id.'" class="investment-help-text" data-investment-editable-help="investmentHelpText_'.$key.'" hidden><span data-investment-help-copy>'.e($investmentHelpTexts[$key] ?? '').'</span>'.$editButton.'</small>'
        );
    };
@endphp
<div id="investmentCrudPage"
    data-investor-update-url="{{ route('admin.investors.update', '__ID__') }}"
    data-entry-update-url="{{ route('admin.investment-entries.update', '__ID__') }}">
    <section class="brand-page-card investment-page">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="brand-page-header">
            <div>
                <h2>Investment Dashboard</h2>
                <p>Equity is not sold here. This is rolling investment, active capital and profit-share calculation.</p>
            </div>

            <div class="people-header-actions">
                <button type="button" class="brand-secondary-button" id="openAddInvestmentEntryModal">＋ Add Ledger Entry</button>
                <button type="button" class="brand-primary-button" id="openAddInvestorModal">＋ Add Investor</button>
            </div>
        </div>

        <nav class="investment-page-tabs">
            @foreach($investmentTabs as $tabKey => $tabLabel)
                <a
                    href="{{ route('admin.investments.index', array_merge(request()->except('page'), ['investment_tab' => $tabKey])) }}"
                    class="{{ $activeInvestmentTab === $tabKey ? 'active' : '' }}"
                    aria-current="{{ $activeInvestmentTab === $tabKey ? 'page' : 'false' }}"
                >
                    {{ $tabLabel }}
                </a>
            @endforeach
        </nav>

        @if($activeInvestmentTab === 'overview')
            <div class="customer-stat-grid" id="investmentOverview">
                <article><span>Active Capital {!! $infoHelp('active_capital') !!}</span><strong>৳{{ number_format($totalActiveCapital, 2) }}</strong></article>
                <article><span>Pending Investment {!! $infoHelp('pending_investment') !!}</span><strong>৳{{ number_format((float) $totalPendingInvestment, 2) }}</strong></article>
                <article><span>Inactive / Paused {!! $infoHelp('inactive_paused') !!}</span><strong>{{ number_format($investors->where('lifecycle_status', 'inactive')->count()) }}</strong></article>
                <article class="investment-help-card">
                    <span>Returned Capital <button type="button" class="investment-info-button" data-investment-help-toggle="returnedCapitalHelp" onclick="const box=document.getElementById('returnedCapitalHelp'); if(box){ box.hidden = !box.hidden; }" aria-label="Returned capital help">i</button></span>
                    <strong>৳{{ number_format((float) $totalReturnedCapital, 2) }}</strong>
                    <small id="returnedCapitalHelp" class="investment-help-text" data-investment-editable-help="returnedCapitalHelpText" hidden><span data-investment-help-copy>Investor inactive korle ekhane taka barbe na. Jokhon investor-er main capital actually ferot dewa hobe, tokhon Returned Capital barbe.</span>@if(auth()->user()?->hasAdminPermission('info.editing'))<button type="button" class="investment-help-edit-button">Edit</button>@endif</small>
                </article>
                <article><span>Business Costs {!! $infoHelp('business_costs') !!}</span><strong>৳{{ number_format((float) $totalBusinessCost, 2) }}</strong></article>
                <article><span>Paid Out {!! $infoHelp('paid_out') !!}</span><strong>৳{{ number_format((float) $totalPaidOut, 2) }}</strong></article>
                <article><span>Investors {!! $infoHelp('investors_total') !!}</span><strong>{{ number_format($investors->count()) }}</strong></article>
            </div>

            <form method="GET" class="admin-ajax-search investment-preview-form">
                <input type="hidden" name="investment_tab" value="overview">
                <div class="admin-search-grid">
                    <div class="admin-search-field">
                        <label>Search Everything {!! $infoHelp('search_everything') !!}</label>
                        <input type="search" name="overview_search" value="{{ $overviewSearch }}" placeholder="Investor name, phone, amount, purpose or note">
                    </div>
                    <button class="brand-primary-button" type="submit">Search</button>
                    <a class="brand-secondary-button" href="{{ route('admin.investments.index', ['investment_tab' => 'overview']) }}">Reset</a>
                </div>
            </form>

            @if($overviewSearch !== '')
                <div class="investment-overview-results">
                    <div class="brand-table-wrapper">
                        <table class="brand-table">
                            <thead>
                                <tr>
                                    <th colspan="5">Investor Results</th>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Term</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overviewInvestorResults as $resultInvestor)
                                    <tr>
                                        <td><strong>{{ $resultInvestor->name }}</strong></td>
                                        <td>{{ $resultInvestor->typeLabel() }}</td>
                                        <td>{{ $resultInvestor->phone ?: $resultInvestor->email ?: '-' }}</td>
                                        <td><span class="brand-status-badge {{ $resultInvestor->is_active ? 'active' : 'inactive' }}">{{ $resultInvestor->lifecycleLabel() }}</span></td>
                                        <td>{{ $resultInvestor->term_months }} months</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><div class="brand-empty-state"><strong>No investor found</strong><span>Try another keyword.</span></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="brand-table-wrapper">
                        <table class="brand-table">
                            <thead>
                                <tr>
                                    <th colspan="6">Ledger Results</th>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Investor</th>
                                    <th>Purpose</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overviewEntryResults as $resultEntry)
                                    <tr>
                                        <td><strong>{{ $resultEntry->entry_date?->format('d M Y') ?: '-' }}</strong></td>
                                        <td><span class="people-profile-type">{{ $resultEntry->typeLabel() }}</span></td>
                                        <td>{{ $resultEntry->investor?->name ?: 'Business / Owner / Cost' }}</td>
                                        <td><strong>{{ $resultEntry->purpose ?: '-' }}</strong><small>{{ Str::limit($resultEntry->note ?: '', 45) }}</small></td>
                                        <td><strong>৳{{ number_format((float) $resultEntry->amount, 2) }}</strong></td>
                                        <td>{{ $resultEntry->statusLabel() }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><div class="brand-empty-state"><strong>No ledger entry found</strong><span>Try another keyword.</span></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="investment-note-box">
                <strong>Clean dashboard view</strong>
                <span>Use the tabs above to manage Investors, Investor History, Ledger entries, Monthly Profit Settlement and Reports separately.</span>
            </div>
        @endif

        @if($activeInvestmentTab === 'settlement')
            <div id="investmentSettlement"></div>
            <div class="customer-stat-grid">
                <article><span>Settlement Month {!! $infoHelp('settlement_month') !!}</span><strong>{{ \Carbon\CarbonImmutable::parse($settlementMonth.'-01')->format('M Y') }}</strong></article>
                <article><span>Monthly Profit {!! $infoHelp('monthly_profit') !!}</span><strong>৳{{ number_format((float) $profitPreview, 2) }}</strong></article>
                <article><span>Total Payable {!! $infoHelp('total_payable') !!}</span><strong>৳{{ number_format($settlementTotalPayable, 2) }}</strong></article>
                <article><span>Paid Investors {!! $infoHelp('paid_investors') !!}</span><strong>{{ number_format(count($settlementPaidInvestorIds)) }}</strong></article>
            </div>

            <form method="GET" class="admin-ajax-search investment-preview-form">
                <input type="hidden" name="investment_tab" value="settlement">
                <div class="admin-search-grid">
                    <div class="admin-search-field">
                        <label>Search Investor {!! $infoHelp('search_everything') !!}</label>
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email or note">
                    </div>
                    <div class="admin-search-field">
                        <label>Settlement Month {!! $infoHelp('settlement_month') !!}</label>
                        <input type="month" name="settlement_month" value="{{ $settlementMonth }}">
                    </div>
                    <div class="admin-search-field">
                        <label>Monthly Profit (৳) {!! $infoHelp('monthly_profit') !!}</label>
                        <input type="number" name="monthly_profit" min="0" step="0.01" value="{{ request('monthly_profit', $profitPreview ?: '') }}" placeholder="Example: 20000">
                    </div>
                    <button class="brand-primary-button" type="submit">Calculate Payable</button>
                    <a class="brand-secondary-button" href="{{ route('admin.investments.index', ['investment_tab' => 'settlement']) }}">Reset</a>
                </div>
            </form>

            <div class="investment-note-box">
                <strong>Monthly profit settlement</strong>
                <span>Enter final monthly profit after salaries, commissions, costs and tax adjustments. The system calculates each investor’s payable by date-weighted active capital for that month.</span>
            </div>

            <div class="investment-settlement-panel">
                <div class="investment-settlement-head">
                    <div>
                        <strong>{{ \Carbon\CarbonImmutable::parse($settlementMonth.'-01')->format('F Y') }} Payable</strong>
                        <span>Total calculated payout: ৳{{ number_format($settlementTotalPayable, 2) }}</span>
                    </div>
                    <span class="investment-settlement-hint">Create payout one investor at a time.</span>
                </div>
                <div class="investment-settlement-rows">
                    @forelse($settlementRows as $row)
                        @php
                            $isPaid = in_array((int) $row['investor']->id, $settlementPaidInvestorIds, true);
                        @endphp
                        <article>
                            <strong>{{ $row['investor']->name }}</strong>
                            <span>Avg capital ৳{{ number_format($row['average_capital'], 2) }}</span>
                            <span>Share {{ number_format($row['share_percent'], 4) }}%</span>
                            <em>Payable ৳{{ number_format($row['payable'], 2) }}</em>
                            @if($isPaid)
                                <span class="investment-payout-done">✓ Paid this month</span>
                            @elseif($profitPreview > 0 && $row['payable'] > 0)
                                <button
                                    type="button"
                                    class="brand-secondary-button openSettlementPayoutModal"
                                    data-investor-id="{{ $row['investor']->id }}"
                                    data-investor-name="{{ $row['investor']->name }}"
                                    data-payable="{{ number_format($row['payable'], 2) }}"
                                    data-share="{{ number_format($row['share_percent'], 4) }}"
                                >
                                    Create This Payout
                                </button>
                            @endif
                        </article>
                    @empty
                        <article><strong>No payable yet</strong><span>Add investor capital first.</span><em>৳0.00</em></article>
                    @endforelse
                </div>
            </div>
        @endif

        @if($activeInvestmentTab === 'investors')
        <div class="customer-stat-grid">
            <article><span>Total Investors {!! $infoHelp('investors_total') !!}</span><strong>{{ number_format($investors->count()) }}</strong></article>
            <article><span>Active {!! $infoHelp('active_capital') !!}</span><strong>{{ number_format($investors->where('is_active', true)->count()) }}</strong></article>
            <article><span>Inactive / Paused {!! $infoHelp('inactive_paused') !!}</span><strong>{{ number_format($investors->where('lifecycle_status', 'inactive')->count()) }}</strong></article>
            <article><span>Due Pending {!! $infoHelp('due_pending') !!}</span><strong>{{ number_format($investors->where('lifecycle_status', 'due_pending')->count()) }}</strong></article>
        </div>

        <form method="GET" class="admin-ajax-search investment-preview-form" id="investmentInvestors">
            <input type="hidden" name="investment_tab" value="investors">
            <div class="admin-search-grid">
                <div class="admin-search-field">
                    <label>Search Investor {!! $infoHelp('search_everything') !!}</label>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email or note">
                </div>
                <button class="brand-primary-button" type="submit">Search</button>
                <a class="brand-secondary-button" href="{{ route('admin.investments.index', ['investment_tab' => 'investors']) }}">Reset</a>
            </div>
        </form>

        <div class="brand-table-wrapper">
            <table class="brand-table investment-investor-table">
                <thead>
                    <tr>
                        <th>Investor</th>
                        <th>Source</th>
                        <th>Contact</th>
                        <th>Note / Attachment {!! $infoHelp('note_attachment') !!}</th>
                        <th>Term</th>
                        <th>Active Capital {!! $infoHelp('active_capital') !!}</th>
                        <th>Current Share {!! $infoHelp('current_share') !!}</th>
                        <th>Monthly Payable {!! $infoHelp('monthly_payable') !!}</th>
                        <th>Status</th>
                        <th class="brand-actions-heading">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investors as $investor)
                        @php
                            $activeCapital = $investor->is_active ? (float) ($investor->active_capital ?? 0) : 0;
                            $sharePercent = $totalActiveCapital > 0 ? ($activeCapital / $totalActiveCapital) * 100 : 0;
                            $settlementRow = collect($settlementRows)->first(fn ($row) => $row['investor']->id === $investor->id);
                            $investorPayload = [
                                'id' => $investor->id,
                                'member_id' => $investor->member_id,
                                'people_profile_id' => $investor->people_profile_id,
                                'name' => $investor->name,
                                'type' => $investor->type,
                                'phone' => $investor->phone,
                                'email' => $investor->email,
                                'term_months' => $investor->term_months,
                                'agreement_note' => $investor->agreement_note,
                                'attachment_path' => $investor->attachment_path,
                                'attachment_name' => $investor->attachment_name,
                                'lifecycle_status' => $investor->lifecycle_status,
                                'returnable_capital' => $investor->returnable_capital,
                                'due_total' => $investor->due_total,
                                'is_active' => $investor->is_active,
                            ];
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $investor->name }}</strong>
                                <small>{{ $investor->typeLabel() }}</small>
                                <small>{{ $investor->phone ?: $investor->email ?: 'No contact added' }}</small>
                            </td>
                            <td>
                                @if($investor->member)
                                    <strong>{{ $investor->member->name }}</strong><small>Member</small>
                                @elseif($investor->peopleProfile)
                                    <strong>{{ $investor->peopleProfile->name }}</strong><small>{{ $investor->peopleProfile->title ?: 'Employee profile' }}</small>
                                @else
                                    <span>External</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $investor->phone ?: '-' }}</strong>
                                <small>{{ $investor->email ?: 'No email added' }}</small>
                            </td>
                            <td>
                                @if($investor->attachment_path)
                                    <small><a href="{{ asset($investor->attachment_path) }}" target="_blank" rel="noopener">{{ $investor->attachment_name ?: 'View attachment' }}</a></small>
                                @endif
                                @if($investor->agreement_note)
                                    <small>{{ str($investor->agreement_note)->limit(70) }}</small>
                                @endif
                                @if(! $investor->attachment_path && ! $investor->agreement_note)
                                    <span>-</span>
                                @endif
                            </td>
                            <td><strong>{{ $investor->term_months }} months</strong></td>
                            <td><strong>৳{{ number_format($activeCapital, 2) }}</strong></td>
                            <td><span class="investment-share-pill">{{ number_format($sharePercent, 4) }}%</span></td>
                            <td><strong>৳{{ number_format((float) ($settlementRow['payable'] ?? 0), 2) }}</strong><small>{{ $profitPreview > 0 ? 'for '.$settlementMonth : 'enter monthly profit' }}</small></td>
                            <td><span class="brand-status-badge {{ $investor->is_active ? 'active' : 'inactive' }}">{{ $investor->lifecycleLabel() }}</span></td>
                            <td>
                                <div class="brand-table-actions">
                                    <button type="button" class="brand-action-button edit editInvestorButton" data-investor-id="{{ $investor->id }}">Edit</button>
                                    <form method="POST" action="{{ route('admin.investors.destroy', $investor) }}" onsubmit="return confirm('Delete {{ $investor->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="brand-action-button delete">Delete</button>
                                    </form>
                                </div>
                                <script type="application/json" id="investorData{{ $investor->id }}">{!! json_encode($investorPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><div class="brand-empty-state"><strong>No investors yet</strong><span>Add investor capital first, then manage monthly payouts.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </section>

    @if($activeInvestmentTab === 'investor_history')
    <section class="brand-page-card investment-page" id="investmentInvestorHistory">
        <div class="brand-page-header">
            <div>
                <h2>Investor History</h2>
                <p>Select one investor and see every investment row with month and amount.</p>
            </div>
        </div>

        <form method="GET" class="admin-ajax-search investment-preview-form">
            <input type="hidden" name="investment_tab" value="investor_history">
            <div class="admin-search-grid">
                <div class="admin-search-field">
                    <label>Select Investor {!! $infoHelp('history_investor') !!}</label>
                    <select name="history_investor_id" required>
                        <option value="">Choose investor</option>
                        @foreach($investorOptions as $investorOption)
                            <option value="{{ $investorOption->id }}" @selected($historyInvestorId === $investorOption->id)>
                                {{ $investorOption->name }}{{ $investorOption->phone ? ' — '.$investorOption->phone : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="brand-primary-button" type="submit">Show History</button>
                <a class="brand-secondary-button" href="{{ route('admin.investments.index', ['investment_tab' => 'investor_history']) }}">Reset</a>
            </div>
        </form>

        <div class="customer-stat-grid">
            <article><span>Selected Investor {!! $infoHelp('history_investor') !!}</span><strong>{{ $historyInvestor?->name ?: 'Not selected' }}</strong></article>
            <article><span>Total Invested {!! $infoHelp('history_total_invested') !!}</span><strong>৳{{ number_format((float) $historyInvestmentEntries->sum('amount'), 2) }}</strong></article>
            <article><span>Total Rows {!! $infoHelp('history_rows') !!}</span><strong>{{ number_format($historyInvestmentEntries->count()) }}</strong></article>
            <article><span>Investment Months {!! $infoHelp('history_months') !!}</span><strong>{{ number_format($historyMonthlyTotals->count()) }}</strong></article>
        </div>

        @if($historyInvestor)
            <div class="investment-report-summary">
                @forelse($historyMonthlyTotals as $monthTotal)
                    <article>
                        <span>{{ $monthTotal['month'] }}</span>
                        <strong>৳{{ number_format($monthTotal['amount'], 2) }}</strong>
                        <small>{{ number_format($monthTotal['count']) }} investment row{{ $monthTotal['count'] === 1 ? '' : 's' }}</small>
                    </article>
                @empty
                    <article>
                        <span>No Investment</span>
                        <strong>৳0.00</strong>
                        <small>No investor investment entry found.</small>
                    </article>
                @endforelse
            </div>

            <div class="brand-table-wrapper">
                <table class="brand-table investment-entry-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Month</th>
                            <th>Amount {!! $infoHelp('amount') !!}</th>
                            <th>Source</th>
                            <th>Purpose {!! $infoHelp('purpose') !!}</th>
                            <th>Status</th>
                            <th>Note {!! $infoHelp('note') !!}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historyInvestmentEntries as $historyEntry)
                            <tr>
                                <td><strong>{{ $historyEntry->entry_date?->format('d M Y') ?: '-' }}</strong></td>
                                <td>{{ $historyEntry->entry_date?->format('F Y') ?: '-' }}</td>
                                <td><strong>৳{{ number_format((float) $historyEntry->amount, 2) }}</strong></td>
                                <td><strong>{{ $historyEntry->channelLabel() }}</strong><small>Active: {{ $historyEntry->active_date?->format('d M Y') ?: 'pending' }}</small></td>
                                <td>{{ $historyEntry->purpose ?: '-' }}</td>
                                <td><span class="brand-status-badge {{ $historyEntry->status === 'active' ? 'active' : 'inactive' }}">{{ $historyEntry->statusLabel() }}</span></td>
                                <td>{{ Str::limit($historyEntry->note ?: '-', 70) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="brand-empty-state"><strong>No investment history</strong><span>This investor has no investment rows yet.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="brand-empty-state">
                <strong>Select an investor</strong>
                <span>Dropdown theke investor select korle tar kobe/koto taka invest korse shob ekhane ashbe.</span>
            </div>
        @endif
    </section>
    @endif

    @if(in_array($activeInvestmentTab, ['ledger', 'reports'], true))
    <section class="brand-page-card investment-page" id="investmentLedger">
        <div class="brand-page-header">
            <div>
                <h2>{{ $activeInvestmentTab === 'reports' ? 'Investment Reports' : 'Investment Ledger' }}</h2>
                <p>{{ $activeInvestmentTab === 'reports' ? 'Search by date range and see investment, cost and net profit summary.' : 'Record investor investment, costs, profit payouts and capital returns.' }}</p>
            </div>
            @if($activeInvestmentTab === 'ledger')
                <button type="button" class="brand-primary-button" id="openAddInvestmentEntryModalSecond">＋ Add Entry</button>
            @endif
        </div>

        <div class="investment-report-summary">
            <article>
                <span>Total Investment {!! $infoHelp('ledger_total_investment') !!}</span>
                <strong>৳{{ number_format($reportInvestment, 2) }}</strong>
                <small>Online ৳{{ number_format($reportOnlineInvestment, 2) }} · Offline ৳{{ number_format($reportOfflineInvestment, 2) }}</small>
            </article>
            <article>
                <span>Total Cost {!! $infoHelp('ledger_total_cost') !!}</span>
                <strong>৳{{ number_format($reportCost, 2) }}</strong>
                <small>Online ৳{{ number_format($reportOnlineCost, 2) }} · Offline ৳{{ number_format($reportOfflineCost, 2) }}</small>
            </article>
            <article>
                <span>Net / Profit {!! $infoHelp('ledger_net_profit') !!}</span>
                <strong>৳{{ number_format($reportProfit, 2) }}</strong>
                <small>Investment minus cost</small>
            </article>
            <article>
                <span>Paid / Returned {!! $infoHelp('ledger_paid_returned') !!}</span>
                <strong>৳{{ number_format($reportPaidOut, 2) }}</strong>
                <small>{{ number_format($reportEntryCount) }} ledger entries</small>
            </article>
        </div>

        <form method="GET" class="investment-report-filter no-print" id="investmentReports">
            <input type="hidden" name="investment_tab" value="{{ $activeInvestmentTab }}">
            <div class="investment-report-filter-head">
                <div>
                    <strong>{{ $activeInvestmentTab === 'reports' ? 'Ledger Search & Report' : 'Ledger Search' }} {!! $infoHelp('ledger_search') !!}</strong>
                    <span>Filter by date range, type, investor, purpose or note.</span>
                </div>
                @if($activeInvestmentTab === 'reports')
                    <button type="button" class="brand-secondary-button" onclick="window.print()">Print Report</button>
                @endif
            </div>
            <div class="investment-report-filter-grid">
                <label>Search {!! $infoHelp('ledger_search') !!}<input type="search" name="ledger_search" value="{{ $ledgerSearch }}" placeholder="Investor, purpose or note"></label>
                <label>Type {!! $infoHelp('entry_type') !!}<select name="ledger_type"><option value="">All Types</option>@foreach($entryTypes as $key => $label)<option value="{{ $key }}" @selected($ledgerType === $key)>{{ $label }}</option>@endforeach</select></label>
                <label>Source {!! $infoHelp('source') !!}<select name="ledger_channel"><option value="">Online + Offline</option>@foreach($entryChannels as $key => $label)<option value="{{ $key }}" @selected($ledgerChannel === $key)>{{ $label }}</option>@endforeach</select></label>
                <label>From {!! $infoHelp('received_date') !!}<input type="date" name="date_from" value="{{ $dateFrom }}"></label>
                <label>To {!! $infoHelp('received_date') !!}<input type="date" name="date_to" value="{{ $dateTo }}"></label>
                <button class="brand-primary-button" type="submit">Apply</button>
                <a class="brand-secondary-button" href="{{ route('admin.investments.index', ['investment_tab' => $activeInvestmentTab]) }}">Reset</a>
            </div>
        </form>

        <div class="brand-table-wrapper">
            <table class="brand-table investment-entry-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type {!! $infoHelp('entry_type') !!}</th>
                        <th>Source</th>
                        <th>Investor</th>
                        <th>Purpose {!! $infoHelp('purpose') !!}</th>
                        <th>Amount {!! $infoHelp('amount') !!}</th>
                        <th>Attachment {!! $infoHelp('file') !!}</th>
                        <th class="brand-actions-heading">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        @php
                            $entryPayload = [
                                'id' => $entry->id,
                                'investment_investor_id' => $entry->investment_investor_id,
                                'entry_type' => $entry->entry_type,
                                'investment_channel' => $entry->investment_channel,
                                'entry_date' => $entry->entry_date?->format('Y-m-d'),
                                'active_date' => $entry->active_date?->format('Y-m-d'),
                                'maturity_date' => $entry->maturity_date?->format('Y-m-d'),
                                'amount' => $entry->amount,
                                'purpose' => $entry->purpose,
                                'note' => $entry->note,
                                'attachment_path' => $entry->attachment_path,
                                'attachment_name' => $entry->attachment_name,
                                'status' => $entry->status,
                            ];
                        @endphp
                        <tr>
                            <td><strong>{{ $entry->entry_date?->format('d M Y') }}</strong></td>
                            <td><span class="people-profile-type">{{ $entry->typeLabel() }}</span></td>
                            <td><strong>{{ $entry->channelLabel() }}</strong><small>{{ $entry->entry_type === 'investor_investment' ? 'Active: '.($entry->active_date?->format('d M Y') ?: 'pending') : $entry->statusLabel() }}</small></td>
                            <td>{{ $entry->investor?->name ?: 'Business / Owner / Cost' }}</td>
                            <td><strong>{{ $entry->purpose ?: '-' }}</strong><small>{{ Str::limit($entry->note ?: '', 46) }}</small></td>
                            <td><strong>৳{{ number_format((float) $entry->amount, 2) }}</strong></td>
                            <td>
                                @if($entry->attachment_path)
                                    <a href="{{ asset($entry->attachment_path) }}" target="_blank" rel="noopener">{{ $entry->attachment_name ?: 'View file' }}</a>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td>
                                <div class="brand-table-actions">
                                    <button type="button" class="brand-action-button edit editInvestmentEntryButton" data-entry-id="{{ $entry->id }}">Edit</button>
                                    <form method="POST" action="{{ route('admin.investment-entries.destroy', $entry) }}" onsubmit="return confirm('Delete this entry?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="brand-action-button delete">Delete</button>
                                    </form>
                                </div>
                                <script type="application/json" id="investmentEntryData{{ $entry->id }}">{!! json_encode($entryPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="brand-empty-state"><strong>No ledger entries yet</strong><span>Add investor investment, cost or payout entries.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $entries->links() }}
    </section>
    @endif

    <div class="brand-modal" id="settlementPayoutModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="settlementPayoutModal"></div>
        <div class="brand-modal-dialog">
            <div class="brand-modal-header">
                <div>
                    <h3>Create Investor Payout</h3>
                    <p>Confirm this investor’s monthly profit payout before adding it to ledger.</p>
                </div>
                <button type="button" class="brand-modal-close" data-close-modal="settlementPayoutModal">×</button>
            </div>
            <form method="POST" action="{{ route('admin.investment-settlements.store') }}">
                @csrf
                <input type="hidden" name="settlement_month" value="{{ $settlementMonth }}">
                <input type="hidden" name="monthly_profit" value="{{ $profitPreview }}">
                <input type="hidden" id="settlement_investment_investor_id" name="investment_investor_id">
                <div class="brand-modal-body">
                    <div class="investment-payout-confirm">
                        <span>Investor</span>
                        <strong id="settlementInvestorName">-</strong>
                        <span>Month</span>
                        <strong>{{ \Carbon\CarbonImmutable::parse($settlementMonth.'-01')->format('F Y') }}</strong>
                        <span>Payable</span>
                        <strong id="settlementInvestorPayable">৳0.00</strong>
                        <span>Share</span>
                        <strong id="settlementInvestorShare">0%</strong>
                    </div>
                </div>
                <div class="brand-modal-footer">
                    <button type="button" class="brand-secondary-button" data-close-modal="settlementPayoutModal">Cancel</button>
                    <button type="submit" class="brand-primary-button">Create Payout Entry</button>
                </div>
            </form>
        </div>
    </div>

    @foreach(['add' => 'Add Investor', 'edit' => 'Edit Investor'] as $mode => $title)
        <div class="brand-modal" id="{{ $mode }}InvestorModal" aria-hidden="true">
            <div class="brand-modal-backdrop" data-close-modal="{{ $mode }}InvestorModal"></div>
            <div class="brand-modal-dialog brand-modal-large">
                <div class="brand-modal-header">
                    <div>
                        <h3>{{ $title }}</h3>
                        <p>Select External, Member or Employee. Only the needed fields will appear.</p>
                    </div>
                    <button type="button" class="brand-modal-close" data-close-modal="{{ $mode }}InvestorModal">×</button>
                </div>

                <form method="POST" enctype="multipart/form-data" id="{{ $mode }}InvestorForm" action="{{ $mode === 'add' ? route('admin.investors.store') : route('admin.investors.update', 1) }}">
                    @csrf
                    @if($mode === 'edit')
                        @method('PUT')
                    @endif
                    <div class="brand-modal-body">
                        <div class="people-hr-form">
                            <section>
                                <h4>Investor Details</h4>
                                <div class="people-profile-fields">
                                    <label>Investor Type {!! $infoHelp('investor_type') !!}<select id="{{ $mode }}_investor_type" name="type" required data-investor-type-select>@foreach($investorTypes as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                                    <label data-investor-field="external">Name<input id="{{ $mode }}_investor_name" name="name" placeholder="Investor name"></label>
                                    <label data-investor-field="external">Phone<input id="{{ $mode }}_investor_phone" name="phone" placeholder="01XXXXXXXXX"></label>
                                    <label data-investor-field="external">Email<input id="{{ $mode }}_investor_email" type="email" name="email" placeholder="person@example.com"></label>
                                    <label data-investor-field="member">Member {!! $infoHelp('investor_select') !!}<select id="{{ $mode }}_investor_member_id" name="member_id"><option value="">Select member</option>@foreach($members as $member)<option value="{{ $member->id }}">{{ $member->name }} — {{ $member->email }}</option>@endforeach</select></label>
                                    <label data-investor-field="employee">Employee {!! $infoHelp('investor_select') !!}<select id="{{ $mode }}_investor_people_profile_id" name="people_profile_id"><option value="">Select employee</option>@foreach($peopleProfiles as $profile)<option value="{{ $profile->id }}">{{ $profile->name }} — {{ $profile->title ?: 'Profile' }}</option>@endforeach</select></label>
                                    <label>Investment Amount (৳) {!! $infoHelp('investment_amount') !!}<input id="{{ $mode }}_investor_investment_amount" type="number" name="investment_amount" min="0" step="0.01" placeholder="How much invested"></label>
                                    <label>For How Many Months? {!! $infoHelp('term_months') !!}<input id="{{ $mode }}_investor_term_months" type="number" name="term_months" min="1" max="240" value="12" required></label>
                                    <label class="people-profile-wide">Note {!! $infoHelp('note') !!}<textarea id="{{ $mode }}_investor_agreement_note" name="agreement_note" rows="3" placeholder="Agreement, return rule, personal note or reminder"></textarea></label>
                                    <label class="people-profile-wide">Attachment {!! $infoHelp('file') !!}<input id="{{ $mode }}_investor_attachment" type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf"><small>Agreement, receipt or document image/PDF.</small></label>
                                    @if($mode === 'add')
                                        <label class="people-profile-toggle people-profile-wide"><input id="{{ $mode }}_investor_is_active" type="checkbox" name="is_active" value="1" checked> Active investor</label>
                                    @else
                                        <input type="hidden" id="{{ $mode }}_investor_pause_confirmed" name="pause_confirmed" value="0">
                                        <input type="hidden" id="{{ $mode }}_investor_pause_profit_due" name="pause_profit_due" value="0">
                                        <input type="hidden" id="{{ $mode }}_investor_pause_note" name="pause_note">
                                        <label>Status<select id="{{ $mode }}_investor_is_active" name="is_active"><option value="1">Active</option><option value="0">Inactive / Paused</option></select></label>
                                        <p class="people-investor-help people-profile-wide">Inactive korle ei investor-er current settlement share off thakbe. Later Active korle abar settlement calculation-e ashbe.</p>
                                    @endif
                                </div>
                            </section>
                        </div>
                    </div>
                    <div class="brand-modal-footer">
                        <button type="button" class="brand-secondary-button" data-close-modal="{{ $mode }}InvestorModal">Cancel</button>
                        <button type="submit" class="brand-primary-button">{{ $mode === 'add' ? 'Add Investor' : 'Save Investor' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <div class="brand-modal" id="investorPauseModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="investorPauseModal"></div>
        <div class="brand-modal-dialog">
            <div class="brand-modal-header">
                <div>
                    <h3>Confirm Inactive / Pause</h3>
                    <p>Settle this investor’s current payout if needed, then pause their share.</p>
                </div>
                <button type="button" class="brand-modal-close" data-close-modal="investorPauseModal">×</button>
            </div>
            <div class="brand-modal-body">
                <div class="investment-payout-confirm">
                    <span>Investor</span>
                    <strong id="pauseInvestorName">-</strong>
                    <span>Current Capital {!! $infoHelp('active_capital') !!}</span>
                    <strong id="pauseInvestorCapital">৳0.00</strong>
                    <span>Settlement Payout Now {!! $infoHelp('monthly_payable') !!}</span>
                    <label><input id="pauseInvestorProfitDue" type="number" min="0" step="0.01" value="0"></label>
                    <span>Note {!! $infoHelp('note') !!}</span>
                    <label><textarea id="pauseInvestorNote" rows="3" placeholder="Why inactive, when they may join again, payout note"></textarea></label>
                </div>
                <div class="investment-note-box">
                    <strong>Important</strong>
                    <span>Inactive korle tar capital reserve thakbe. Tar share onno investor-der moddhe vag hobe na, and ei investor inactive thaka obosthay payout pabe na.</span>
                </div>
            </div>
            <div class="brand-modal-footer">
                <button type="button" class="brand-secondary-button" data-close-modal="investorPauseModal">Cancel</button>
                <button type="button" class="brand-primary-button" id="confirmInvestorPauseButton">Confirm Inactive</button>
            </div>
        </div>
    </div>

    @foreach(['add' => 'Add Ledger Entry', 'edit' => 'Edit Ledger Entry'] as $mode => $title)
        <div class="brand-modal" id="{{ $mode }}InvestmentEntryModal" aria-hidden="true">
            <div class="brand-modal-backdrop" data-close-modal="{{ $mode }}InvestmentEntryModal"></div>
            <div class="brand-modal-dialog brand-modal-large">
                <div class="brand-modal-header">
                    <div>
                        <h3>{{ $title }}</h3>
                        <p>Add investor capital, domain/hosting/transport cost, payout or capital return.</p>
                    </div>
                    <button type="button" class="brand-modal-close" data-close-modal="{{ $mode }}InvestmentEntryModal">×</button>
                </div>

                <form method="POST" enctype="multipart/form-data" id="{{ $mode }}InvestmentEntryForm" action="{{ $mode === 'add' ? route('admin.investment-entries.store') : route('admin.investment-entries.update', 1) }}">
                    @csrf
                    @if($mode === 'edit')
                        @method('PUT')
                    @endif
                    <div class="brand-modal-body">
                        <div class="people-hr-form">
                            <section>
                                <h4>Ledger Entry</h4>
                                <div class="people-profile-fields">
                                    <label>Entry Type {!! $infoHelp('entry_type') !!}<select id="{{ $mode }}_entry_type" name="entry_type" required data-entry-type-select>@foreach($entryTypes as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                                    <label>Source<select id="{{ $mode }}_entry_investment_channel" name="investment_channel" required>@foreach($entryChannels as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                                    <label data-entry-investor-field>Investor<select id="{{ $mode }}_entry_investment_investor_id" name="investment_investor_id"><option value="">Select investor</option>@foreach($investors as $investor)<option value="{{ $investor->id }}">{{ $investor->name }}</option>@endforeach</select></label>
                                    <label>Received Date<input id="{{ $mode }}_entry_entry_date" type="date" name="entry_date" value="{{ now()->format('Y-m-d') }}" required></label>
                                    <label data-entry-active-date-field>Active Date {!! $infoHelp('active_date') !!}<input id="{{ $mode }}_entry_active_date" type="date" name="active_date" value="{{ now()->format('Y-m-d') }}"><small>Pending thakle future active date dite paro.</small></label>
                                    <label>Status<select id="{{ $mode }}_entry_status" name="status" required>@foreach($entryStatuses as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                                    <label>Amount (৳) {!! $infoHelp('amount') !!}<input id="{{ $mode }}_entry_amount" type="number" name="amount" min="0.01" step="0.01" required></label>
                                    <label class="people-profile-wide">Purpose {!! $infoHelp('purpose') !!}<input id="{{ $mode }}_entry_purpose" name="purpose" placeholder="Example: Domain hosting, fabric, investor capital"></label>
                                    <label class="people-profile-wide">Agreement / Receipt File {!! $infoHelp('file') !!}<input id="{{ $mode }}_entry_attachment" type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf"></label>
                                    <label class="people-profile-wide">Note {!! $infoHelp('note') !!}<textarea id="{{ $mode }}_entry_note" name="note" rows="4" placeholder="Any receipt, tax, VAT, business purpose or agreement note"></textarea></label>
                                </div>
                            </section>
                        </div>
                    </div>
                    <div class="brand-modal-footer">
                        <button type="button" class="brand-secondary-button" data-close-modal="{{ $mode }}InvestmentEntryModal">Cancel</button>
                        <button type="submit" class="brand-primary-button">{{ $mode === 'add' ? 'Add Entry' : 'Save Entry' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
