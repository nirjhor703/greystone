<x-guest-layout>
    @section('title', 'Register | Grey Stone Admin')

    <div class="auth-form-header">
        <h2>Create account</h2>

        <p>
            Create an account to access the Grey Stone management portal.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="auth-field">
            <label for="name" class="auth-label">
                Full name
            </label>

            <input
                id="name"
                class="auth-input"
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="Enter your full name"
                required
                autofocus
                autocomplete="name"
            >

            @if ($errors->has('name'))
                <ul class="auth-error">
                    @foreach ($errors->get('name') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

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
                    placeholder="Create a password"
                    required
                    autocomplete="new-password"
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

        <div class="auth-field">
            <label for="password_confirmation" class="auth-label">
                Confirm password
            </label>

            <div class="auth-input-wrap">
                <input
                    id="password_confirmation"
                    class="auth-input has-password-toggle"
                    type="password"
                    name="password_confirmation"
                    placeholder="Enter the password again"
                    required
                    autocomplete="new-password"
                >

                <button
                    type="button"
                    class="password-toggle"
                    data-password-toggle="password_confirmation"
                >
                    Show
                </button>
            </div>

            @if ($errors->has('password_confirmation'))
                <ul class="auth-error">
                    @foreach ($errors->get('password_confirmation') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <button type="submit" class="auth-submit">
            Create account
        </button>

        <p class="auth-switch-text">
            Already have an account?

            <a href="{{ route('login') }}">
                Sign in
            </a>
        </p>
    </form>
</x-guest-layout>