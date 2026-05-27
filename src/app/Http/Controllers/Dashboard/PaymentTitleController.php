<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Models\PaymentTitle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PaymentTitleController extends ResourceController
{
    protected static string $permissionResource = 'payment_titles';

    public function index(Request $request)
    {
        $this->authorize('viewAny', PaymentTitle::class);

        if ($request->ajax()) {
            $titles = PaymentTitle::latest();

            return DataTables::of($titles)
                ->addColumn('checkbox', function ($title) {
                    return '<input type="checkbox" class="select-row" value="'.$title->id.'">';
                })
                ->addColumn('action', function ($title) {
                    $editBtn = '<a href="'.route('dashboard.payment-titles.edit', $title).'" class="avatar-text avatar-md"><i class="feather feather-edit-3"></i></a>';
                    $deleteBtn = '<a href="javascript:void(0)" class="avatar-text avatar-md text-danger delete-btn" data-url="'.route('dashboard.payment-titles.destroy', $title).'"><i class="feather feather-trash-2"></i></a>';

                    return '<div class="hstack gap-2 justify-content-end">'.$editBtn.$deleteBtn.'</div>';
                })
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }

        return view('dashboard.payment-titles.index');
    }

    public function show(PaymentTitle $paymentTitleRecord)
    {
        $this->authorize('view', $paymentTitleRecord);

        $paymentTitleRecord->loadCount('payments');

        return view('dashboard.payment-titles.show', ['paymentTitle' => $paymentTitleRecord]);
    }

    public function create()
    {
        $this->authorize('create', PaymentTitle::class);

        return view('dashboard.payment-titles.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', PaymentTitle::class);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_titles,code',
        ]);

        $data['slug'] = Str::slug($data['name']);

        PaymentTitle::create($data);

        return redirect()->route('dashboard.payment-titles.index')->with('success', 'Judul pembayaran berhasil ditambahkan.');
    }

    public function edit(PaymentTitle $paymentTitle)
    {
        $this->authorize('update', $paymentTitle);

        return view('dashboard.payment-titles.edit', compact('paymentTitle'));
    }

    public function update(Request $request, PaymentTitle $paymentTitle)
    {
        $this->authorize('update', $paymentTitle);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_titles,code,'.$paymentTitle->id,
        ]);

        $data['slug'] = Str::slug($data['name']);

        $paymentTitle->update($data);

        return redirect()->route('dashboard.payment-titles.index')->with('success', 'Judul pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentTitle $paymentTitle)
    {
        $this->authorize('delete', $paymentTitle);

        $paymentTitle->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['message' => 'Judul pembayaran berhasil dihapus.'], 200);
        }

        return redirect()->route('dashboard.payment-titles.index')->with('success', 'Judul pembayaran berhasil dihapus.');
    }
}
