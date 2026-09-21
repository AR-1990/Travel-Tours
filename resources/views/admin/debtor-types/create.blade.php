@extends('admin.layouts.main')

@section('title', 'Create debtor type')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Create debtor type',
        'subtitle' => 'Add a debtor type for agency assignment.',
        'icon' => 'fas fa-tags',
        'actions' => '<a href="'.e(route('admin.debtor-types.index')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.debtor-types.store') }}"
                data-swal-confirm
                data-swal-title="Create this debtor type?"
                data-swal-text="It will be available to assign to agencies."
                data-swal-icon="question"
                data-swal-confirm-text="Yes, create"
                data-swal-confirm-color="#053750">
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
</div>
@endsection
