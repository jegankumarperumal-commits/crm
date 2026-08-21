<?php

namespace App\Concerns;

use App\Enums\Permission;
use App\Models\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait RoleValidationRules
{
    /**
     * Get the validation rules used to create or edit a role.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function roleRules(?int $ignoreRoleId = null): array
    {
        $uniqueName = Rule::unique(Role::class);

        if ($ignoreRoleId !== null) {
            $uniqueName = $uniqueName->ignore($ignoreRoleId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'permissions' => ['array'],
            'permissions.*' => [Rule::enum(Permission::class)],
        ];
    }
}
