{{-- Family audit trail: always show result summary; page controls use Metronic kt-btn (visible on app chrome). --}}
@if ($paginator->total() > 0)
<nav class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between w-full" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
    <p class="text-xs text-muted-foreground shrink-0">
        {!! __('Showing') !!}
        @if ($paginator->firstItem())
            <span class="font-medium text-foreground">{{ $paginator->firstItem() }}</span>
            {!! __('to') !!}
            <span class="font-medium text-foreground">{{ $paginator->lastItem() }}</span>
        @else
            {{ $paginator->count() }}
        @endif
        {!! __('of') !!}
        <span class="font-medium text-foreground">{{ $paginator->total() }}</span>
        {!! __('results') !!}
    </p>

    @if ($paginator->hasPages())
    <div class="flex flex-wrap items-center gap-1.5 justify-end">
        @if ($paginator->onFirstPage())
            <span class="kt-btn kt-btn-xs kt-btn-ghost opacity-50 pointer-events-none">{{ __('Previous') }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="kt-btn kt-btn-xs kt-btn-outline">{{ __('Previous') }}</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-1.5 text-xs text-muted-foreground select-none">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="kt-btn kt-btn-xs kt-btn-primary min-w-[2rem] justify-center">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="kt-btn kt-btn-xs kt-btn-outline min-w-[2rem] justify-center" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="kt-btn kt-btn-xs kt-btn-outline">{{ __('Next') }}</a>
        @else
            <span class="kt-btn kt-btn-xs kt-btn-ghost opacity-50 pointer-events-none">{{ __('Next') }}</span>
        @endif
    </div>
    @endif
</nav>
@endif
