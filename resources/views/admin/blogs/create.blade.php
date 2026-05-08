@extends('admin.layouts.main')

@section('title', 'Create Blog')

@section('content')
<div class="admin-panel">
    <!-- ========== SIDEBAR START ========== -->
    @include('admin.layouts.sidebar')
    <!-- ========== SIDEBAR END ========== -->

    <main class="main-content flex-grow-1">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div>
                    <h1 class="dashboard-title">
                        <span class="title-icon">✍️</span> Create Blog Post
                    </h1>
                    <p class="dashboard-subtitle">Add a new SEO-ready blog post</p>
                </div>
                <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Blogs
                </a>
            </div>

            <!-- Form Card -->
            <div class="card-modern">
                <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data" class="blog-form">
                    @csrf

                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h5 class="form-section-title">
                            <i class="fas fa-info-circle"></i> Basic Information
                        </h5>

                        <div class="row g-3">
                            <!-- Title -->
                            <div class="col-lg-8">
                                <label class="form-label">Blog Title</label>
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                                    placeholder="Enter blog title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Featured Image -->
                            <div class="col-lg-4">
                                <label class="form-label">Featured Image</label>
                                <div class="image-upload-wrapper">
                                    <div class="image-placeholder mb-2" id="imagePlaceholder">
                                        <i class="fas fa-image"></i>
                                        <p>No image selected</p>
                                    </div>
                                    <div class="image-preview mb-2" id="imagePreviewWrapper" style="display:none;">
                                        <img src="" alt="Blog Image" id="previewImage">
                                    </div>
                                    <input type="file" name="image" id="imageInput" class="form-control @error('image') is-invalid @enderror"
                                        accept="image/*" onchange="previewImage(event)">
                                    @error('image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Slug -->
                            <div class="col-lg-6">
                                <label class="form-label">Slug (URL)</label>
                                <div class="input-group">
                                    <span class="input-group-text">/blog/</span>
                                    <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror"
                                        placeholder="blog-title" value="{{ old('slug') }}" required>
                                </div>
                                @error('slug')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1">Auto-generated from title. Edit manually if needed.</small>
                            </div>

                            <!-- Status -->
                            <div class="col-lg-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror">
                                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="form-section">
                        <h5 class="form-section-title">
                            <i class="fas fa-file-alt"></i> Content
                        </h5>

                        <div class="row g-3">
                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="8" placeholder="Write your blog content here...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- SEO Section -->
                    <div class="form-section">
                        <h5 class="form-section-title">
                            <i class="fas fa-search"></i> SEO & Meta Information
                        </h5>

                        <div class="row g-3">
                            <!-- Meta Title -->
                            <div class="col-lg-6">
                                <label class="form-label">Meta Title</label>
                                <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror"
                                    placeholder="SEO title (50-60 characters)" value="{{ old('meta_title') }}" maxlength="60">
                                <small class="text-muted d-block mt-1">Recommended: 50-60 characters</small>
                                @error('meta_title')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Meta Description -->
                            <div class="col-lg-6">
                                <label class="form-label">Meta Description</label>
                                <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror"
                                    rows="2" placeholder="SEO description (150-160 characters)" maxlength="160">{{ old('meta_description') }}</textarea>
                                <small class="text-muted d-block mt-1">Recommended: 150-160 characters</small>
                                @error('meta_description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Meta Keywords -->
                            <div class="col-12">
                                <label class="form-label">Meta Keywords</label>
                                <input type="text" name="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror"
                                    placeholder="Separate keywords with commas" value="{{ old('meta_keywords') }}">
                                <small class="text-muted d-block mt-1">E.g., keyword1, keyword2, keyword3</small>
                                @error('meta_keywords')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Blog
                        </button>
                        <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    // Image Preview
    function previewImage(event) {
        const file = event.target.files[0];
        const placeholder = document.getElementById('imagePlaceholder');
        const previewWrapper = document.getElementById('imagePreviewWrapper');
        const previewImg = document.getElementById('previewImage');

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                placeholder.style.display = 'none';
                previewImg.src = e.target.result;
                previewWrapper.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    // Slug Auto-generation & CKEditor
    (function () {
        const title = document.getElementById('title');
        const slug = document.getElementById('slug');
        let manuallyEditedSlug = false;

        function toSlug(value) {
            return value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }

        slug.addEventListener('input', function () {
            manuallyEditedSlug = true;
        });

        title.addEventListener('input', function () {
            if (!manuallyEditedSlug || !slug.value.trim()) {
                slug.value = toSlug(title.value);
            }
        });

        if (!slug.value.trim() && title.value.trim()) {
            slug.value = toSlug(title.value);
        }

        // Initialize CKEditor
        ClassicEditor.create(document.querySelector('#description'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'link',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ]
            },
            ui: {
                viewportOffset: { top: 100 }
            }
        }).catch(function (error) {
            console.error(error);
        });
    })();
</script>
@endsection
