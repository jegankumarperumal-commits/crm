<?php

use App\Concerns\TeamValidationRules;
use App\Enums\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    use TeamValidationRules;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role_id = '';

    /** @var array<int, string> */
    public array $permissions = [];

    #[Computed]
    public function isEditing(): bool
    {
        return $this->userId !== null;
    }

    #[Computed]
    public function roles(): Collection
    {
        return Role::query()->orderByDesc('is_founder')->orderBy('name')->get();
    }

    #[Computed]
    public function selectedRoleIsFounder(): bool
    {
        return (bool) Role::find($this->role_id)?->is_founder;
    }

    public function updatedRoleId(string $value): void
    {
        $this->permissions = Role::find($value)?->permissions ?? [];
    }

    #[On('open-user-form')]
    public function open(?int $userId = null): void
    {
        $this->reset(['userId', 'name', 'email', 'password', 'password_confirmation', 'role_id', 'permissions']);
        $this->resetErrorBag();

        if ($userId !== null) {
            $user = User::findOrFail($userId);

            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role_id = (string) $user->role_id;
            $this->permissions = $user->permissions ?? [];
        } else {
            $defaultRole = Role::where('slug', 'business_analyst')->first();

            if ($defaultRole) {
                $this->role_id = (string) $defaultRole->id;
                $this->permissions = $defaultRole->permissions ?? [];
            }
        }

        $this->dispatch('show-bs-modal', id: 'user-form-modal');
    }

    public function save(): void
    {
        $rules = $this->userRules($this->userId);

        if (! $this->isEditing) {
            $rules['password'] = $this->newUserPasswordRules();
        }

        $validated = $this->validate($rules);

        if ($this->isEditing) {
            $user = User::with('role')->findOrFail($this->userId);
            $newRole = Role::findOrFail($validated['role_id']);

            if ($user->role?->is_founder
                && ! $newRole->is_founder
                && User::whereHas('role', fn ($query) => $query->where('is_founder', true))->count() <= 1) {
                throw ValidationException::withMessages(['role_id' => 'At least one Founder must remain.']);
            }

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $validated['role_id'],
                'permissions' => $newRole->is_founder ? [] : $validated['permissions'],
            ]);
        } else {
            $role = Role::findOrFail($validated['role_id']);

            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
                'permissions' => $role->is_founder ? [] : $validated['permissions'],
            ]);
        }

        $this->dispatch('hide-bs-modal', id: 'user-form-modal');

        $this->dispatch('user-saved');

        $this->dispatch('toast', variant: 'success', text: $this->isEditing ? 'User updated.' : 'User created.');
    }
}; ?>

<div wire:ignore.self class="modal fade" id="user-form-modal" tabindex="-1" aria-labelledby="user-form-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form wire:submit="save">
                <div class="modal-header">
                    <h5 class="modal-title" id="user-form-modal-title">{{ $this->isEditing ? __('Edit user') : __('Add user') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="user-name">{{ __('Name') }}</label>
                            <input wire:model="name" type="text" id="user-name" class="form-control" required autocomplete="name">
                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="user-email">{{ __('Email') }}</label>
                            <input wire:model="email" type="email" id="user-email" class="form-control" required autocomplete="email">
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    @unless ($this->isEditing)
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="user-password">{{ __('Password') }}</label>
                                <input wire:model="password" type="password" id="user-password" class="form-control" required autocomplete="new-password">
                                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="user-password-confirmation">{{ __('Confirm password') }}</label>
                                <input wire:model="password_confirmation" type="password" id="user-password-confirmation" class="form-control" required autocomplete="new-password">
                            </div>
                        </div>
                    @endunless

                    <div class="form-group">
                        <label for="user-role">{{ __('Role') }}</label>
                        <select wire:model.live="role_id" id="user-role" class="form-select">
                            @foreach ($this->roles as $roleOption)
                                <option value="{{ $roleOption->id }}">{{ $roleOption->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('Permissions') }}</label>
                        @if ($this->selectedRoleIsFounder)
                            <p class="text-muted small">{{ __('Founders always have full access to every module.') }}</p>
                        @else
                            <div class="row">
                                @foreach (Permission::cases() as $permissionOption)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input
                                                wire:model="permissions"
                                                class="form-check-input"
                                                type="checkbox"
                                                value="{{ $permissionOption->value }}"
                                                id="permission-{{ $permissionOption->value }}"
                                            >
                                            <label class="form-check-label" for="permission-{{ $permissionOption->value }}">
                                                {{ $permissionOption->label() }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        {{ $this->isEditing ? __('Save changes') : __('Create user') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
