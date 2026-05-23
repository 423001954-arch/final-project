<?php

namespace App\Services;

class RoleAccess
{
    private const RANKS = [
        'staff' => 1,
        'manager' => 2,
        'superadmin' => 3,
    ];

    public static function normalize(?string $role): ?string
    {
        if ($role === null || trim($role) === '') {
            return null;
        }

        $role = strtolower(trim($role));

        return $role;
    }

    public static function label(?string $role): string
    {
        return match (self::normalize($role)) {
            'superadmin' => 'SuperAdmin',
            'manager' => 'Manager',
            'staff' => 'Staff',
            default => 'Guest',
        };
    }

    public static function hasAtLeast(?string $role, string $minimumRole): bool
    {
        $role = self::normalize($role);
        $minimumRole = self::normalize($minimumRole);

        if ($role === null || $minimumRole === null) {
            return false;
        }

        return (self::RANKS[$role] ?? 0) >= (self::RANKS[$minimumRole] ?? PHP_INT_MAX);
    }

    public static function canManageUsers(?string $role): bool
    {
        return self::hasAtLeast($role, 'superadmin');
    }

    public static function canOperateSupplyChain(?string $role): bool
    {
        return self::hasAtLeast($role, 'manager');
    }

    public static function canRequestStock(?string $role): bool
    {
        return self::hasAtLeast($role, 'staff');
    }
}
