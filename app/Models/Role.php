<?php

namespace App\Models;

use App\Enums\Permission;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $is_founder
 * @property array<int, string> $permissions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'permissions'])]
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_founder' => 'boolean',
            'permissions' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            if (blank($role->slug)) {
                $role->slug = Str::slug($role->name, '_');
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(Permission $permission): bool
    {
        return $this->is_founder || in_array($permission->value, $this->permissions ?? [], true);
    }
}
