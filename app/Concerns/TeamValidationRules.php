<?php

namespace App\Concerns;

use App\Enums\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

trait TeamValidationRules
{
    /**
     * Get the validation rules shared by creating and editing a team member.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function userRules(?int $ignoreUserId = null): array
    {
        $uniqueEmail = Rule::unique(User::class);

        if ($ignoreUserId !== null) {
            $uniqueEmail = $uniqueEmail->ignore($ignoreUserId);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $uniqueEmail],
            'role_id' => $this->roleIdRules(),
            'permissions' => ['array'],
            'permissions.*' => [Rule::enum(Permission::class)],
        ];
    }

    /**
     * Get the validation rules for a new team member's password.
     *
     * @return array<int, ValidationRule|Password|string>
     */
    protected function newUserPasswordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
    }

    /**
     * Get the validation rules used to validate a team member's role.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function roleIdRules(): array
    {
        return ['required', Rule::exists(Role::class, 'id')];
    }
}
