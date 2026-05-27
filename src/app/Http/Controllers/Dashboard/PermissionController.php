<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Http\Requests\Dashboard\PermissionRequest;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends ResourceController
{
    protected static string $permissionResource = 'permissions';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Permission::class);

        if ($request->ajax()) {
            $permissions = Permission::query();

            return DataTables::of($permissions)
                ->filter(function ($query) {
                    $search = request('search.value', '');
                    if (empty($search)) {
                        return;
                    }
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('guard_name', 'like', "%{$search}%");
                    });
                }, true)
                ->addColumn('checkbox', function ($permission) {
                    return '
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_'.$permission->id.'">
                                <label class="custom-control-label" for="checkBox_'.$permission->id.'"></label>
                            </div>
                        </div>';
                })
                ->addColumn('action', function ($permission) {
                    return '
                        <div class="gap-2 hstack justify-content-end">
                            <a href="'.route('dashboard.settings.permissions.show', $permission->id).'" class="avatar-text avatar-md">
                                <i class="feather feather-eye"></i>
                            </a>
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="avatar-text avatar-md"
                                    data-bs-toggle="dropdown" data-bs-offset="0,21">
                                    <i class="feather feather-more-horizontal"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="'.route('dashboard.settings.permissions.edit', $permission->id).'">
                                            <i class="feather feather-edit-3 me-3"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item delete-btn" href="javascript:void(0)"
                                            data-id="'.$permission->id.'"
                                            data-url="'.route('dashboard.settings.permissions.destroy', $permission->id).'">
                                            <i class="feather feather-trash-2 me-3"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>';
                })
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }

        return view('dashboard.permissions.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Permission::class);

        $permission = null;

        return view('dashboard.permissions.create', compact('permission'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PermissionRequest $request): RedirectResponse
    {
        $this->authorize('create', Permission::class);

        $permission = Permission::create($request->validated());

        $this->log(
            'create',
            'Menambahkan data permission',
            $permission,
            [],
            $permission->toArray()
        );

        return Redirect::route('dashboard.settings.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $permission = Permission::find($id);
        $this->authorize('view', $permission);

        return view('dashboard.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $permission = Permission::find($id);
        $this->authorize('update', $permission);

        return view('dashboard.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermissionRequest $request, $id): RedirectResponse
    {
        $permission = Permission::findOrFail($id);
        $this->authorize('update', $permission);

        $oldData = $permission->toArray();
        $permission->update($request->validated());

        $this->log(
            'update',
            'Memperbarui data permission',
            $permission,
            $oldData,
            $permission->toArray()
        );

        return Redirect::route('dashboard.settings.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        $this->authorize('delete', $permission);

        $oldData = $permission->toArray();
        $action = $permission->delete();

        if ($action) {
            $this->log(
                'delete',
                'Menghapus data permission',
                $permission,
                $oldData,
                []
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Permission berhasil dihapus.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal menghapus permission.',
        ], 500);
    }

    /**
     * Remove multiple records from storage.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = json_decode($request->input('ids'), true);

        if (! empty($ids)) {
            Permission::whereIn('id', $ids)->delete();
        }

        return Redirect::route('dashboard.settings.permissions.index')
            ->with('success', count($ids).' Permission records deleted successfully.');
    }
}
