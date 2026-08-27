<?php

namespace App\Services\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Row-level ACL for "assigned to me only" resources. A user who lacks the
 * given `<module>.<resource>.view_all` permission only sees/acts on rows
 * where the owner column matches them — everyone else (reps without the
 * flag) is scoped down from "every record in the business" to "my own".
 *
 * User::hasPermission() has a built-in admin override, so the Admin role
 * is never restricted by this regardless of whether it holds the
 * view_all permission explicitly.
 */
trait ScopesOwnRecords
{
    protected function applyOwnershipScope($query, string $viewAllPermission, string $ownerColumn = 'assigned_to')
    {
        $user = Auth::user();

        if (!$user->hasPermission($viewAllPermission)) {
            $query->where($ownerColumn, $user->id);
        }

        return $query;
    }
}
