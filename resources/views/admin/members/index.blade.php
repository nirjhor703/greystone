@extends('admin.layouts.app')
@section('title', 'Members | Grey Stone Admin')
@section('page-title', 'Customer Members')
@section('page-subtitle', 'Member audience data for gender and location based campaigns')
@section('content')
<section class="brand-page-card customer-page-card">
    <div class="brand-page-header"><div><h2>Members</h2><p>Customers who completed the membership onboarding.</p></div></div>
    <div class="customer-stat-grid">
        <article><span>Total Members</span><strong>{{ number_format($totalMembers) }}</strong></article>
        <article><span>Male</span><strong>{{ number_format($maleMembers) }}</strong></article>
        <article><span>Female</span><strong>{{ number_format($femaleMembers) }}</strong></article>
        <article><span>With Google</span><strong>{{ number_format(App\Models\Member::whereNotNull('google_id')->count()) }}</strong></article>
    </div>
    <form method="GET" class="admin-ajax-search"><div class="admin-search-grid">
        <div class="admin-search-field"><label>Search</label><input type="search" name="search" value="{{ request('search') }}" placeholder="Name, email, mobile or address"></div>
        <div class="admin-search-field"><label>Gender</label><select name="gender"><option value="">All genders</option><option value="male" @selected(request('gender')==='male')>Male</option><option value="female" @selected(request('gender')==='female')>Female</option></select></div>
        <div class="admin-search-field"><label>Division</label><select name="division"><option value="">All divisions</option>@foreach($divisions as $key=>$label)<option value="{{ $key }}" @selected(request('division')===$key)>{{ $label }}</option>@endforeach</select></div>
        <button class="brand-primary-button" type="submit">Filter</button>
    </div></form>
    <div class="brand-table-wrapper"><table class="brand-table customer-table"><thead><tr><th>Member</th><th>Phones</th><th>Gender</th><th>Division</th><th>Date of birth</th><th>Address</th><th>Joined</th></tr></thead><tbody>
        @forelse($members as $member)<tr><td><div class="customer-cell"><span>{{ mb_strtoupper(mb_substr($member->name,0,1)) }}</span><div><strong>{{ $member->name }}</strong><small>{{ $member->email }}</small></div></div></td><td><strong>{{ $member->mobile }}</strong>@if($member->mobile_2)<small>{{ $member->mobile_2 }}</small>@endif</td><td>{{ ucfirst($member->gender) }}</td><td>{{ $divisions[$member->division] }}</td><td>{{ $member->date_of_birth->format('d M Y') }}</td><td>{{ Str::limit($member->address,45) }}</td><td>{{ $member->created_at->format('d M Y') }}</td></tr>
        @empty<tr><td colspan="7"><div class="brand-empty-state"><strong>No members found</strong><span>Completed registrations will appear here.</span></div></td></tr>@endforelse
    </tbody></table></div>{{ $members->links() }}
</section>
@endsection
