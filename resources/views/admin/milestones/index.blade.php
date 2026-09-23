@extends('admin.layouts.app')
@section('title', 'Member Milestones | Grey Stone Admin')
@section('page-title', 'Member Milestones')
@section('page-subtitle', 'Build and control the complete member reward journey')
@section('content')
<section class="brand-page-card customer-page-card">
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="brand-page-header"><div><h2>Purchase reward journey</h2><p>Add or remove steps, then choose what members see and which coupon enters their wallet.</p></div><form method="POST" action="{{ route('admin.milestones.store') }}">@csrf<button class="brand-primary-button milestone-add-button" type="submit">＋ Add new step</button></form></div>
    <div class="customer-stat-grid"><article><span>Journey length @include('admin.partials.info-help', ['key' => 'milestone_journey_length', 'text' => 'Member reward journey-te total koyta step ache.'])</span><strong>{{ $milestones->count() }} steps</strong></article><article><span>Configured coupons @include('admin.partials.info-help', ['key' => 'milestone_configured_coupons', 'text' => 'Koyta step-e coupon reward set kora ache.'])</span><strong>{{ $milestones->whereNotNull('coupon_id')->count() }}</strong></article><article><span>Active steps @include('admin.partials.info-help', ['key' => 'milestone_active_steps', 'text' => 'Members-der dekhano active reward steps.'])</span><strong>{{ $milestones->where('is_active', true)->count() }}</strong></article><article><span>Members</span><strong>{{ number_format($members->total()) }}</strong></article></div>
    <div class="milestone-admin-grid">
        @foreach($milestones as $milestone)
        <div class="milestone-admin-wrap">
        <form method="POST" action="{{ route('admin.milestones.update', $milestone) }}" class="milestone-admin-card">@csrf @method('PUT')
            <b>{{ str_pad($milestone->step, 2, '0', STR_PAD_LEFT) }}</b>
            <label>Step title<input name="title" value="{{ $milestone->title }}" required></label>
            <label>Customer message<input name="description" value="{{ $milestone->description }}"></label>
            <label>Coupon reward @include('admin.partials.info-help', ['key' => 'milestone_coupon', 'text' => 'Ei step complete korle member wallet-e kon coupon jabe.'])<select name="coupon_id"><option value="">No coupon</option>@foreach($coupons as $coupon)<option value="{{ $coupon->id }}" @selected($milestone->coupon_id === $coupon->id)>{{ $coupon->title ?: $coupon->code }} — {{ $coupon->discountLabel() }}</option>@endforeach</select></label>
            <label class="milestone-admin-toggle"><input type="checkbox" name="is_active" value="1" @checked($milestone->is_active)> Show this step @include('admin.partials.info-help', ['key' => 'milestone_show_step', 'text' => 'On thakle customer/member ei step dekhte pabe.'])</label>
            <button class="brand-primary-button" type="submit">Save step</button>
        </form>
        <form method="POST" action="{{ route('admin.milestones.destroy', $milestone) }}" class="milestone-remove-form" onsubmit="return confirm('Remove step {{ $milestone->step }}? Earned coupons will remain in member wallets.')">@csrf @method('DELETE')<button type="submit" aria-label="Remove step {{ $milestone->step }}">× Remove</button></form>
        </div>
        @endforeach
    </div>
    <div class="brand-page-header"><div><h2>Member progress</h2><p>See exactly which step every member has reached.</p></div></div>
    <div class="brand-table-wrapper"><table class="brand-table"><thead><tr><th>Member</th><th>Division</th><th>Purchases @include('admin.partials.info-help', ['key' => 'milestone_table_purchases', 'text' => 'Member total purchase/order count.'])</th><th>Current position @include('admin.partials.info-help', ['key' => 'milestone_table_position', 'text' => 'Member reward journey-te currently kon step-e ache.'])</th></tr></thead><tbody>@forelse($members as $member)<tr><td><strong>{{ $member->name }}</strong><small>{{ $member->email }}</small></td><td>{{ strtoupper($member->division ?: '—') }}</td><td>{{ $member->orders_count }}</td><td>{{ $member->orders_count >= $milestones->count() ? $milestones->count().' / '.$milestones->count().' · Coming soon' : $member->orders_count.' / '.$milestones->count() }}</td></tr>@empty<tr><td colspan="4">No members yet.</td></tr>@endforelse</tbody></table></div>{{ $members->links() }}
</section>
<style>.milestone-admin-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin:24px 0 36px}.milestone-admin-wrap{display:flex;flex-direction:column}.milestone-admin-card{position:relative;display:grid;gap:11px;flex:1;padding:20px;border:1px solid #ddd;border-radius:18px;background:#fff}.milestone-admin-card>b{position:absolute;right:16px;top:14px;font-size:30px;color:#ddd}.milestone-admin-card label{display:grid;gap:6px;font-size:12px;font-weight:800}.milestone-admin-card input,.milestone-admin-card select{width:100%;padding:11px;border:1px solid #ccc;border-radius:10px}.milestone-admin-card .milestone-admin-toggle{display:flex;align-items:center}.milestone-admin-toggle input{width:auto}.milestone-remove-form{margin:8px 12px 0}.milestone-remove-form button{width:100%;padding:9px;border:1px solid #e4b3b3;border-radius:10px;background:#fff5f5;color:#b42318;font-weight:800;cursor:pointer}.milestone-add-button{white-space:nowrap}@media(max-width:700px){.brand-page-header{align-items:flex-start}.milestone-add-button{width:100%}}</style>
@endsection
