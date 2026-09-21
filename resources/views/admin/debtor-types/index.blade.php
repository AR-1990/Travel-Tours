@extends('admin.layouts.main')

@section('title', 'Debtor types')

@section('content')
<div class="container-fluid panel-page">
    
@include('admin.partials.page-header', [
        'title' => 'Debtor types',
        'subtitle' => 'Classify debtor categories for agencies.',
        'icon' => 'fas fa-tags',
        'actions' => '<a href="'.e(route('admin.debtor-types.create')).'" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Add type</a>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($debtorTypes as $dt)
                        <tr>
                            <td>{{ $dt->name }}</td>
                            <td><code>{{ $dt->slug }}</code></td>
                            <td>{{ $dt->is_active ? 'Yes' : 'No' }}</td>
                            <td>
                                <a href="{{ route('admin.debtor-types.edit', $dt->id) }}" class="btn btn-sm btn-primary"
                                    data-swal-confirm
                                    data-swal-title="Edit this debtor type?"
                                    data-swal-text="You will open the edit form."
                                    data-swal-icon="question"
                                    data-swal-confirm-text="Continue"
                                    data-swal-confirm-color="#0d6efd">Edit</a>
                                @if(!in_array($dt->slug, ['cash','credit']))
                                    <form action="{{ route('admin.debtor-types.destroy', $dt->id) }}" method="POST" class="d-inline"
                                        data-swal-confirm
                                        data-swal-title="Delete this debtor type?"
                                        data-swal-text="You cannot delete built-in cash/credit here; this row is custom."
                                        data-swal-icon="warning"
                                        data-swal-confirm-text="Yes, delete"
                                        data-swal-confirm-color="#dc3545">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
