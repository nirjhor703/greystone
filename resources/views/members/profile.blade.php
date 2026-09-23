@extends('members.layout')
@section('title', $member->name.' | Grey Stone')
@section('body-class', 'member-theme-'.$member->gender.' member-compact-body member-profile-body')
@section('content')
<main class="signup-screen member-profile-screen"><section class="signup-card member-profile-card">
    <svg class="signup-wave" viewBox="0 0 720 520" preserveAspectRatio="none" aria-hidden="true"><path d="M0 330 C92 156 164 104 244 151 C337 206 330 350 464 425 C557 477 645 479 720 451 L720 520 L0 520 Z" /></svg>
    <header class="signup-head profile-wave-head">
        <a href="{{ url('/') }}" class="signup-corner-brand">GREY STONE</a>
        <div class="signup-title"><small>WELCOME</small><h1>{{ $member->firstName() }}</h1><span class="profile-points"><b>1000</b> points</span></div>
        <button type="button" class="profile-wave-avatar" id="profilePhotoMenuButton" aria-label="Profile picture options">@if($member->avatar_url)<img src="{{ $member->avatar_url }}" alt="{{ $member->name }}">@else<img src="{{ asset('images/member-camera-placeholder.png') }}" alt="Add profile photo">@endif</button>
    </header>
    @php
        $missingProfileFields = collect([
            'name' => $member->name,
            'email' => $member->email,
            'gender' => $member->gender,
            'mobile number' => $member->mobile,
            'address' => $member->address,
            'date of birth' => $member->date_of_birth,
            'division' => $member->division,
        ])->filter(fn ($value) => blank($value))->keys();
        $availableCouponCount = $walletCoupons->where('status', 'available')->count();
        $currentStage = max(1, min($purchaseCount ?: 1, $journeyLength));
    @endphp
    <section class="member-profile-dashboard" id="wallet">
        @if($missingProfileFields->isNotEmpty())
        <aside class="profile-completion">
            <span class="profile-completion-icon">✦</span>
            <div><strong>Membership Pending!</strong><span>Add your {{ $missingProfileFields->implode(', ') }} to unlock all member rewards.</span></div>
            <a href="{{ route('member.edit') }}"><span>Complete profile</span><b>→</b></a>
        </aside>
        @endif
        @if(session('milestone_collected'))<div class="milestone-collect-notice"><b>✓</b><span>Coupon collected! It is now safely stored in your wallet.</span></div>@endif
        @error('milestone')<div class="milestone-collect-notice is-error"><b>!</b><span>{{ $message }}</span></div>@enderror
        <section class="member-account-overview" aria-label="Membership overview">
            <header><small>MEMBERSHIP OVERVIEW</small><strong>{{ $missingProfileFields->isEmpty() ? 'Active Member' : 'Profile Incomplete' }}</strong></header>
            <div class="member-overview-stats">
                <article><strong>1000</strong><span>Points</span></article>
                <article><strong>{{ $purchaseCount }}</strong><span>Purchases</span></article>
                <article><strong>{{ $availableCouponCount }}</strong><span>Coupons</span></article>
            </div>
        </section>
        <div class="member-dashboard-actions">
            <button type="button" class="member-dashboard-card profile-summary-trigger" data-profile-details-open aria-label="Open profile details"><span class="member-action-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/></svg></span><strong>PROFILE</strong></button>
            <button type="button" class="member-dashboard-card member-wallet-trigger" data-wallet-open aria-label="Open coupon wallet; {{ $availableCouponCount }} coupon available"><span class="member-action-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 7.5h18v9H3z"/><path d="M8 7.5v9M16 7.5v9M8 12h8"/></svg></span><strong>COUPONS</strong></button>
        </div>
        <div class="member-milestone-panel">
            <button type="button" class="member-milestone-trigger" data-milestone-open aria-label="Open reward milestones; {{ min($purchaseCount, $journeyLength) }} of {{ $journeyLength }} reached"><span class="member-action-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 4h8v5a4 4 0 0 1-8 0z"/><path d="M8 6H4v2a4 4 0 0 0 4 4M16 6h4v2a4 4 0 0 1-4 4M12 13v4M8 21h8M9 17h6v4"/></svg></span><strong>MILESTONES</strong></button>
            <div class="member-stage-strip" aria-label="Current reward stage {{ $currentStage }}">
                <span class="member-stage-line" aria-hidden="true"></span>
                @foreach($milestones as $milestone)
                    @php
                        $stageCollected = $walletCoupons->contains(fn ($item) => $item->source === 'milestone:'.$milestone->step);
                    @endphp
                    <button type="button" class="member-stage-dot {{ $purchaseCount >= $milestone->step ? 'is-reached' : '' }} {{ $currentStage === $milestone->step ? 'is-active' : '' }} {{ $stageCollected ? 'is-collected' : '' }}" data-milestone-open aria-label="Open milestone {{ $milestone->step }}{{ $stageCollected ? ', coupon collected' : '' }}"><b>{{ $stageCollected ? '✓' : $milestone->step }}</b></button>
                @endforeach
            </div>
        </div>
    </section>
    <div class="member-profile-details-modal member-glass-modal" id="memberProfileDetailsModal" hidden>
        <button type="button" class="member-glass-backdrop" data-profile-details-close aria-label="Close profile details"></button>
        <section role="dialog" aria-modal="true" aria-labelledby="profileDetailsTitle">
            <header><div><small>MEMBER ACCOUNT</small><h2 id="profileDetailsTitle">Profile Details</h2><span class="profile-modal-avatar">@if($member->avatar_url)<img src="{{ $member->avatar_url }}" alt="{{ $member->name }}">@else<img src="{{ asset('images/member-camera-placeholder.png') }}" alt="No profile photo added">@endif</span></div><button type="button" data-profile-details-close aria-label="Close">×</button></header>
            <div class="profile-detail-list">
                <article class="profile-field-name"><span>Name</span><strong>{{ $member->name ?: 'Not added' }}</strong></article>
                <article class="profile-field-email"><span>Email</span><strong>{{ $member->email ?: 'Not added' }}</strong></article>
                <article class="profile-field-mobile"><span>Mobile</span><strong>{{ $member->mobile ?: 'Not added' }}</strong></article>
                <article class="profile-field-gender"><span>Gender</span><strong>{{ $member->gender ? ucfirst($member->gender) : 'Not added' }}</strong></article>
                @if($member->mobile_2)<article class="profile-field-mobile-two"><span>Mobile 2</span><strong>{{ $member->mobile_2 }}</strong></article>@endif
                <article class="profile-field-division profile-division division-{{ $member->division ?: 'pending' }}"><span>Division</span><strong>{{ App\Models\Member::DIVISIONS[$member->division] ?? 'Not added' }}</strong></article>
                <article class="profile-field-dob"><span>Date of birth</span><strong>{{ $member->date_of_birth?->format('d M Y') ?: 'Not added' }}</strong></article>
                <article class="profile-field-address"><span>Address</span><strong>{{ $member->address ?: 'Not added' }}</strong></article>
            </div>
            <a class="member-modal-edit" href="{{ route('member.edit') }}">EDIT PROFILE <b>→</b></a>
        </section>
    </div>
    <div class="member-milestone-modal member-glass-modal" id="memberMilestoneModal" hidden>
        <button type="button" class="member-glass-backdrop" data-milestone-close aria-label="Close reward milestones"></button>
        <section role="dialog" aria-modal="true" aria-labelledby="milestoneModalTitle">
            <header><div><small>GREY STONE REWARDS</small><h2 id="milestoneModalTitle">Reward Milestones</h2></div><button type="button" data-milestone-close aria-label="Close">×</button></header>
            <div class="member-milestone" style="--journey-progress: {{ min(100, ($purchaseCount / $journeyLength) * 100) }}%">
            <div class="milestone-heading"><h2>Reward Milestones</h2></div>
            <div class="milestone-timeline">
                @foreach($milestones as $milestone)
                    @php
                        $walletReward = $walletCoupons->first(fn ($item) => $item->coupon_id === $milestone->coupon_id && $item->source === 'milestone:'.$milestone->step);
                        $complete = $purchaseCount >= $milestone->step;
                        $current = !$complete && $purchaseCount + 1 === $milestone->step;
                    @endphp
                    <article class="milestone-event {{ $complete ? 'is-complete' : '' }} {{ $current ? 'is-current' : '' }}" style="--step-delay: {{ $loop->index * 80 }}ms">
                        <div class="milestone-copy">
                            <small>PURCHASE {{ str_pad($milestone->step, 2, '0', STR_PAD_LEFT) }}</small>
                            <h3>{{ $milestone->title }}</h3>
                            <p>{{ $milestone->description }}</p>
                        </div>
                        <span class="milestone-orb">{{ $complete ? '✓' : $milestone->step }}</span>
                        @if($walletReward)
                            <button type="button" class="milestone-reward is-collected milestone-ticket" data-wallet-open><i><span>DISCOUNT</span><b>{{ $walletReward->coupon->discountLabel() }}</b></i><strong>COUPON</strong><code>{{ $walletReward->coupon->code }}</code><em>{{ $walletReward->coupon->expires_at ? 'VALID UNTIL '.$walletReward->coupon->expires_at->format('d M Y') : 'MEMBER EXCLUSIVE' }}<b>{{ $walletReward->status === 'available' ? '✓ COLLECTED' : 'USED' }}</b></em></button>
                        @elseif($milestone->coupon && $complete)
                            <form method="POST" action="{{ route('member.milestone.collect', $milestone) }}" class="milestone-reward-shell">@csrf<div class="milestone-reward is-active milestone-ticket"><i><span>DISCOUNT</span><b>{{ $milestone->coupon->discountLabel() }}</b></i><strong>COUPON</strong><code>{{ $milestone->coupon->code }}</code><em>{{ $milestone->coupon->expires_at ? 'VALID UNTIL '.$milestone->coupon->expires_at->format('d M Y') : 'MEMBER EXCLUSIVE' }}</em></div><button type="submit" class="milestone-collect-button">COLLECT IN WALLET <b>→</b></button></form>
                        @elseif($milestone->coupon)
                            <div class="milestone-reward is-locked milestone-ticket"><i><span>DISCOUNT</span><b>{{ $milestone->coupon->discountLabel() }}</b></i><strong>COUPON</strong><code>LOCKED</code><em>COMPLETE PURCHASE {{ $milestone->step }} TO UNLOCK</em></div>
                        @else
                            <div class="milestone-reward is-empty milestone-ticket"><i><span>THIS</span><b>STEP</b></i><strong>NO COUPON</strong><code>REWARD</code><em>{{ $complete ? 'STEP COMPLETE' : 'KEEP GOING' }}</em></div>
                        @endif
                    </article>
                @endforeach
                <article class="milestone-event milestone-coming" style="--step-delay: 850ms"><div class="milestone-copy"><small>THE JOURNEY CONTINUES</small><h3>Coming soon</h3><p>Fresh rewards and member-only surprises are on the way.</p></div><span class="milestone-orb">∞</span><div class="milestone-reward is-empty milestone-ticket"><i><span>NEXT</span><b>STEP</b></i><strong>MORE REWARDS</strong><code>SOON</code><em>COMING SOON</em></div></article>
            </div>
        </div>
        </section>
    </div>
    @include('members.partials.wallet-modal', ['walletCoupons' => $walletCoupons])
    @if(session('member_welcome'))
    <div class="member-congrats-modal" id="memberCongratsModal" role="dialog" aria-modal="true" aria-labelledby="memberCongratsTitle">
        <button type="button" class="member-congrats-backdrop" data-close-congrats aria-label="Close congratulations"></button>
        <div class="member-congrats-card">
            <button type="button" class="member-congrats-close" data-close-congrats aria-label="Close">×</button>
            <div class="member-congrats-aura" aria-hidden="true"></div>
            <img src="{{ asset('images/member-congratulations.png') }}" alt="Congratulations! You're now a Grey Stone member">
            <h2 id="memberCongratsTitle">Welcome, {{ $member->firstName() }}!</h2>
            <p>Your Coupon Wallet is open. Shop more, unlock more coupons and enjoy exclusive member-only rewards.</p>
            <button type="button" class="member-congrats-action" data-close-congrats>Explore my membership <b>→</b></button>
        </div>
    </div>
    @endif
    <div class="member-signout-modal member-glass-modal" id="memberSignoutModal" hidden>
        <button type="button" class="member-glass-backdrop" data-signout-close aria-label="Stay signed in"></button>
        <section role="dialog" aria-modal="true" aria-labelledby="memberSignoutTitle">
            <span class="member-signout-icon" aria-hidden="true">↗</span>
            <small>LEAVING SO SOON?</small>
            <h2 id="memberSignoutTitle">Sign out?</h2>
            <p>Are you sure you want to sign out of your Grey Stone membership?</p>
            <div><button type="button" data-signout-close>NO, STAY</button><button type="submit" form="memberSignoutForm">YES, SIGN OUT</button></div>
        </section>
    </div>
    <div class="profile-actions"><a href="{{ url('/') }}">SHOP NOW <b>→</b></a><form id="memberSignoutForm" method="POST" action="{{ route('member.logout') }}">@csrf<button type="button" data-signout-open>SIGN OUT</button></form></div>
    <div class="profile-photo-modal" id="profilePhotoModal" hidden><button class="profile-photo-backdrop" type="button" data-close-photo></button><section role="dialog" aria-modal="true" aria-label="Profile picture options"><strong>Profile picture</strong>@if($member->avatar_url)<button type="button" id="seeProfilePicture">See picture</button>@endif<form method="POST" enctype="multipart/form-data" action="{{ route('member.photo.update') }}">@csrf @method('PUT')<label>Upload photo<input type="file" name="profile_photo" accept="image/*" required onchange="this.form.submit()"></label></form><button type="button" data-close-photo>Cancel</button></section></div>
    @if($member->avatar_url)<div class="profile-picture-viewer" id="profilePictureViewer" hidden><button type="button" data-close-viewer aria-label="Close picture preview"></button><section role="dialog" aria-modal="true" aria-label="Profile picture preview"><img src="{{ $member->avatar_url }}" alt="{{ $member->name }}"><button type="button" data-close-viewer>Close</button></section></div>@endif
</section></main>
<script>const photoModal=document.getElementById('profilePhotoModal'),pictureViewer=document.getElementById('profilePictureViewer'),walletModal=document.getElementById('memberWalletModal'),congratsModal=document.getElementById('memberCongratsModal'),profileDetailsModal=document.getElementById('memberProfileDetailsModal'),milestoneModal=document.getElementById('memberMilestoneModal'),signoutModal=document.getElementById('memberSignoutModal');const togglePageModal=(modal,open)=>{if(!modal)return;open?modal.removeAttribute('hidden'):modal.setAttribute('hidden','');document.body.classList.toggle('member-modal-open',open)};document.getElementById('profilePhotoMenuButton')?.addEventListener('click',()=>photoModal.hidden=false);document.querySelectorAll('[data-close-photo]').forEach(button=>button.addEventListener('click',()=>photoModal.hidden=true));document.getElementById('seeProfilePicture')?.addEventListener('click',()=>{photoModal.hidden=true;pictureViewer.hidden=false});document.querySelectorAll('[data-close-viewer]').forEach(button=>button.addEventListener('click',()=>pictureViewer.hidden=true));document.querySelectorAll('[data-wallet-open]').forEach(button=>button.addEventListener('click',()=>walletModal?.removeAttribute('hidden')));document.querySelectorAll('[data-wallet-close]').forEach(button=>button.addEventListener('click',()=>walletModal?.setAttribute('hidden','')));document.querySelectorAll('[data-profile-details-open]').forEach(button=>button.addEventListener('click',()=>togglePageModal(profileDetailsModal,true)));document.querySelectorAll('[data-profile-details-close]').forEach(button=>button.addEventListener('click',()=>togglePageModal(profileDetailsModal,false)));document.querySelectorAll('[data-milestone-open]').forEach(button=>button.addEventListener('click',()=>togglePageModal(milestoneModal,true)));document.querySelectorAll('[data-milestone-close]').forEach(button=>button.addEventListener('click',()=>togglePageModal(milestoneModal,false)));document.querySelectorAll('[data-signout-open]').forEach(button=>button.addEventListener('click',()=>togglePageModal(signoutModal,true)));document.querySelectorAll('[data-signout-close]').forEach(button=>button.addEventListener('click',()=>togglePageModal(signoutModal,false)));document.addEventListener('keydown',event=>{if(event.key==='Escape'){togglePageModal(profileDetailsModal,false);togglePageModal(milestoneModal,false);togglePageModal(signoutModal,false)}});document.querySelectorAll('[data-close-congrats]').forEach(button=>button.addEventListener('click',()=>congratsModal?.remove()));</script>
@endsection
