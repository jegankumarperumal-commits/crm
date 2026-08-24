<?php

use App\Concerns\RoleValidationRules;
use App\Enums\Permission;
use App\Models\Role;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    use RoleValidationRules;

    public ?int $roleId = null;

    public string $name = '';

    /** @var array<int, string> */
    public array $permissions = [];

    public bool $isFounderRole = false;

    #[Computed]
    public function isEditing(): bool
    {
        return $this->roleId !== null;
    }

    #[On('open-role-form')]
    public function open(?int $roleId = null): void
    {
        $this->reset(['roleId', 'name', 'permissions', 'isFounderRole']);
        $this->resetErrorBag();

        if ($roleId !== null) {
            $role = Role::findOrFail($roleId);

            $this->roleId = $role->id;
            $this->name = $role->name;
            $this->permissions = $role->permissions ?? [];
            $this->isFounderRole = $role->is_founder;
        }

        $this->dispatch('show-bs-modal', id: 'role-form-modal');
    }

    public function save(): void
    {
        $validated = $this->validate($this->roleRules($this->roleId));

        if ($this->isFounderRole) {
            unset($validated['permissions']);
        }

        if ($this->isEditing) {
            Role::findOrFail($this->roleId)->update($validated);
        } else {
            Role::create($validated);
        }

        $this->dispatch('hide-bs-modal', id: 'role-form-modal');

        $this->dispatch('role-saved');

        $this->dispatch('toast', variant: 'success', text: $this->isEditing ? 'Role updated.' : 'Role created.');
    }
}; ?>

<div wire:ignore.self class="modal fade" id="role-form-modal" tabindex="-1" aria-labelledby="role-form-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form wire:submit="save">
                <div class="modal-header">
                    <h5 class="modal-title" id="role-form-modal-title">{{ $this->isEditing ? __('Edit role') : __('Add role') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="role-name">{{ __('Name') }}</label>
                        <input wire:model="name" type="text" id="role-name" class="form-control" required autofocus>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('Permissions') }}</label>
                        @if ($isFounderRole)
                            <p class="text-muted small">{{ __('The Founder role always has full access to every module.') }}</p>
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
                                                id="role-permission-{{ $permissionOption->value }}"
                                            >
                                            <label class="form-check-label" for="role-permission-{{ $permissionOption->value }}">
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
                        {{ $this->isEditing ? __('Save changes') : __('Create role') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
