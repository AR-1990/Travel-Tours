@extends('admin.layouts.main')

@section('title', 'Create Blog')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Create blog',
        'subtitle' => 'Add a new SEO-ready blog post.',
        'icon' => 'fas fa-newspaper',
        'actions' => '<a href="'.e(route('admin.blogs.index')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface">
        <div class="card-body">
            <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('admin.blogs.partials.form')
            </form>
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
