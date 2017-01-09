<?php

namespace DefrostedTuna\Alnus\Models;

use DefrostedTuna\Alnus\Exceptions\InvalidInstanceException;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function users()
    {
        return $this->belongsToMany(config('alnus.models.user', \App\Models\User::class));
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

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

    public function syncPermissions(array $permissions)
    {
        $thesePermissions = [];

        foreach ($permissions as $permission) {
            $thesePermissions[] = $this->parsePermissionId($permission);
        }

        return $this->permissions()->sync($thesePermissions);
    }

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

    public function revokeAllPermissions()
    {
        return $this->permissions()->detach();
    }

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