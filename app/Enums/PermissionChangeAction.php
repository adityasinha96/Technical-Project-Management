<?php

namespace App\Enums;

enum PermissionChangeAction: string
{
    case RolesSynchronized = 'roles_synchronized';
    case RoleAssigned = 'role_assigned';
    case RoleRemoved = 'role_removed';
    case RolePermissionsSynchronized = 'role_permissions_synchronized';
    case DirectPermissionsSynchronized = 'direct_permissions_synchronized';
    case PermissionCreated = 'permission_created';
    case RoleCreated = 'role_created';

    public function label(): string
    {
        return str($this->value)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}