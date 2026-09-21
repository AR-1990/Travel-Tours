@extends('admin.layouts.main')

@section('title', 'Edit debtor type')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Edit debtor type',
        'subtitle' => 'Update debtor type settings.',
        'icon' => 'fas fa-tags',
        'actions' => '<a href="'.e(route('admin.debtor-types.index')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.debtor-types.update', $debtorType->id) }}"
                data-swal-confirm
                data-swal-title="Save changes?"
                data-swal-text="Debtor type settings will be updated."
                data-swal-icon="question"
                data-swal-confirm-text="Yes, save"
                data-swal-confirm-color="#053750">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $debtorType->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $debtorType->slug) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $debtorType->description) }}</textarea>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $debtorType->is_active))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <a href="{{ route('admin.debtor-types.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
