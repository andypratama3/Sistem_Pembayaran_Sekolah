<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Http\Requests\Dashboard\UserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserController extends ResourceController
{
    protected static string $permissionResource = 'users';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        if ($request->ajax()) {
            $users = User::query();

            if ($request->filled('role_id')) {
                $users->whereHas('roles', function ($query) use ($request) {
                    $query->where('id', $request->input('role_id'));
                });
            }

            return DataTables::of($users)
                ->filter(function ($query) {
                    $search = request('search.value', '');
                    if (empty($search)) {
                        return;
                    }
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhereHas('roles', fn ($r) => $r->where('name', 'like', "%{$search}%"));
                    });
                }, true)
                ->addColumn('checkbox', function ($user) {
                    return '
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_'.$user->id.'">
                                <label class="custom-control-label" for="checkBox_'.$user->id.'"></label>
                            </div>
                        </div>';
                })
                ->addColumn('status', function ($user) {
                    return '
                    <div class="m-2 form-group">
                        <select class="form-control form-control-sm change-status"
                            data-id="'.$user->id.'"
                            data-select2-selector="status">
                            <option value="1" '.($user->is_active ? 'selected' : '').'
                                data-bg="bg-success">Aktif</option>

                            <option value="0" '.(! $user->is_active ? 'selected' : '').'
                                data-bg="bg-danger">Tidak Aktif</option>

                        </select>
                    </div>';
                })
                ->addColumn('action', function ($user) {
                    $viewBtn = auth()->user()->can('view', $user) ?
                        '<a href="'.route('dashboard.settings.users.show', $user->id).'" class="avatar-text avatar-md">
                            <i class="feather feather-eye"></i>
                        </a>' : '';

                    $editBtn = auth()->user()->can('update', $user) ?
                        '<li>
                            <a class="dropdown-item" href="'.route('dashboard.settings.users.edit', $user->id).'">
                                <i class="feather feather-edit-3 me-3"></i>
                                <span>Edit</span>
                            </a>
                        </li>' : '';

                    $deleteBtn = auth()->user()->can('delete', $user) ?
                        '<li>
                            <a class="dropdown-item delete-btn" href="javascript:void(0)"
                                data-id="'.$user->id.'"
                                data-url="'.route('dashboard.settings.users.destroy', $user->id).'">
                                <i class="feather feather-trash-2 me-3"></i>
                                <span>Delete</span>
                            </a>
                        </li>' : '';

                    $dropdown = ($editBtn || $deleteBtn) ? '
                        <div class="dropdown">
                            <a href="javascript:void(0)" class="avatar-text avatar-md"
                                data-bs-toggle="dropdown" data-bs-offset="0,21">
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
                ->rawColumns(['checkbox', 'status', 'action'])
                ->make(true);
        }

        $roles = Role::where('name', '!=', 'superadmin')->orderBy('name')->get();

        return view('dashboard.users.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $user = null;

        return view('dashboard.users.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($data);

        $this->log(
            'create',
            'Menambahkan user',
            $user,
            $user->getOriginal(),
            $user->getChanges()
        );

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully.',
                'data' => $user,
            ], 201);
        }

        return Redirect::route('dashboard.settings.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $userRecord): View
    {
        $this->authorize('view', $userRecord);

        return view('dashboard.users.show', ['user' => $userRecord]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $userRecord): View
    {
        $this->authorize('update', $userRecord);

        return view('dashboard.users.edit', ['user' => $userRecord]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $userRecord): RedirectResponse
    {
        $this->authorize('update', $userRecord);

        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($userRecord->avatar) {
                \Storage::disk('public')->delete($userRecord->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $userRecord->update($data);

        $this->log(
            'update',
            'Memperbarui user',
            $userRecord,
            $userRecord->getOriginal(),
            $userRecord->getChanges()
        );

        return Redirect::route('dashboard.settings.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $userRecord): JsonResponse
    {
        $this->authorize('delete', $userRecord);

        $oldData = $userRecord->toArray();
        $action = $userRecord->delete();

        if ($action) {
            $this->log(
                'delete',
                'Menghapus user',
                $userRecord,
                $oldData,
                []
            );

            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil dihapus.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal menghapus user.',
        ], 500);
    }

    public function updateStatus(Request $request, User $userRecord)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $oldStatus = $userRecord->is_active;
        $userRecord->is_active = $request->status;
        $userRecord->save();

        $this->log(
            'change_status',
            'Mengubah status user',
            $userRecord,
            ['is_active' => $oldStatus],
            ['is_active' => $request->status]
        );

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui',
        ]);
    }

    /**
     * Remove multiple records from storage.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = json_decode($request->input('ids'), true);

        if (! empty($ids)) {
            User::whereIn('id', $ids)->delete();
        }

        return Redirect::route('dashboard.settings.users.index')
            ->with('success', count($ids).' User records deleted successfully.');
    }
}
