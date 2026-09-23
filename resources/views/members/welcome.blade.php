@extends('members.layout')
@section('title', 'Welcome | Grey Stone Members')
@section('body-class', 'member-welcome-body')
@section('content')
<main class="member-welcome-screen">
    <svg class="member-welcome-waves" viewBox="0 0 430 760" preserveAspectRatio="none" aria-hidden="true">
        <path class="welcome-wave-one" d="M0 398 C75 292 128 274 181 354 C247 454 292 520 430 442 L430 760 L0 760 Z" />
        <path class="welcome-wave-two" d="M0 443 C78 330 135 318 193 402 C255 493 306 556 430 478 L430 760 L0 760 Z" />
        <path class="welcome-wave-three" d="M0 493 C76 380 137 367 198 452 C262 542 316 594 430 520 L430 760 L0 760 Z" />
    </svg>
    <a href="{{ url('/') }}" class="member-welcome-brand">GREY STONE</a>
    <section class="member-welcome-copy">
        <span>MEMBERSHIP</span>
        <h1>Welcome.</h1>
        <p>Let’s sign you up before we start.</p>
    </section>
    <a href="{{ route('member.form') }}" class="member-welcome-next" aria-label="Start registration">→</a>
</main>
@endsection
