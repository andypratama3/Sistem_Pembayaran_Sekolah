<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Models\AuditLog;
use Yajra\DataTables\Facades\DataTables;

class AuditLogController extends ResourceController
{
    // Removed permissionResource to bypass middleware - authorization handled via authorize() in methods

    public function index()
    {
        $this->authorize('viewAny', AuditLog::class);

        return view('dashboard.audit-log.index');
    }

    public function datatable()
    {
        $query = AuditLog::with('user')->latest('created_at');

        if (request()->filled('date_from') && request()->filled('date_to')) {
            $query->whereBetween('created_at', [request('date_from').' 00:00:00', request('date_to').' 23:59:59']);
        }

        return DataTables::of($query)
            ->filter(function ($query) {
                $search = request('search.value', '');
                if (empty($search)) {
                    return;
                }
                $query->where(function ($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('model_type', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
            }, true)
            ->addColumn('checkbox', function ($log) {
                return '<div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input checkbox" id="chk-'.$log->id.'">
                            <label class="custom-control-label" for="chk-'.$log->id.'">
                                <span data-id="'.$log->id.'"></span>
                            </label>
                        </div>';
            })

            ->addColumn('user_name', function ($log) {
                return $log->user
                    ? '<span class="fw-medium">'.($log->user->name).'</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary">System</span>';
            })

            ->addColumn('desc', function ($log) {
                $map = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', 'login' => 'info', 'logout' => 'secondary'];
                $c = $map[strtolower($log->action)] ?? 'primary';

                return '<span class="badge bg-'.$c.'-subtle text-'.$c.'">'.e(ucfirst($log->action)).'</span>';
            })

            ->addColumn('model_type', function ($log) {
                if (! $log->model_type) {
                    return '<span class="text-muted">—</span>';
                }
                $parts = explode('\\', $log->model_type);

                return '<code class="fs-11">'.e(end($parts)).'</code>';
            })

            ->addColumn('model_id', function ($log) {
                return $log->model_id
                    ? '<span class="badge bg-secondary-subtle text-secondary">#'.$log->model_id.'</span>'
                    : '<span class="text-muted">—</span>';
            })

            ->addColumn('description', function ($log) {
                return $log->description ? e($log->description) : '<span class="text-muted">—</span>';
            })

            ->addColumn('ip_address', function ($log) {
                return $log->ip_address
                    ? '<code class="fs-11">'.e($log->ip_address).'</code>'
                    : '<span class="text-muted">—</span>';
            })

            ->editColumn('created_at', function ($log) {
                return $log->created_at
                    ? '<span title="'.$log->created_at->format('d M Y H:i:s').'">'
                      .$log->created_at->diffForHumans().'</span>'
                    : '—';
            })

            // Tombol detail — buka modal, BUKAN redirect ke halaman baru
            ->addColumn('action', function ($log) {
                return '<button type="button"
                                class="btn btn-sm btn-outline-primary btn-audit-detail"
                                data-id="'.$log->id.'">
                            <i class="feather-eye me-1"></i> Detail
                        </button>';
            })

            ->rawColumns(['checkbox', 'user_name', 'action', 'model_type', 'model_id', 'description', 'ip_address', 'created_at', 'desc'])
            ->make(true);
    }

    /**
     * Dipanggil via $.getJSON dari modal — kembalikan JSON, bukan view.
     */
    public function show($id)
    {
        $auditLog = AuditLog::with('user')->find($id);
        $this->authorize('view', $auditLog);
        $parts = explode('\\', $auditLog->model_type ?? '');

        return response()->json([
            'id' => $auditLog->id,
            'user_name' => $auditLog->user?->name,
            'action' => $auditLog->action,
            'model_type' => $auditLog->model_type,
            'model_type_short' => end($parts) ?: null,
            'model_id' => $auditLog->model_id,
            'description' => $auditLog->description,
            'old_values' => $auditLog->old_values ?? [],
            'new_values' => $auditLog->new_values ?? [],
            'ip_address' => $auditLog->ip_address,
            'user_agent' => $auditLog->user_agent,
            'created_at_full' => $auditLog->created_at?->format('d M Y H:i:s'),
        ]);
    }
}
