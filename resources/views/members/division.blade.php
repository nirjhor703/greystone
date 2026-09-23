@extends('members.layout')
@section('title', 'Last step | Grey Stone')
@section('body-class', 'member-theme-'.$selectedGender.' member-compact-body')
@section('content')
<main class="signup-screen member-division-screen"><section class="signup-card member-division-card">
    <svg class="signup-wave" viewBox="0 0 720 520" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0 330 C92 156 164 104 244 151 C337 206 330 350 464 425 C557 477 645 479 720 451 L720 520 L0 520 Z" />
    </svg>
    <header class="signup-head division-wave-head">
        <a href="{{ ($editing ?? false) ? route('member.edit') : route('member.form') }}" class="division-back" aria-label="Back to details">←</a>
        <a href="{{ url('/') }}" class="signup-corner-brand">GREY STONE</a>
        <div class="signup-title"><small>02 / 02</small><h1>Last step</h1><p>Choose the division you call home.</p></div>
    </header>
    <form method="POST" action="{{ route('member.finish') }}" class="division-form">@csrf
        <div class="division-choice-grid">
            @foreach ($divisions as $key => $label)
                <label class="division-choice division-{{ $key }}">
                    <input type="radio" name="division" value="{{ $key }}" @checked(old('division', $selectedDivision ?? null) === $key) required>
                    <span><b>{{ $label }}</b></span>
                </label>
            @endforeach
        </div>
        @error('division')<small class="division-error">{{ $message }}</small>@enderror
        <button class="division-finish" type="submit"><span>FINISH</span><b>→</b></button>
    </form>
</section></main>
@endsection
