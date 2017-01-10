<?php

namespace DefrostedTuna\Alnus\Models;

use DefrostedTuna\Alnus\Exceptions\InvalidInstanceException;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(config('alnus.models.user', \App\Models\User::class));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * @param $permission
     * @return bool
     * @throws InvalidInstanceException
     */
    public function givePermission($permission)
    {
        // Check if there are multiple permissions passed through.
        if (is_array($permission)) {
            foreach ($permission as $perm) {
                $this->givePermission($perm);
            }
            return true;
        }

        // Find record and send back through to be attached.
        if (is_string($permission)) {
            $record = Permission::where('name', $permission)->firstOrFail();
            return $this->givePermission($record);
        }

        // Verify and attach the permission.
        if ($permission instanceof Permission) {
            $this->permissions()->attach($permission);
            return true;
        } else {
            throw new InvalidInstanceException(
                'The argument is not an instance of the correct model.'
            );
        }
    }

    /**
     * @param array $permissions
     * @return array
     */
    public function syncPermissions(array $permissions)
    {
        $thesePermissions = [];

        foreach ($permissions as $permission) {
            $thesePermissions[] = $this->parsePermissionId($permission);
        }

        return $this->permissions()->sync($thesePermissions);
    }

    /**
     * @param $permission
     * @return bool|int
     * @throws InvalidInstanceException
     */
    public function revokePermission($permission)
    {
        // Check if there are multiple permissions passed through.
        if (is_array($permission)) {
            foreach ($permission as $perm) {
                $this->revokePermission($perm);
            }
            return true;
        }

        // Find record and send back through to be attached.
        if (is_string($permission)) {
            $record = $this->permissions->where('name', $permission)->firstOrFail();
            return $this->revokePermission($record);
        }

        // Verify and detach the permission.
        if ($permission instanceof Permission) {
            return $this->permissions()->detach($permission);
        } else {
            throw new InvalidInstanceException(
                'The argument is not an instance of the correct model.'
            );
        }
    }

    /**
     * @return int
     */
    public function revokeAllPermissions()
    {
        return $this->permissions()->detach();
    }

    /**
     * @param $permission
     * @return mixed
     * @throws InvalidInstanceException
     */
    protected function parsePermissionId($permission)
    {
        // Check if an id has already been passed.
        if (is_numeric($permission)) {
            return $permission;
        }

        // Find the permission if the name has been given as a string.
        if (is_string($permission)) {
            $record = Permission::where('name', $permission)->firstOrFail();
            return $record->id;
        }

        // Verify an instance and return the associated id.
        if (is_object($permission) && $permission instanceof Permission) {
            return $permission->id;
        } else {
            throw new InvalidInstanceException(
                'The argument is not an instance of the correct model.'
            );
        }
    }
}