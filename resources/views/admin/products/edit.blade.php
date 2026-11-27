@extends('layouts.app')

@section('title', 'Edit ' . $product->name . ' - Admin')

@section('content')
    <a href="{{ route('admin.products.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Kembali</a>
    <h1 class="text-2xl font-bold mt-3 mb-5">Edit Preset</h1>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.products._form', ['submitLabel' => 'Simpan perubahan'])
    </form>
@endsection
