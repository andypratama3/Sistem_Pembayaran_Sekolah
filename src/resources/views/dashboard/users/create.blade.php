@extends('layouts.app')
@section('title', 'Create User')

@section('page-header')
    <x-page-header title="Tambah Pengguna">
        {{-- BreadCrumb --}}
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.users.create') }}
        </x-slot:left>

        {{-- Actions --}}
        <x-slot:actions>
        </x-slot:actions>
    </x-page-header>

@endsection

@section('content')
    <form action="{{ route('dashboard.settings.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="col-xl-12">
            <div class="card stretch stretch-full">
                @include('dashboard.users.form')
            </div>
        </div>
    </form>
@endsection
