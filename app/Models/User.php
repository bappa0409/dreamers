<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'language',
        'is_active',
        'role_id',
        'password_setup_token',
'password_setup_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
         'password_setup_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'password_setup_expires_at'=>'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }


    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles'
        )->withTimestamps();
    }

    public function role(): BelongsTo
{
    return $this->belongsTo(Role::class);
}

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function hasRole(string $role): bool
{
    return in_array(
        $role,
        $this->cachedRoleNames(),
        true
    );
}


    public function hasAnyRole(array $roles): bool
{
    return !empty(
        array_intersect(
            $roles,
            $this->cachedRoleNames()
        )
    );
}


    public function assignRole(Role|string|int $role): void
{
    $roleId = $this->resolveRoleId($role);

    $this->roles()
        ->syncWithoutDetaching([
            $roleId,
        ]);

    $this->unsetRelation('roles');

    $this->forgetAuthorizationCache();
}


    public function removeRole(Role|string|int $role): void
{
    $roleId = $this->resolveRoleId($role);

    $this->roles()
        ->detach($roleId);

    $this->unsetRelation('roles');

    $this->forgetAuthorizationCache();
}


    public function syncRoles(array $roles): void
{
    $roleIds = collect($roles)
        ->map(
            fn ($role) =>
                $this->resolveRoleId($role)
        )
        ->unique()
        ->values()
        ->all();

    $this->roles()
        ->sync($roleIds);

    $this->unsetRelation('roles');

    $this->forgetAuthorizationCache();
}


    /*
    |--------------------------------------------------------------------------
    | Permission Helpers
    |--------------------------------------------------------------------------
    */

    public function isSystemAnalyst(): bool
    {
        return $this->hasRole(
            'system_analyst'
        );
    }


    public function hasPermission(string $permission): bool
{
    if ($this->isSystemAnalyst()) {
        return true;
    }

    return in_array(
        $permission,
        $this->cachedPermissionNames(),
        true
    );
}


    public function hasAnyPermission(array $permissions): bool
{
    if ($this->isSystemAnalyst()) {
        return true;
    }

    return !empty(
        array_intersect(
            $permissions,
            $this->cachedPermissionNames()
        )
    );
}


    public function hasAllPermissions(array $permissions): bool
{
    if ($this->isSystemAnalyst()) {
        return true;
    }

    $userPermissions = $this->cachedPermissionNames();

    foreach ($permissions as $permission) {
        if (!in_array(
            $permission,
            $userPermissions,
            true
        )) {
            return false;
        }
    }

    return true;
}


    /*
    |--------------------------------------------------------------------------
    | Other Relationships
    |--------------------------------------------------------------------------
    */

    public function tellerTransactions()
    {
        return $this->hasMany(
            TellerTransaction::class,
            'teller_id'
        );
    }


    public function tellerClosings()
    {
        return $this->hasMany(
            TellerClosing::class,
            'teller_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Internal Role Resolver
    |--------------------------------------------------------------------------
    */

    protected function resolveRoleId(
        Role|string|int $role
    ): int {

        if ($role instanceof Role) {
            return $role->id;
        }

        if (is_int($role)) {
            return $role;
        }

        return Role::where(
            'name',
            $role
        )->value('id')
            ?? throw new \InvalidArgumentException(
                "Role [{$role}] does not exist."
            );
    }

    /*
|--------------------------------------------------------------------------
| RBAC Cache
|--------------------------------------------------------------------------
*/

public function cachedRoleNames(): array
{
    return Cache::remember(
        $this->roleCacheKey(),
        now()->addMinutes(30),
        function () {
            return $this->roles()
                ->pluck('roles.name')
                ->unique()
                ->values()
                ->all();
        }
    );
}


public function cachedPermissionNames(): array
{
    return Cache::remember(
        $this->permissionCacheKey(),
        now()->addMinutes(30),
        function () {

            return $this->roles()
                ->with('permissions:id,name')
                ->get()
                ->flatMap(
                    fn ($role) =>
                        $role->permissions
                            ->pluck('name')
                )
                ->unique()
                ->values()
                ->all();
        }
    );
}


public function forgetAuthorizationCache(): void
{
    Cache::forget(
        $this->roleCacheKey()
    );

    Cache::forget(
        $this->permissionCacheKey()
    );
}


protected function roleCacheKey(): string
{
    return "rbac:user:{$this->id}:roles";
}


protected function permissionCacheKey(): string
{
    return "rbac:user:{$this->id}:permissions";
}
}