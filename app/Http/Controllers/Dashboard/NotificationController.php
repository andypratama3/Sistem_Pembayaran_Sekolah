<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Models\Notification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends ResourceController
{
    protected static string $permissionResource = 'notifications';

    use ApiResponse;

    public function list_notification(Request $request)
    {
        if ($request->ajax()) {
            $query = Notification::query()->with('user')
                ->where('user_id', auth()->id())
                ->latest();

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                if ($request->status === 'read') {
                    $query->whereNotNull('read_at');
                } elseif ($request->status === 'unread') {
                    $query->whereNull('read_at');
                }
            }

            return DataTables::of($query)
                ->filter(function ($query) {
                    $search = request('search.value', '');
                    if (empty($search)) {
                        return;
                    }
                    $query->where(function ($builder) use ($search) {
                        $builder->where('title', 'like', '%'.$search.'%')
                            ->orWhere('message', 'like', '%'.$search.'%')
                            ->orWhere('type', 'like', '%'.$search.'%');
                    });
                }, true)

                ->addColumn('checkbox', function ($n) {
                    return '<div class="custom-control custom-checkbox ms-1">'
                         .'<input type="checkbox" class="custom-control-input checkbox" id="chk_'.$n->id.'">'
                         .'<label class="custom-control-label" for="chk_'.$n->id.'">'
                         .'<span data-id="'.$n->id.'"></span></label></div>';
                })

                // key 'user' match <th>User</th>
                ->addColumn('user', function ($n) {
                    return e($n->user->name ?? '-');
                })

                // key 'type' match <th>Type</th>
                ->editColumn('type', function ($n) {
                    $colors = ['info' => 'primary', 'success' => 'success', 'warning' => 'warning', 'error' => 'danger'];
                    $c = $colors[$n->type ?? ''] ?? 'secondary';

                    return '<span class="badge bg-soft-'.$c.' text-'.$c.'">'.e(ucfirst($n->type ?? 'info')).'</span>';
                })

                // key 'title' match <th>Title</th>
                ->editColumn('title', function ($n) {
                    return e($n->title ?? '-');
                })

                // key 'message' match <th>Message</th>
                ->editColumn('message', function ($n) {
                    $msg = $n->message ?? '-';

                    return strlen($msg) > 60
                        ? '<span title="'.e($msg).'">'.e(substr($msg, 0, 60)).'...</span>'
                        : e($msg);
                })

                // key 'date' match <th>Date</th>
                ->editColumn('created_at', function ($n) {
                    return $n->created_at
                        ? '<span title="'.$n->created_at->format('d M Y H:i').'">'.$n->created_at->diffForHumans().'</span>'
                        : '-';
                })

                // key 'status' match <th>Status</th>
                ->editColumn('status', function ($n) {
                    return $n->read_at
                        ? '<span class="badge bg-soft-success text-success">Read</span>'
                        : '<span class="badge bg-soft-danger text-danger">Unread</span>';
                })

                // action_btn: di-skip initDataTable karena <th class="text-end">
                ->addColumn('action', function ($n) {
                    $mark = $n->read_at ? '' :
                        '<a href="javascript:void(0)" class="avatar-text avatar-md mark-read" data-id="'.$n->id.'" title="Tandai dibaca">'
                        .'<i class="feather feather-check"></i></a>';

                    return '<div class="hstack gap-2 justify-content-end">'
                         .$mark
                         .'<a href="javascript:void(0)" class="avatar-text avatar-md text-danger delete-btn" data-id="'.$n->id.'"'
                         .' data-url="'.route('dashboard.notifications.destroy', $n->id).'">'
                         .'<i class="feather feather-trash-2"></i></a></div>';
                })

                ->rawColumns(['checkbox', 'type', 'message', 'created_at', 'status', 'action'])
                ->make(true);
        }

        return view('dashboard.notifications');
    }

    public function index()
    {
        return auth()->user()->notifications()->latest()->limit(10)->get();
    }

    public function unreadCount()
    {
        return auth()->user()->notifications()->whereNull('read_at')->count();
    }

    public function read(Notification $notificationRecord)
    {
        if ($notificationRecord->user_id !== auth()->id()) {
            abort(403);
        }

        $notificationRecord->update(['read_at' => now()]);

        return $this->success(null, 'Notifikasi ditandai dibaca.');
    }

    public function readAll()
    {
        auth()->user()->notifications()->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(null, 'Semua notifikasi ditandai dibaca.');
    }

    public function destroy(Notification $notificationRecord)
    {
        if ($notificationRecord->user_id !== auth()->id()) {
            abort(403);
        }

        $notificationRecord->delete();

        return $this->success(null, 'Notifikasi berhasil dihapus.');
    }
}
