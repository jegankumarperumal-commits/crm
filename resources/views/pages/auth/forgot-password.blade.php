<x-layouts::auth-coolapp :title="__('Forgot password')">
    <x-auth-header-coolapp :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

    <x-auth-session-status-coolapp :status="session('status')" />

    <form class="login-form" method="POST" action="{{ route('password.email') }}">
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
                autofocus
                required
            >
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <button class="au-btn au-btn--green" type="submit" data-test="email-password-reset-link-button">
            {{ __('Email password reset link') }}
        </button>
    </form>

    <div class="register-link">
        <p>{{ __('Or, return to') }} <a href="{{ route('login') }}">{{ __('log in') }}</a></p>
    </div>
</x-layouts::auth-coolapp>
