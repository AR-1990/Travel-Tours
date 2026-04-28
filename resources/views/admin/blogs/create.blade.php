@extends('admin.layouts.main')

@section('title', 'Create Blog')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Create Blog</h1>
                    <p class="text-muted mb-0">Add a new SEO-ready blog post.</p>
                </div>
                <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Blogs
                </a>
            </div>

            <div class="card-modern">
                <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('admin.blogs.partials.form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
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

        ClassicEditor.create(document.querySelector('#description')).catch(function (error) {
            console.error(error);
        });
    })();
</script>
@endsection
