@extends('layouts.app')
@section('title', 'Edit Role')

@section('page-header')
    <x-page-header>
        {{-- BreadCrumb --}}
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.roles.edit') }}
        </x-slot:left>

        {{-- Actions --}}
        <x-slot:actions>
        </x-slot:actions>
    </x-page-header>

@endsection

@section('content')
    <form action="{{ route('dashboard.settings.roles.update', $role->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="col-xl-12">
            <div class="card stretch stretch-full">
                @include('dashboard.roles.form', [
                    'role' => $role,
                    'permissions' => $permissions,
                ])
            </div>
        </div>
    </form>
@endsection
