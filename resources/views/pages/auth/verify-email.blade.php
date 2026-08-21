<x-layouts::auth-coolapp :title="__('Email verification')">
    <x-auth-header-coolapp :title="__('Verify email')" :description="__('Please verify your email address by clicking on the link we just emailed to you.')" />

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status-coolapp status="{{ __('A new verification link has been sent to the email address you provided during registration.') }}" class="text-center" />
    @endif

    <div class="d-flex flex-column align-items-center gap-3 mt-4">
        <form method="POST" action="{{ route('verification.send') }}" class="w-100">
            @csrf
            <button class="au-btn au-btn--green w-100" type="submit">
                {{ __('Resend verification email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="cursor-pointer"
                style="background:none; border:0; font-size:14px; color:#666; text-decoration:underline;"
                data-test="logout-button"
            >
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</x-layouts::auth-coolapp>
