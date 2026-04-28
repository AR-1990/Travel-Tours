@php
    $isEdit = isset($blog);
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
        <input
            type="text"
            id="title"
            name="title"
            class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $blog->title ?? '') }}"
            required
        >
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
        <input
            type="text"
            id="slug"
            name="slug"
            class="form-control @error('slug') is-invalid @enderror"
            value="{{ old('slug', $blog->slug ?? '') }}"
            required
        >
        @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description (HTML)</label>
        <textarea
            id="description"
            name="description"
            rows="14"
            class="form-control @error('description') is-invalid @enderror"
        >{{ old('description', $blog->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="meta_title" class="form-label">Meta Title</label>
        <input
            type="text"
            id="meta_title"
            name="meta_title"
            class="form-control @error('meta_title') is-invalid @enderror"
            value="{{ old('meta_title', $blog->meta_title ?? '') }}"
        >
        @error('meta_title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="image" class="form-label">Image</label>
        <input
            type="file"
            id="image"
            name="image"
            class="form-control @error('image') is-invalid @enderror"
            accept=".jpg,.jpeg,.png,.webp"
        >
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if($isEdit && !empty($blog->image))
            <div class="mt-2">
                <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}" style="width:120px; height:80px; object-fit:cover; border-radius:8px;">
            </div>
        @endif
    </div>

    <div class="col-12">
        <label for="meta_description" class="form-label">Meta Description</label>
        <textarea
            id="meta_description"
            name="meta_description"
            rows="3"
            class="form-control @error('meta_description') is-invalid @enderror"
        >{{ old('meta_description', $blog->meta_description ?? '') }}</textarea>
        @error('meta_description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-2"></i>{{ $isEdit ? 'Update Blog' : 'Create Blog' }}
    </button>
</div>

<style>
    .ck-editor__editable_inline {
        min-height: 420px;
    }
</style>
