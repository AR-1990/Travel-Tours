@extends('admin.layouts.main')

@section('title', 'Create debtor type')

@section('content')
<div class="container-fluid">
    <div class="col-lg-8 mx-auto card-modern p-4">
        <h1 class="h4 mb-3">Create debtor type</h1>
        <form method="POST" action="{{ route('admin.debtor-types.store') }}"
            data-swal-confirm
            data-swal-title="Create this debtor type?"
            data-swal-text="It will be available to assign to agencies."
            data-swal-icon="question"
            data-swal-confirm-text="Yes, create"
            data-swal-confirm-color="#0d6efd">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Slug (optional, auto from name)</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', true))>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <a href="{{ route('admin.debtor-types.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection
