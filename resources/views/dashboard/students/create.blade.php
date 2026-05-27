@extends('layouts.app')

@section('title', 'Tambah Siswa')

@section('page-header')
    <x-page-header title="Tambah Siswa">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.students.create') }}
        </x-slot:left>
        <x-slot:actions>
            <a href="{{ route('dashboard.students.index') }}" class="btn btn-md btn-outline-secondary">
                <i class="feather-arrow-left me-2"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="col-lg-12">
        <form action="{{ route('dashboard.students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('dashboard.students.form')
        </form>
    </div>
@endsection
