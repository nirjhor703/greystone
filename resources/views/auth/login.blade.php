<x-guest-layout>
    @section('title', 'Login | Grey Stone Admin')

    <div class="auth-form-header">
        <h2>Welcome back</h2>

        <p>
            Sign in to continue to the Grey Stone management dashboard.
        </p>
    </div>

    @if (session('status'))
        <div class="auth-session-status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="auth-field">
            <label for="email" class="auth-label">
                Email address
            </label>

            <input
                id="email"
                class="auth-input"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Enter your email"
                required
                autofocus
                autocomplete="username"
            >

            @if ($errors->has('email'))
                <ul class="auth-error">
                    @foreach ($errors->get('email') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="auth-field">
            <label for="password" class="auth-label">
                Password
            </label>

            <div class="auth-input-wrap">
                <input
                    id="password"
                    class="auth-input has-password-toggle"
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                >

                <button
                    type="button"
                    class="password-toggle"
                    data-password-toggle="password"
                >
                    Show
                </button>
            </div>

            @if ($errors->has('password'))
                <ul class="auth-error">
                    @foreach ($errors->get('password') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="auth-row">
            <label for="remember_me" class="auth-checkbox-label">
                <input
                    id="remember_me"
                    class="auth-checkbox"
                    type="checkbox"
                    name="remember"
                >

                <span>Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="auth-link"
                >
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="auth-submit">
            Sign in
        </button>

        @if (Route::has('register'))
            <p class="auth-switch-text">
                Don’t have an account?

                <a href="{{ route('register') }}">
                    Create account
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>