@extends('layouts.app')
@section('title', 'Edit Permission')

@section('page-header')
    <x-page-header title="Edit Permission">
        {{-- BreadCrumb --}}
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.permissions.edit') }}
        </x-slot:left>

        {{-- Actions --}}
        <x-slot:actions>
        </x-slot:actions>
    </x-page-header>

@endsection

@section('content')
    <form action="{{ route('dashboard.settings.permissions.update', $permission->id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="col-xl-12">
            <div class="card stretch stretch-full">
                @include('dashboard.permissions.form')
            </div>
        </div>
    </form>
@endsection
