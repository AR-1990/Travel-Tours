@extends('admin.layouts.main')

@section('title', 'Partner applications')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Partner applications',
        'subtitle' => 'People who submitted the Partner With Us form.',
        'icon' => 'fas fa-handshake',
    ])

    @include('admin.partials.flash')

    <form method="GET" action="{{ route('admin.partner-inquiries.index') }}" class="panel-surface mb-3 p-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    <option value="new" @selected($status === 'new')>New</option>
                    <option value="contacted" @selected($status === 'contacted')>Contacted</option>
                    <option value="closed" @selected($status === 'closed')>Closed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Partnership type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All types</option>
                    @foreach($partnerTypes as $key => $partner)
                        <option value="{{ $key }}" @selected($type === $key)>{{ $partner['title'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.partner-inquiries.index') }}" class="btn btn-light btn-sm">Reset</a>
            </div>
            <div class="col-md-3 text-md-end">
                <span class="badge bg-success">{{ $newCount }} new</span>
            </div>
        </div>
    </form>

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100 mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inquiry)
                        <tr>
                            <td>{{ $inquiry->id }}</td>
                            <td class="fw-semibold">{{ $inquiry->name }}</td>
                            <td><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></td>
                            <td>{{ $inquiry->phone ?: '—' }}</td>
                            <td>{{ $inquiry->typeTitle() }}</td>
                            <td>{{ $inquiry->company ?: '—' }}</td>
                            <td><span class="badge {{ $inquiry->statusBadgeClass() }}">{{ $inquiry->statusLabel() }}</span></td>
                            <td>{{ $inquiry->created_at?->format('M d, Y g:i A') }}</td>
                            <td>
                                <a href="{{ route('admin.partner-inquiries.show', $inquiry) }}" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No partnership applications yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inquiries->hasPages())
            <div class="p-3">{{ $inquiries->links() }}</div>
        @endif
    </div>
</div>
@endsection
