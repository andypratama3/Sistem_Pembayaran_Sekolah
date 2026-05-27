<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Http\Requests\Dashboard\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class RoleController extends ResourceController
{
    protected static string $permissionResource = 'roles';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        if ($request->ajax()) {
            $roles = Role::query();

            return $this->buildDataTable($roles, ['name', 'guard_name'])
                ->addColumn('checkbox', function ($role) {
                    return '
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_'.$role->id.'">
                                <label class="custom-control-label" for="checkBox_'.$role->id.'"></label>
                            </div>
                        </div>';
                })
                ->addColumn('action', function ($role) {
                    $viewBtn = auth()->user()->can('view', $role) ?
                        '<a href="'.route('dashboard.settings.roles.show', $role->id, false).'" class="avatar-text avatar-md">
                            <i class="feather feather-eye"></i>
                        </a>' : '';

                    $editBtn = auth()->user()->can('update', $role) ?
                        '<li>
                            <a class="dropdown-item" href="'.route('dashboard.settings.roles.edit', $role->id, false).'">
                                <i class="feather feather-edit-3 me-3"></i>
                                <span>Edit</span>
                            </a>
                        </li>' : '';

                    $deleteBtn = auth()->user()->can('delete', $role) ?
                        '<li>
                            <a class="dropdown-item delete-btn" href="javascript:void(0)"
                                data-id="'.$role->id.'"
                                data-url="'.route('dashboard.settings.roles.destroy', $role->id, false).'">
                                <i class="feather feather-trash-2 me-3"></i>
                                <span>Delete</span>
                            </a>
                        </li>' : '';

                    $dropdown = ($editBtn || $deleteBtn) ? '
                        <div class="dropdown">
                            <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown">
                                <i class="feather feather-more-horizontal"></i>
                            </a>
                            <ul class="dropdown-menu">
                                '.$editBtn.'
                                '.($editBtn && $deleteBtn ? '<li class="dropdown-divider"></li>' : '').'
                                '.$deleteBtn.'
                            </ul>
                        </div>' : '';

                    return '<div class="gap-2 hstack justify-content-end">'
                         .$viewBtn
                         .$dropdown
                         .'</div>';
                })
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }

        return view('dashboard.roles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Role::class);

        $role = null;
        $permissions = Permission::orderBy('name')->get();

        return view('dashboard.roles.create', compact('role', 'permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        $this->log(
            'create',
            'Menambahkan data role',
            $role,
            [],
            $role->toArray()
        );

        return Redirect::route('dashboard.settings.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $role = Role::find($id);
        $this->authorize('view', $role);

        return view('dashboard.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $role = Role::find($id);
        $this->authorize('update', $role);

        $permissions = Permission::orderBy('name')->get();

        return view('dashboard.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, $id): RedirectResponse
    {
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);

        $validated = $request->validated();
        $oldData = $role->toArray();

        $role->update([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        $this->log(
            'update',
            'Memperbarui data role',
            $role,
            $oldData,
            $role->toArray()
        );

        return Redirect::route('dashboard.settings.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $this->authorize('delete', $role);

        $oldData = $role->toArray();
        $action = $role->delete();

        if ($action) {
            $this->log(
                'delete',
                'Menghapus data role',
                $role,
                $oldData,
                []
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Role berhasil dihapus.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal menghapus role.',
        ], 500);
    }

    /**
     * Remove multiple records from storage.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = json_decode($request->input('ids'), true);

        if (! empty($ids)) {
            Role::whereIn('id', $ids)->delete();
        }

        return Redirect::route('dashboard.settings.roles.index')
            ->with('success', count($ids).' Role records deleted successfully.');
    }
}
