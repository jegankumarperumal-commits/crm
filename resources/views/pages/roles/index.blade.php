<?php

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Roles')] #[Layout('layouts::coolapp')] class extends Component {
    #[Computed]
    public function roles(): Collection
    {
        return Role::query()->withCount('users')->orderByDesc('is_founder')->orderBy('name')->get();
    }

    public function openCreateForm(): void
    {
        $this->dispatch('open-role-form');
    }

    public function openEditForm(int $roleId): void
    {
        $this->dispatch('open-role-form', roleId: $roleId);
    }

    public function deleteRole(int $roleId): void
    {
        $role = Role::withCount('users')->findOrFail($roleId);

        if ($role->is_founder) {
            throw ValidationException::withMessages(['role' => "The Founder role can't be deleted."]);
        }

        if ($role->users_count > 0) {
            throw ValidationException::withMessages(['role' => 'Reassign the users in this role before deleting it.']);
        }

        $role->delete();

        unset($this->roles);

        $this->dispatch('toast', variant: 'success', text: 'Role deleted.');
    }

    #[On('role-saved')]
    public function onRoleSaved(): void
    {
        unset($this->roles);
    }
}; ?>

<div>
<div class="page-header">
    <div>
        <h1>{{ __('Roles') }}</h1>
        <p class="subtitle">{{ __('Define job functions and what each one can access.') }}</p>
    </div>
    <div class="page-header__actions">
        <button type="button" class="btn btn-primary" wire:click="openCreateForm">
            {{ __('Add role') }}
        </button>
    </div>
</div>

<section class="m-card">
    <table class="m-table">
        <thead>
            <tr>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Permissions') }}</th>
                <th scope="col">{{ __('Users') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->roles as $role)
                <tr wire:key="role-{{ $role->id }}">
                    <td>
                        {{ $role->name }}
                        @if ($role->is_founder)
                            <span class="badge bg-secondary">{{ __('Protected') }}</span>
                        @endif
                    </td>
                    <td>
                        @if ($role->is_founder)
                            <span class="text-muted small">{{ __('Full access') }}</span>
                        @elseif (empty($role->permissions))
                            <span class="text-muted small">{{ __('None') }}</span>
                        @else
                            {{ count($role->permissions) }} {{ __('granted') }}
                        @endif
                    </td>
                    <td>{{ $role->users_count }}</td>
                    <td style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openEditForm({{ $role->id }})">
                            {{ __('Edit') }}
                        </button>
                        @unless ($role->is_founder)
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete-role-modal-{{ $role->id }}">
                                {{ __('Delete') }}
                            </button>
                        @endunless
                    </td>
                </tr>

                <div wire:ignore.self class="modal fade" id="delete-role-modal-{{ $role->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Delete :name?', ['name' => $role->name]) }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body">
                                <p>{{ __('This permanently removes the role. Users with this role must be reassigned first.') }}</p>
                                @error('role')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="button" class="btn btn-danger" wire:click="deleteRole({{ $role->id }})">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </tbody>
    </table>
</section>

<livewire:pages::roles.role-form-modal />
</div>
