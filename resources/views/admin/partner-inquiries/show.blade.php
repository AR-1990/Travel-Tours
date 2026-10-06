@extends('admin.layouts.main')

@section('title', 'Partner application')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => $inquiry->name,
        'subtitle' => $inquiry->typeTitle().' · submitted '.$inquiry->created_at?->format('M d, Y g:i A'),
        'icon' => 'fas fa-handshake',
        'actions' => '<a href="'.e(route('admin.partner-inquiries.index')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="panel-surface p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Partnership type</dt>
                    <dd class="col-sm-9">{{ $inquiry->typeTitle() }}</dd>
                    <dt class="col-sm-3">Full name</dt>
                    <dd class="col-sm-9">{{ $inquiry->name }}</dd>
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9"><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></dd>
                    <dt class="col-sm-3">Phone</dt>
                    <dd class="col-sm-9">{{ $inquiry->phone ?: '—' }}</dd>
                    <dt class="col-sm-3">Company / brand</dt>
                    <dd class="col-sm-9">{{ $inquiry->company ?: '—' }}</dd>
                    <dt class="col-sm-3">Message</dt>
                    <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $inquiry->message }}</dd>
                    <dt class="col-sm-3">IP</dt>
                    <dd class="col-sm-9">{{ $inquiry->ip_address ?: '—' }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel-surface p-4">
                <p class="mb-2"><span class="badge {{ $inquiry->statusBadgeClass() }}">{{ $inquiry->statusLabel() }}</span></p>
                <form method="POST" action="{{ route('admin.partner-inquiries.update', $inquiry) }}" class="mb-3">
                    @csrf
                    @method('PUT')
                    <label class="form-label">Update status</label>
                    <select name="status" class="form-select mb-2">
                        <option value="new" @selected($inquiry->status === 'new')>New</option>
                        <option value="contacted" @selected($inquiry->status === 'contacted')>Contacted</option>
                        <option value="closed" @selected($inquiry->status === 'closed')>Closed</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Save status</button>
                </form>
                <form method="POST" action="{{ route('admin.partner-inquiries.destroy', $inquiry) }}"
                    data-swal-confirm
                    data-swal-title="Delete this application?"
                    data-swal-text="This cannot be undone."
                    data-swal-icon="warning"
                    data-swal-confirm-text="Yes, delete"
                    data-swal-confirm-color="#dc3545">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
