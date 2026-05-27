@extends('layouts.app')

@section('title', 'Edit Payment')

@section('content')
    <div class="col-lg-12">
        <x-page-header title="Edit Payment" :breadcrumbs="Breadcrumbs::render('dashboard.payments.edit', $payment)" />
        <form action="{{ route('dashboard.payments.update', $payment) }}" method="POST">
            @csrf
            @method('PUT')
            @include('dashboard.payments.form')
        </form>
    </div>
@endsection
