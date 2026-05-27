<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Auditable;
use App\Traits\HasDataTable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

abstract class ResourceController implements HasMiddleware
{
    use ApiResponse;
    use Auditable;
    use AuthorizesRequests;
    use DispatchesJobs;
    use HasDataTable;
    use ValidatesRequests;

    protected static string $permissionResource = '';

    public static function middleware(): array
    {
        $resource = static::$permissionResource;

        if (! $resource) {
            return [];
        }

        return [
            new Middleware(PermissionMiddleware::using("view-{$resource}"), only: ['index', 'show']),
            new Middleware(PermissionMiddleware::using("create-{$resource}"), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using("edit-{$resource}"), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using("delete-{$resource}"), only: ['destroy']),
        ];
    }
}
