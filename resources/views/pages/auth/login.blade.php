<x-layouts::auth-coolapp :title="__('Log in')">
    <x-auth-header-coolapp :title="__('Welcome back')" :description="__('Enter your email and password below to log in')" />

    <x-auth-session-status-coolapp :status="session('status')" />

    <form class="login-form" method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="form-group">
            <label for="email">{{ __('Email address') }}</label>
            <input
                id="email"
                class="au-input"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="email@example.com"
                autocomplete="email"
                autofocus
                required
            >
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">{{ __('Password') }}</label>
            <input
                id="password"
                class="au-input"
                type="password"
                name="password"
                placeholder="{{ __('Password') }}"
                autocomplete="current-password"
                required
            >
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="login-checkbox">
            <label>
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                {{ __('Remember me') }}
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
        </div>

        <button class="au-btn au-btn--green" type="submit" data-test="login-button">
            {{ __('Log in') }}
        </button>
    </form>
</x-layouts::auth-coolapp>
