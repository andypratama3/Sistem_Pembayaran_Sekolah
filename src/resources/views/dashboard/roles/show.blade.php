@extends('layouts.app')
@section('title', 'Role Detail — ' . $role->name)

@section('page-header')
    <x-page-header>
        {{-- BreadCrumb --}}
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.roles.show', $role) }}
        </x-slot:left>

        {{-- Actions --}}
        <x-slot:actions>
            <a href="{{ route('dashboard.settings.roles.index') }}" class="btn btn-light">
                <i class="feather feather-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('dashboard.settings.roles.edit', $role->id) }}" class="btn btn-primary">
                <i class="feather feather-edit-3 me-1"></i> Edit
            </a>
        </x-slot:actions>
    </x-page-header>

@endsection

@section('content')
    <div class="col-xl-12">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Informasi Role</h5>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Nama Role</label>
                            <div class="fs-18 text-primary fw-bold">{{ $role->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Guard Name</label>
                            <div class="badge bg-soft-secondary text-secondary fs-12">{{ $role->guard_name }}</div>
                        </div>
                    </div>
                </div>

                {{-- Permissions List --}}
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">Permissions ({{ $role->permissions->count() }})</h6>
                    @if($role->permissions->count() > 0)
                        <div class="row">
                            @foreach($role->permissions->sortBy('name') as $permission)
                                <div class="col-md-4 col-sm-6 mb-2">
                                    <span class="badge bg-soft-primary text-primary fs-12">{{ $permission->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="feather icon-info me-2"></i>
                            Role ini belum memiliki permission.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
