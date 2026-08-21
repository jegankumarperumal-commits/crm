<x-layouts::auth-coolapp :title="__('Reset password')">
    <x-auth-header-coolapp :title="__('Reset password')" :description="__('Please enter your new password below')" />

    <x-auth-session-status-coolapp :status="session('status')" />

    <form class="login-form" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">

        <div class="form-group">
            <label for="email">{{ __('Email') }}</label>
            <input
                id="email"
                class="au-input"
                type="email"
                name="email"
                value="{{ request('email') }}"
                autocomplete="email"
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
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                required
            >
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">{{ __('Confirm password') }}</label>
            <input
                id="password_confirmation"
                class="au-input"
                type="password"
                name="password_confirmation"
                placeholder="{{ __('Confirm password') }}"
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                required
            >
            @error('password_confirmation')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <button class="au-btn au-btn--green" type="submit" data-test="reset-password-button">
            {{ __('Reset password') }}
        </button>
    </form>
</x-layouts::auth-coolapp>
