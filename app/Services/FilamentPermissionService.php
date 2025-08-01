<?php

namespace App\Services;

use App\Models\Auth;

class FilamentPermissionService
{
    /**
     * Check if the current user can view any records
     */
    public static function canViewAny(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) {
            return false;
        }

        $authModel = Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }

    /**
     * Check if the current user can create records
     */
    public static function canCreate(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) {
            return false;
        }

        $authModel = Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }

    /**
     * Check if the current user can edit records
     */
    public static function canEdit($record = null): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) {
            return false;
        }

        $authModel = Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }

    /**
     * Check if the current user can delete records
     */
    public static function canDelete($record = null): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) {
            return false;
        }

        $authModel = Auth::where('pn', $auth['pn'])->first();
        return $authModel && $authModel->hasRole('superadmin');
    }

    /**
     * Check if the current user is superadmin
     */
    public static function isSuperAdmin(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) {
            return false;
        }

        $authModel = Auth::where('pn', $auth['pn'])->first();
        return $authModel && $authModel->hasRole('superadmin');
    }

    /**
     * Check if the current user is admin or superadmin
     */
    public static function isAdmin(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) {
            return false;
        }

        $authModel = Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }
}
