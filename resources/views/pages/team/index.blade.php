<?php

use App\Concerns\TeamValidationRules;
use App\Enums\Permission;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Team')] #[Layout('layouts::coolapp')] class extends Component {
    use TeamValidationRules, WithPagination;

    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()->with('role')->orderByDesc('created_at')->paginate(15);
    }

    public function openCreateForm(): void
    {
        $this->dispatch('open-user-form');
    }

    public function openEditForm(int $userId): void
    {
        $this->dispatch('open-user-form', userId: $userId);
    }

    /**
     * Delete a team member.
     */
    public function deleteUser(int $userId): void
    {
        if ($userId === Auth::id()) {
            $this->dispatch('toast', variant: 'error', text: "You can't delete your own account here.");

            return;
        }

        $user = User::with('role')->findOrFail($userId);

        if ($user->role?->is_founder && $this->founderCount() <= 1) {
            $this->dispatch('toast', variant: 'error', text: 'At least one Founder must remain.');

            return;
        }

        if (Lead::where('created_by', $userId)->exists()) {
            $this->dispatch('toast', variant: 'error', text: "This user has created leads and can't be deleted.");

            return;
        }

        $user->delete();

        unset($this->users);

        $this->dispatch('toast', variant: 'success', text: 'User deleted.');
    }

    #[On('user-saved')]
    public function onUserSaved(): void
    {
        unset($this->users);
    }

    private function founderCount(): int
    {
        return User::whereHas('role', fn ($query) => $query->where('is_founder', true))->count();
    }
}; ?>

<div>
<div class="page-header">
    <div>
        <h1>{{ __('Team') }}</h1>
        <p class="subtitle">{{ __('Manage every job function on the team and what they can access.') }}</p>
    </div>
    <div class="page-header__actions">
        <button type="button" class="btn btn-primary" wire:click="openCreateForm">
            {{ __('Add user') }}
        </button>
    </div>
</div>

<section class="m-card">
    <table class="m-table">
        <thead>
            <tr>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Email') }}</th>
                <th scope="col">{{ __('Role') }}</th>
                <th scope="col">{{ __('Permissions') }}</th>
                <th scope="col">{{ __('Created') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->users as $user)
                <tr wire:key="user-{{ $user->id }}">
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="role {{ $user->role?->is_founder ? 'admin' : 'user' }}">
                            {{ $user->role?->name ?? __('No role') }}
                        </span>
                    </td>
                    <td>
                        @if ($user->role?->is_founder)
                            <span class="badge bg-secondary">{{ __('Full access') }}</span>
                        @elseif (empty($user->permissions))
                            <span class="text-muted small">{{ __('None') }}</span>
                        @else
                            {{ count($user->permissions) }} {{ __('granted') }}
                        @endif
                    </td>
                    <td>{{ $user->created_at?->format('M j, Y') }}</td>
                    <td style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#view-user-{{ $user->id }}">
                            {{ __('View') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openEditForm({{ $user->id }})">
                            {{ __('Edit') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" wire:click="deleteUser({{ $user->id }})" wire:confirm="{{ __('Delete :name? This permanently removes their account.', ['name' => $user->name]) }}">
                            {{ __('Delete') }}
                        </button>
                    </td>
                </tr>

                <div class="modal fade" id="view-user-{{ $user->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $user->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">{{ __('Email') }}</dt>
                                    <dd class="col-sm-8">{{ $user->email }}</dd>

                                    <dt class="col-sm-4">{{ __('Role') }}</dt>
                                    <dd class="col-sm-8">{{ $user->role?->name ?? __('No role') }}</dd>

                                    <dt class="col-sm-4">{{ __('Permissions') }}</dt>
                                    <dd class="col-sm-8">
                                        @if ($user->role?->is_founder)
                                            {{ __('Full access') }}
                                        @elseif (empty($user->permissions))
                                            {{ __('None') }}
                                        @else
                                            {{ collect($user->permissions)->map(fn ($p) => Permission::from($p)->label())->join(', ') }}
                                        @endif
                                    </dd>

                                    <dt class="col-sm-4">{{ __('Joined') }}</dt>
                                    <dd class="col-sm-8">{{ $user->created_at?->format('M j, Y') }}</dd>
                                </dl>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

            @endforeach
        </tbody>
    </table>

    <div style="padding:16px 25px;">
        {{ $this->users->links() }}
    </div>
</section>

<livewire:pages::team.user-form-modal />
</div>
