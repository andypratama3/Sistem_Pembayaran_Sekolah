@extends('layouts.app')
@section('title', 'Permission Detail — ' . $permission->name)

@section('page-header')
    <x-page-header>
        {{-- BreadCrumb --}}
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.permissions.show', $permission) }}
        </x-slot:left>

        {{-- Actions --}}
        <x-slot:actions>
            <a href="{{ route('dashboard.settings.permissions.index') }}" class="btn btn-light">
                <i class="feather feather-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('dashboard.settings.permissions.edit', $permission->id) }}" class="btn btn-primary">
                <i class="feather feather-edit-3 me-1"></i> Edit
            </a>
        </x-slot:actions>
    </x-page-header>

@endsection

@section('content')
    <div class="col-xl-12">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Informasi Permission</h5>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Nama Permission</label>
                            <div class="fs-16 text-dark fw-bold font-monospace">{{ $permission->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Guard Name</label>
                            <div class="badge bg-soft-info text-info fs-12">{{ $permission->guard_name }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
