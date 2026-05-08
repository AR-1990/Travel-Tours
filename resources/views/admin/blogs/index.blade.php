@extends('admin.layouts.main')

@section('title', 'Blogs')

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
                        <span class="title-icon">📝</span> Blogs Management
                    </h1>
                    <p class="dashboard-subtitle">Manage blog posts and SEO content</p>
                </div>
                <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Add New Blog
                </a>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Blogs Table Card -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <div class="header-with-stats">
                        <h2 class="h4 mb-0">
                            <i class="fas fa-list me-2"></i>Blog Posts
                        </h2>
                        <span class="stat-badge">{{ count($blogs) }} Post(s)</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="blogsTable" class="blogs-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Image</th>
                                <th>Slug</th>
                                <th>Meta Title</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($blogs as $blog)
                                <tr class="blog-row">
                                    <td class="blog-title">
                                        <div class="blog-title-content">
                                            <h5 class="mb-0">{{ $blog->title }}</h5>
                                        </div>
                                    </td>
                                    <td class="blog-image">
                                        @if($blog->image)
                                            <div class="blog-thumbnail">
                                                <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}">
                                            </div>
                                        @else
                                            <div class="blog-thumbnail-empty">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="blog-slug">
                                        <code class="slug-badge">{{ $blog->slug }}</code>
                                    </td>
                                    <td class="blog-meta">
                                        <span class="meta-text">{{ $blog->meta_title ?: '-' }}</span>
                                    </td>
                                    <td class="blog-date">
                                        <div class="date-badge">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            {{ $blog->updated_at?->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn-action btn-edit" title="Edit Blog">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.blogs.destroy', $blog->id) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-delete" title="Delete Blog" onclick="return confirm('Are you sure you want to delete this blog post?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-row">
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                                            <p class="text-muted mb-0">No blog posts found</p>
                                            <a href="{{ route('admin.blogs.create') }}" class="btn btn-sm btn-primary mt-3">
                                                <i class="fas fa-plus me-1"></i>Create First Blog
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#blogsTable').DataTable({
            order: [[4, 'desc']], // Order by date descending
            pageLength: 10,
            autoWidth: false,
            responsive: true,
            language: {
                search: "Search posts...",
                lengthMenu: "Show _MENU_ posts per page",
                info: "Showing _START_ to _END_ of _TOTAL_ posts",
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            }
        });
    });
</script>
@endsection
