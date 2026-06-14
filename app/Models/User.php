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
        hasAnyRole as hasAnyRoleViaSpatie;
        hasAllRoles as hasAllRolesViaSpatie;
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
     * Determine if the model has any of the given role(s), with database column fallback.
     */
    public function hasAnyRole(...$roles): bool
    {
        if ($this->hasAnyRoleViaSpatie(...$roles)) {
            return true;
        }

        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all of the given role(s), with database column fallback.
     */
    public function hasAllRoles($roles, ?string $guard = null): bool
    {
        if ($this->hasAllRolesViaSpatie($roles, $guard)) {
            return true;
        }

        $roleColumn = $this->role ?? null;
        if (!$roleColumn) {
            return false;
        }

        if ($roles instanceof \BackedEnum) {
            $roles = $roles->value;
        }

        if (is_string($roles) && str_contains($roles, '|')) {
            $roles = explode('|', $roles);
        } elseif (is_string($roles)) {
            $roles = [$roles];
        } elseif ($roles instanceof \Spatie\Permission\Models\Role) {
            $roles = [$roles->name];
        } elseif ($roles instanceof \Illuminate\Support\Collection) {
            $roles = $roles->map(fn($r) => $r instanceof \Spatie\Permission\Models\Role ? $r->name : (is_string($r) || is_int($r) ? $r : ($r->value ?? null)))->filter()->toArray();
        } elseif (is_array($roles)) {
            $roles = array_map(fn($r) => $r instanceof \Spatie\Permission\Models\Role ? $r->name : (is_string($r) || is_int($r) ? $r : ($r->value ?? null)), $roles);
            $roles = array_filter($roles);
        } else {
            return false;
        }

        foreach ($roles as $r) {
            if (!$this->hasRole($r, $guard)) {
                return false;
            }
        }

        return true;
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
