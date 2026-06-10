<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; 


#[Fillable(['name', 'email', 'password', 'expires_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles {
        hasRole as hasRoleViaSpatie;
        hasPermissionTo as hasPermissionToViaSpatie;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'expires_at'        => 'datetime',
        ];
    }

    public function trackAdmins(): HasMany
    {
        return $this->hasMany(TrackAdmin::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function instructor(): HasOne
    {
        return $this->hasOne(Instructor::class);
    }

    /**
     * Determine if this account has passed its expiry date.
     */
    public function isExpired(): bool
    {
        if (is_null($this->expires_at)) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Determine if the model has (one of) the given role(s), with database column fallback.
     */
    public function hasRole($roles, ?string $guard = null): bool
    {
        if ($this->hasRoleViaSpatie($roles, $guard)) {
            return true;
        }

        $roleColumn = $this->role ?? null;
        if (!$roleColumn) {
            return false;
        }

        if (is_string($roles)) {
            if (str_contains($roles, '|')) {
                return in_array($roleColumn, explode('|', $roles));
            }
            return $roles === $roleColumn;
        }

        if (is_array($roles)) {
            return in_array($roleColumn, $roles);
        }

        if ($roles instanceof \Illuminate\Support\Collection) {
            return $roles->contains($roleColumn);
        }

        if ($roles instanceof \Spatie\Permission\Models\Role) {
            return $roles->name === $roleColumn;
        }

        if ($roles instanceof \BackedEnum) {
            return $roles->value === $roleColumn;
        }

        return false;
    }

    /**
     * Determine if the model has the given permission, with database column fallback.
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->hasPermissionToViaSpatie($permission, $guardName)) {
            return true;
        }

        $roleName = $this->roles->first()?->name ?? $this->role;
        if ($roleName) {
            try {
                $role = \Spatie\Permission\Models\Role::findByName($roleName, $guardName ?? $this->getDefaultGuardName());
                if ($role) {
                    if (is_string($permission)) {
                        return $role->hasPermissionTo($permission);
                    }
                    if (is_int($permission)) {
                        return $role->permissions->contains('id', $permission);
                    }
                    if ($permission instanceof \Spatie\Permission\Contracts\Permission) {
                        return $role->permissions->contains('id', $permission->id);
                    }
                }
            } catch (\Exception $e) {
                // Role not found
            }
        }

        return false;
    }

    /**
     * Get the names of the roles associated with the model, with database column fallback.
     */
    public function getRoleNames(): \Illuminate\Support\Collection
    {
        $roleNames = $this->roles->pluck('name');
        if ($roleNames->isEmpty() && !empty($this->role)) {
            return collect([$this->role]);
        }
        return $roleNames;
    }
}
