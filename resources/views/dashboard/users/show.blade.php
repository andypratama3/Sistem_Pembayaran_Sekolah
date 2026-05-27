@extends('layouts.app')
@section('title', 'User Detail — ' . $user->name)

@section('page-header')
    <x-page-header>
        {{-- BreadCrumb --}}
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.users.show', $user) }}
        </x-slot:left>

        {{-- Actions --}}
        <x-slot:actions>
            <a href="{{ route('dashboard.settings.users.index') }}" class="btn btn-light">
                <i class="feather feather-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('dashboard.settings.users.edit', $user->id) }}" class="btn btn-primary">
                <i class="feather feather-edit-3 me-1"></i> Edit
            </a>
        </x-slot:actions>
    </x-page-header>

@endsection

@section('content')
    <div class="col-xl-12">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Informasi Akun User</h5>
            </div>

            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center mb-4 mb-md-0">
                        <div class="bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto"
                            style="width: 80px; height: 80px;">
                            <i class="feather feather-user fs-32"></i>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-muted small text-uppercase">Nama Lengkap</label>
                                <div class="fs-18 text-dark fw-bold">{{ $user->name }}</div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-muted small text-uppercase">Alamat Email</label>
                                <div class="fs-16 text-primary fw-semibold"><i
                                        class="feather feather-mail me-2"></i>{{ $user->email }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if (method_exists($user, 'getRoleNames') && $user->getRoleNames()->isNotEmpty())
                    <div class="mt-4 pt-4 border-top">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-3">Roles Assigned</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($user->getRoleNames() as $role)
                                <span class="badge bg-soft-success text-success text-uppercase">{{ $role }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
