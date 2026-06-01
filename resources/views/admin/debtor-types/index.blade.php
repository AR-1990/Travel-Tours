@extends('admin.layouts.main')

@section('title', 'Debtor types')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Debtor types</h1>
            <p class="text-muted mb-0">Define cash/credit (or custom) terms for agencies. Cash and credit are built-in.</p>
        </div>
        <a href="{{ route('admin.debtor-types.create') }}" class="btn btn-primary">Add type</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="card-modern">
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
