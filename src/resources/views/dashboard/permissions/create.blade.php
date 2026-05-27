@extends('layouts.app')
@section('title', 'Create Permission')

@section('page-header')
    <x-page-header title="Tambah Permission">
        {{-- BreadCrumb --}}
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.permissions.create') }}
        </x-slot:left>

        {{-- Actions --}}
        <x-slot:actions>
        </x-slot:actions>
    </x-page-header>

@endsection

@section('content')
    <form action="{{ route('dashboard.settings.permissions.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="col-xl-12">
            <div class="card stretch stretch-full">
                @include('dashboard.permissions.form')
            </div>
        </div>
    </form>
@endsection
