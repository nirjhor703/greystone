@extends('members.layout')
@section('title', 'Member Sign In | Grey Stone')
@section('body-class', 'member-compact-body')
@section('content')
<main class="signup-screen member-login-screen"><section class="signup-card member-login-card">
    <svg class="signup-wave" viewBox="0 0 720 520" preserveAspectRatio="none" aria-hidden="true"><path d="M0 330 C92 156 164 104 244 151 C337 206 330 350 464 425 C557 477 645 479 720 451 L720 520 L0 520 Z" /></svg>
    <header class="signup-head">
        <a href="{{ url('/') }}" class="signup-corner-brand">GREY STONE</a>
        <div class="signup-title"><h1>Sign In</h1></div>
        <div class="signup-google-block"><span>Or</span><a href="{{ $googleReady ? route('member.google.redirect') : '#' }}" class="signup-google {{ $googleReady ? '' : 'is-disabled' }}" @if(!$googleReady) aria-disabled="true" onclick="return false" @endif><svg viewBox="0 0 18 18" aria-hidden="true"><path fill="#4285F4" d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.797 2.715v2.259h2.909c1.702-1.567 2.684-3.878 2.684-6.614Z"/><path fill="#34A853" d="M9 18c2.43 0 4.468-.806 5.956-2.181l-2.909-2.259c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.714H.956v2.333A9 9 0 0 0 9 18Z"/><path fill="#FBBC05" d="M3.963 10.705A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.167.281-1.705V4.962H.956A9 9 0 0 0 0 9c0 1.452.347 2.827.956 4.038l3.007-2.333Z"/><path fill="#EA4335" d="M9 3.581c1.321 0 2.507.454 3.441 1.346l2.581-2.581C13.464.892 11.426 0 9 0A9 9 0 0 0 .956 4.962l3.007 2.333C4.672 5.166 6.656 3.581 9 3.581Z"/></svg><span>Continue with Google</span></a></div>
    </header>
    <form method="POST" action="{{ route('member.login.store') }}" class="member-login-form">@csrf
        <div class="signup-field"><label for="loginEmail">Email</label><input id="loginEmail" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">@error('email')<small>{{ $message }}</small>@enderror</div>
        <div class="signup-field"><label for="loginPassword">Password</label><input id="loginPassword" type="password" name="password" required autocomplete="current-password"></div>
        <button class="signup-submit" type="submit"><span>SIGN IN</span><b>→</b></button>
        <a class="login-shopping" href="{{ url('/') }}"><span>GO FOR SHOPPING</span><b>→</b></a>
    </form>
    <p class="signup-login">Not a member? <a href="{{ route('member.register') }}">Join now</a></p>
</section></main>
@endsection
