@if(!empty($searchResult['solutions_paginator']))
    @php $paginator = $searchResult['solutions_paginator']; @endphp
    <nav class="flight-results-pagination mt-4" aria-label="Fare results pages">
        <p class="small text-muted text-center mb-2">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} fares
        </p>
        {{ $paginator->links('pagination::bootstrap-5') }}
    </nav>
@endif
