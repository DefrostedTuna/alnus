<?php

namespace DefrostedTuna\Alnus\Traits;

use DefrostedTuna\Alnus\Exceptions\InvalidInstanceException;
use DefrostedTuna\Alnus\Models\Role;

trait RolesAndPermissions
{
    /*
     * This trait is meant to go onto the User model.
     */

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function isA($role)
    {
        return $this->hasRole($role);
    }

    public function isAn($role)
    {
        return $this->hasRole($role);
    }

    public function hasRole($role)
    {
        // Check for an array of roles and pass them back through.
        if (is_array($role)) {
            foreach ($role as $arg) {
                if ($this->hasRole($arg)) {
                    return true;
                }
            }
            return false;
        }

        // Check string against name.
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        // Check instance of model.
        if ($role instanceof Role) {
            return $this->roles->contains('id', $role->id);
        }

        return (bool) $role->intersect($this->roles)->count();
    }

    public function isAbleTo($permissions)
    {
        // Check if the argument is an array and pass each one through as a string.
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                $hasPermission = $this->isAbleTo($permission);
                if ($hasPermission) {
                    return true;
                }
            }
            return false;
        }

        // Check if user has access to a role that gives them permission.
        if (is_string($permissions)) {
            foreach ($this->roles->load('permissions') as $role) {
                foreach ($role->permissions as $perm) {
                    if ($perm->name == $permissions) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function assignRole($role)
    {
        // Check if there are multiple roles passed through.
        if (is_array($role)) {
            foreach ($role as $arg) {
                $this->assignRole($arg);
            }
            return true;
        }

        // Find record and send back through to be attached.
        if (is_string($role)) {
            $record = Role::where('name', $role)->firstOrFail();
            return $this->assignRole($record);
        }

        // Verify and attach the role.
        if ($role instanceof Role) {
            $this->roles()->attach($role);
            return true;
        } else {
            throw new InvalidInstanceException(
                'The argument is not an instance of the correct model.'
            );
        }
    }

    public function syncRoles(array $roles)
    {
        $theseRoles = [];

        foreach ($roles as $role) {
            $theseRoles[] = $this->parseRoleId($role);
        }

        return $this->roles()->sync($theseRoles);
    }

    public function revokeRole($role)
    {
        // Check if there are multiple roles passed through.
        if (is_array($role)) {
            foreach ($role as $arg) {
                $this->revokeRole($arg);
            }
            return true;
        }

        // Find record and send back through to be attached.
        if (is_string($role)) {
            $record = $this->roles->where('name', $role)->firstOrFail();
            return $this->revokeRole($record);
        }

        // Verify and detach the role.
        if ($role instanceof Role) {
            return $this->roles()->detach($role);
        } else {
            throw new InvalidInstanceException(
                'The argument is not an instance of the correct model.'
            );
        }
    }

    public function revokeAllRoles()
    {
        return $this->roles()->detach();
    }

    protected function parseRoleId($role)
    {
        // Check if an id has already been passed.
        if (is_numeric($role)) {
            return $role;
        }

        // Find the role if the name has been given as a string.
        if (is_string($role)) {
            $record = Role::where('name', $role)->firstOrFail();
            return $record->id;
        }

        // Verify an instance and return the associated id.
        if (is_object($role) && $role instanceof Role) {
            return $role->id;
        } else {
            throw new InvalidInstanceException(
                'The argument is not an instance of the correct model.'
            );
        }
    }

}