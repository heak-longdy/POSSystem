<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CheckAdminPermission
{
    public function handle(Request $request, Closure $next)
    {
        $permissions = AdminPermissions::forRequest($request);
        if ($permissions && !$request->user()->canAny($permissions)) {
            throw UnauthorizedException::forPermissions($permissions);
        }

        // User editing must not provide a way around the super-admin bypass.
        if ($request->routeIs('admin-user-*') && $request->user()->role !== 'super_admin') {
            $ids = array_filter([$request->route('id'), $request->input('id')], fn ($id) => is_scalar($id) && $id !== '');
            if (($ids && User::withTrashed()->whereKey($ids)->where('role', 'super_admin')->exists())
                || $request->input('role') === 'super_admin') {
                throw UnauthorizedException::forPermissions(['user-update']);
            }
        }

        return $next($request);
    }
}
