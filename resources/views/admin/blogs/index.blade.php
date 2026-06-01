@extends('admin.layouts.main')

@section('title', 'Blogs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Blogs</h1>
            <p class="text-muted mb-0">Manage blog posts and SEO content.</p>
        </div>
        <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Blog
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card-modern">
        <div class="table-responsive">
            <table id="blogsTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Meta Title</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blogs as $blog)
                        <tr>
                            <td>{{ $blog->id }}</td>
                            <td>
                                @if($blog->image)
                                    <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}" style="width:56px; height:56px; object-fit:cover; border-radius:8px;">
                                @else
                                    <span class="badge bg-light text-dark">No image</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $blog->title }}</td>
                            <td><code>{{ $blog->slug }}</code></td>
                            <td>{{ $blog->meta_title ?: '-' }}</td>
                            <td>{{ $blog->updated_at?->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn btn-sm btn-primary"
                                        data-swal-confirm
                                        data-swal-title="Edit this blog?"
                                        data-swal-text="You will open the blog editor."
                                        data-swal-icon="question"
                                        data-swal-confirm-text="Continue"
                                        data-swal-confirm-color="#0d6efd">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.blogs.destroy', $blog->id) }}" method="POST"
                                        data-swal-confirm
                                        data-swal-title="Delete this blog?"
                                        data-swal-text="This action cannot be undone."
                                        data-swal-icon="warning"
                                        data-swal-confirm-text="Yes, delete"
                                        data-swal-confirm-color="#dc3545">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No blogs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#blogsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10,
            autoWidth: false
        });
    });
</script>
@endsection
