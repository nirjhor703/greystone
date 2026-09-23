@extends('members.layout')
@section('title', 'Sign up | Grey Stone')
@php
    $editing = $editing ?? false;
    $selectedGender = old('gender', $googleProfile['gender'] ?? 'neutral');
    $hasGoogleGender = in_array($googleProfile['gender'] ?? null, ['male', 'female'], true);
@endphp
@section('body-class', 'member-theme-'.$selectedGender.' member-compact-body'.($hasGoogleGender ? ' member-splash' : '').($editing ? ' member-editing' : ''))
@section('content')
<main class="signup-screen"><section class="signup-card">
    <svg class="signup-wave" viewBox="0 0 720 520" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0 330 C92 156 164 104 244 151 C337 206 330 350 464 425 C557 477 645 479 720 451 L720 520 L0 520 Z" />
    </svg>
    <header class="signup-head">
        @if($editing)<a href="{{ route('member.profile') }}" class="signup-edit-back" aria-label="Back to profile">←</a>@endif
        <a href="{{ url('/') }}" class="signup-corner-brand">GREY STONE</a>
        <div class="signup-title"><h1>{{ $editing ? 'Edit profile' : 'Sign Up' }}</h1></div>
        @unless($editing)<div class="signup-google-block">
            <span>Or</span>
            <a href="{{ route('member.google.redirect') }}" class="signup-google"><svg viewBox="0 0 18 18" aria-hidden="true"><path fill="#4285F4" d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.797 2.715v2.259h2.909c1.702-1.567 2.684-3.878 2.684-6.614Z"/><path fill="#34A853" d="M9 18c2.43 0 4.468-.806 5.956-2.181l-2.909-2.259c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.714H.956v2.333A9 9 0 0 0 9 18Z"/><path fill="#FBBC05" d="M3.963 10.705A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.167.281-1.705V4.962H.956A9 9 0 0 0 0 9c0 1.452.347 2.827.956 4.038l3.007-2.333Z"/><path fill="#EA4335" d="M9 3.581c1.321 0 2.507.454 3.441 1.346l2.581-2.581C13.464.892 11.426 0 9 0A9 9 0 0 0 .956 4.962l3.007 2.333C4.672 5.166 6.656 3.581 9 3.581Z"/></svg><span>Continue with Google</span></a>
        </div>@endunless
        <label class="signup-photo-picker" for="memberPhoto"><span class="signup-photo-preview">@if(!empty($googleProfile['avatar_url']))<img src="{{ $googleProfile['avatar_url'] }}" alt="Profile preview">@else<img class="is-placeholder" src="{{ asset('images/member-camera-placeholder.png') }}" alt="Choose a profile image">@endif</span><b>{{ !empty($googleProfile['avatar_url']) ? 'CHANGE PHOTO' : 'CHOOSE AN IMAGE' }}</b></label>
    </header>
    @if ($errors->has('google'))<div class="member-alert">{{ $errors->first('google') }}</div>@endif
    <form id="memberDetailsForm" method="POST" enctype="multipart/form-data" action="{{ $editing ? route('member.edit.details') : route('member.details') }}" class="signup-form">@csrf @if($editing) @method('PUT') @endif
        <input id="memberPhoto" type="file" name="profile_photo" accept="image/*" hidden>
        <div class="signup-field"><label for="memberName">Name</label><input id="memberName" name="name" value="{{ old('name', $googleProfile['name'] ?? '') }}" required autocomplete="name">@error('name')<small>{{ $message }}</small>@enderror</div>
        <div class="signup-field"><label for="memberEmail">Email</label><input id="memberEmail" type="email" name="email" value="{{ old('email', $googleProfile['email'] ?? '') }}" required autocomplete="email" @readonly(!$editing && !empty($googleProfile['google_id']))>@error('email')<small>{{ $message }}</small>@enderror</div>
        <fieldset class="signup-gender signup-wide"><legend>Gender</legend><div>@foreach (['male' => 'Male', 'female' => 'Female'] as $value => $label)<label><input type="radio" name="gender" value="{{ $value }}" @checked($selectedGender === $value) required><span>{{ $label }}</span></label>@endforeach</div>@error('gender')<small>{{ $message }}</small>@enderror</fieldset>
        <div class="signup-field"><label for="memberMobile">Mobile 1</label><input id="memberMobile" type="tel" name="mobile" value="{{ old('mobile', $googleProfile['mobile'] ?? '') }}" required autocomplete="tel">@error('mobile')<small>{{ $message }}</small>@enderror</div>
        <div class="signup-field"><label for="memberMobile2">Mobile 2 <em>optional</em></label><input id="memberMobile2" type="tel" name="mobile_2" value="{{ old('mobile_2', $googleProfile['mobile_2'] ?? '') }}">@error('mobile_2')<small>{{ $message }}</small>@enderror</div>
        <div class="signup-field signup-wide"><label for="memberAddress">Address</label><input id="memberAddress" name="address" value="{{ old('address', $googleProfile['address'] ?? '') }}" required autocomplete="street-address">@error('address')<small>{{ $message }}</small>@enderror</div>
        <div class="signup-field signup-wide signup-birthday"><label for="memberDob">Date of birth</label><div class="birthday-control"><input id="memberDob" type="text" name="date_of_birth" value="{{ old('date_of_birth', $googleProfile['date_of_birth'] ?? '') }}" placeholder="YYYY-MM-DD" readonly required><button type="button" id="openBirthdayCalendar" aria-label="Open date of birth calendar"><span></span></button></div>@error('date_of_birth')<small>{{ $message }}</small>@enderror</div>
        @unless($editing)<div class="signup-field"><label for="memberPassword">Password <em>{{ !empty($googleProfile) ? 'optional' : '' }}</em></label><input id="memberPassword" type="password" name="password" minlength="6" {{ empty($googleProfile) ? 'required' : '' }} autocomplete="new-password">@error('password')<small>{{ $message }}</small>@enderror</div>
        <div class="signup-field"><label for="memberPasswordConfirm">Confirm password</label><input id="memberPasswordConfirm" type="password" name="password_confirmation" minlength="6" autocomplete="new-password"></div>@endunless
        @unless($editing)<div class="signup-field signup-wide"><label for="memberReferralCode">Referral code <em>optional</em></label><input id="memberReferralCode" name="referral_code" value="{{ old('referral_code', $googleProfile['referral_code'] ?? '') }}" placeholder="e.g. NIRJHOR100" style="text-transform:uppercase">@error('referral_code')<small>{{ $message }}</small>@enderror</div>@endunless
        <button class="signup-submit signup-wide" type="submit"><span>GO</span><b>→</b></button>
    </form>
    @unless($editing)<p class="signup-login">Already a member? <a href="{{ route('member.login') }}">Sign in</a></p>@endunless
    <div class="birthday-modal" id="birthdayModal" hidden>
        <button type="button" class="birthday-backdrop" data-close-birthday aria-label="Close calendar"></button>
        <section class="birthday-picker" role="dialog" aria-modal="true" aria-label="Choose date of birth">
            <header><button type="button" id="birthdayPrev" aria-label="Previous month">‹</button><div class="birthday-jump"><div class="birthday-choice"><button type="button" id="birthdayMonthSelect" aria-label="Select month"></button><div class="birthday-choice-menu" id="birthdayMonthMenu" hidden></div></div><div class="birthday-choice"><button type="button" id="birthdayYearSelect" aria-label="Select year"></button><div class="birthday-choice-menu" id="birthdayYearMenu" hidden></div></div></div><button type="button" id="birthdayNext" aria-label="Next month">›</button></header>
            <div class="birthday-weekdays"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
            <div class="birthday-days" id="birthdayDays"></div>
            <button type="button" class="birthday-close" data-close-birthday>Done</button>
        </section>
    </div>
</section></main>
<script>
window.setTimeout(() => document.body.classList.remove('member-splash'), 700);
document.getElementById('memberPhoto')?.addEventListener('change',event=>{const file=event.target.files?.[0],preview=document.querySelector('.signup-photo-preview'),label=document.querySelector('.signup-photo-picker>b');if(!file||!preview)return;const image=document.createElement('img');image.alt='Profile preview';image.src=URL.createObjectURL(file);preview.replaceChildren(image);if(label)label.textContent='CHANGE PHOTO';});
document.querySelectorAll('input[name="gender"]').forEach((input) => input.addEventListener('change', () => {
    document.body.classList.remove('member-theme-neutral','member-theme-male','member-theme-female','member-splash');
    document.body.classList.add(`member-theme-${input.value}`,'member-splash');
    window.setTimeout(() => document.body.classList.remove('member-splash'), 650);
}));
const dobInput=document.getElementById('memberDob'),dobModal=document.getElementById('birthdayModal'),dobDays=document.getElementById('birthdayDays'),dobMonthSelect=document.getElementById('birthdayMonthSelect'),dobYearSelect=document.getElementById('birthdayYearSelect'),dobMonthMenu=document.getElementById('birthdayMonthMenu'),dobYearMenu=document.getElementById('birthdayYearMenu'),today=new Date();
today.setHours(0,0,0,0);let calendarDate=dobInput?.value?new Date(`${dobInput.value}T00:00:00`):new Date(today.getFullYear()-18,today.getMonth(),1);
const monthNames=Array.from({length:12},(_,month)=>new Date(2000,month,1).toLocaleDateString('en-US',{month:'long'}));
monthNames.forEach((name,index)=>{const button=document.createElement('button');button.type='button';button.textContent=name;button.addEventListener('click',()=>{calendarDate=new Date(calendarDate.getFullYear(),index,1);dobMonthMenu.hidden=true;renderBirthdayCalendar();});dobMonthMenu.append(button);});
for(let year=today.getFullYear();year>=1900;year--){const button=document.createElement('button');button.type='button';button.textContent=year;button.addEventListener('click',()=>{calendarDate=new Date(year,calendarDate.getMonth(),1);dobYearMenu.hidden=true;renderBirthdayCalendar();});dobYearMenu.append(button);}
function renderBirthdayCalendar(){const y=calendarDate.getFullYear(),m=calendarDate.getMonth(),first=new Date(y,m,1).getDay(),count=new Date(y,m+1,0).getDate();dobMonthSelect.textContent=monthNames[m];dobYearSelect.textContent=y;dobDays.innerHTML='';for(let i=0;i<first;i++)dobDays.append(document.createElement('span'));for(let day=1;day<=count;day++){const date=new Date(y,m,day),button=document.createElement('button');button.type='button';button.textContent=day;button.disabled=date>=today;const iso=`${y}-${String(m+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;if(dobInput.value===iso)button.classList.add('selected');button.addEventListener('click',()=>{dobInput.value=iso;dobModal.hidden=true;});dobDays.append(button);}}
document.getElementById('openBirthdayCalendar')?.addEventListener('click',()=>{dobModal.hidden=false;renderBirthdayCalendar();});
document.getElementById('birthdayPrev')?.addEventListener('click',()=>{calendarDate=new Date(calendarDate.getFullYear(),calendarDate.getMonth()-1,1);renderBirthdayCalendar();});
document.getElementById('birthdayNext')?.addEventListener('click',()=>{calendarDate=new Date(calendarDate.getFullYear(),calendarDate.getMonth()+1,1);renderBirthdayCalendar();});
dobMonthSelect?.addEventListener('click',()=>{dobYearMenu.hidden=true;dobMonthMenu.hidden=!dobMonthMenu.hidden;});
dobYearSelect?.addEventListener('click',()=>{dobMonthMenu.hidden=true;dobYearMenu.hidden=!dobYearMenu.hidden;});
document.querySelectorAll('[data-close-birthday]').forEach(button=>button.addEventListener('click',()=>dobModal.hidden=true));
</script>
@endsection
