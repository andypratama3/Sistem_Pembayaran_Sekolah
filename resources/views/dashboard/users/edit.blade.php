@extends('layouts.app')
@section('title', 'Edit User')

@section('page-header')
    <x-page-header title="Edit Pengguna">
        {{-- BreadCrumb --}}
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.users.edit', $user->id) }}
        </x-slot:left>

        {{-- Actions --}}
        <x-slot:actions>
        </x-slot:actions>
    </x-page-header>

@endsection

@section('content')
    <form action="{{ route('dashboard.settings.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="col-xl-12">
            <div class="card stretch stretch-full">
                @include('dashboard.users.form')
            </div>
        </div>
    </form>
@endsection
