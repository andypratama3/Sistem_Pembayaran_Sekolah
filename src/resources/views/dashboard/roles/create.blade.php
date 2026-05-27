@extends('layouts.app')
@section('title', 'Create Role')

@section('page-header')
    <x-page-header>
        {{-- BreadCrumb --}}
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.roles.create') }}
        </x-slot:left>

        {{-- Actions --}}
        <x-slot:actions>
        </x-slot:actions>
    </x-page-header>

@endsection

@section('content')
    <form action="{{ route('dashboard.settings.roles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="col-xl-12">
            <div class="card stretch stretch-full">
                @include('dashboard.roles.form', [
                    'permissions' => $permissions,
                ])
            </div>
        </div>
    </form>
@endsection
