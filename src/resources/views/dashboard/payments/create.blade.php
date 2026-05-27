@extends('layouts.app')

@section('title', 'Create Payment')

@section('content')
    <div class="col-lg-12">
        <x-page-header title="Create Payment" :breadcrumbs="Breadcrumbs::render('dashboard.payments.create')" />
        <form action="{{ route('dashboard.payments.store') }}" method="POST">
            @csrf
            @include('dashboard.payments.form')
        </form>
    </div>
@endsection
