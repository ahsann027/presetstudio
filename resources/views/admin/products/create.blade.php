@extends('layouts.app')

@section('title', 'Tambah Preset - Admin')

@section('content')
    <a href="{{ route('admin.products.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Kembali</a>
    <h1 class="text-2xl font-bold mt-3 mb-5">Tambah Preset</h1>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.products._form', ['submitLabel' => 'Simpan preset'])
    </form>
@endsection
