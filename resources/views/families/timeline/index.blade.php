@extends('layouts.metronic')

@section('title', __('Family Timeline'))
@section('page_title', __('Family Timeline'))

@section('content')
@php
    $categoryLabels = [
        'birthday' => __('Birthday'),
        'graduation' => __('Graduation'),
        'wedding' => __('Wedding'),
        'anniversary' => __('Anniversary'),
        'achievement' => __('Achievement'),
        'travel' => __('Travel'),
        'purchase' => __('Purchase'),
        'other' => __('Other'),
    ];
    $milestonesByYear = isset($milestones) && $milestones->isNotEmpty()
        ? $milestones->groupBy(fn ($m) => $m->date ? $m->date->format('Y') : '—')->sortKeysDesc()
        : collect();
@endphp
<div class="min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="fin-pulse-eyebrow mb-1.5">{{ __('Family') }}</p>
                <h1 class="fin-pulse-title truncate">{{ __('Timeline') }}</h1>
                <div class="fin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('families.index') }}">{{ __('Families') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium truncate">{{ __('Timeline') }}</span>
                </div>
            </div>
            <div class="shrink-0 w-full sm:w-auto sm:ms-auto flex flex-wrap items-center justify-end gap-2 max-w-full">
                <x-famledger.pulse-button variant="primary" :href="route('families.timeline.create')">
                    <i class="ki-filled ki-plus"></i>
                    {{ __('Add memory') }}
                </x-famledger.pulse-button>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
        @if(session('success'))
            <div class="famledger-timeline-flash max-w-4xl mx-auto rounded-xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-3 text-sm text-emerald-900 shadow-sm" role="status">
                {{ session('success') }}
            </div>
        @endif

        <div class="w-full max-w-4xl mx-auto famledger-timeline-page">
            {{-- Filters --}}
            <div class="famledger-timeline-filters rounded-2xl bg-gradient-to-br from-white via-slate-50/80 to-blue-50/40 p-5 sm:p-6">
                <div class="famledger-timeline-filters-head">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 tracking-tight">{{ __('Browse memories') }}</h2>
                        <p class="text-sm text-slate-500 mt-0.5">{{ __('Filter by category or year.') }}</p>
                    </div>
                </div>
                <form method="GET" class="famledger-timeline-filter-form famledger-timeline-filter-form--spaced">
                    <div class="famledger-timeline-filter-grid">
                        <div class="famledger-timeline-filter-field">
                            <label class="famledger-timeline-label" for="timeline-filter-category">{{ __('Category') }}</label>
                            <select id="timeline-filter-category" name="category" class="kt-select w-full">
                                <option value="">{{ __('All categories') }}</option>
                                @foreach($categoryLabels as $value => $label)
                                    <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="famledger-timeline-filter-field">
                            <label class="famledger-timeline-label" for="timeline-filter-year">{{ __('Year') }}</label>
                            <select id="timeline-filter-year" name="year" class="kt-select w-full">
                                <option value="">{{ __('All years') }}</option>
                                @for($y = (int) date('Y'); $y >= 2000; $y--)
                                    <option value="{{ $y }}" {{ (string) request('year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="famledger-timeline-filter-actions">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-600/25 transition hover:from-blue-700 hover:to-indigo-700">
                                {{ __('Apply') }}
                            </button>
                            @if(request('category') || request('year'))
                                <a href="{{ route('families.timeline.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                                    {{ __('Clear') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            @if($milestonesByYear->isNotEmpty())
                <div class="famledger-timeline-track">
                    @foreach($milestonesByYear as $year => $yearMilestones)
                        <div class="famledger-timeline-year-block">
                            <div class="famledger-timeline-year-sticky">
                                <span class="famledger-timeline-year-pill">{{ $year }}</span>
                            </div>
                            <div class="famledger-timeline-year-items">
                                @foreach($yearMilestones as $milestone)
                                    <article class="famledger-timeline-item">
                                        <div class="famledger-timeline-node" aria-hidden="true"></div>
                                        <div class="famledger-timeline-card">
                                            <div class="famledger-timeline-card__head">
                                                <div class="famledger-timeline-card__titles">
                                                    @if($milestone->category && isset($categoryLabels[$milestone->category]))
                                                        <span class="famledger-timeline-badge" data-category="{{ $milestone->category }}">{{ $categoryLabels[$milestone->category] }}</span>
                                                    @endif
                                                    <h3 class="famledger-timeline-card__title">{{ $milestone->title }}</h3>
                                                    <time class="famledger-timeline-card__date" datetime="{{ $milestone->date?->format('Y-m-d') }}">
                                                        {{ $milestone->date ? $milestone->date->format('l, F j, Y') : '' }}
                                                    </time>
                                                </div>
                                                <div class="famledger-timeline-card__actions">
                                                    <button
                                                        type="button"
                                                        class="famledger-timeline-action-btn js-timeline-view"
                                                        title="{{ __('View memory') }}"
                                                        data-title="{{ $milestone->title }}"
                                                        data-date="{{ $milestone->date ? $milestone->date->format('l, F j, Y') : '' }}"
                                                        data-category="{{ $milestone->category && isset($categoryLabels[$milestone->category]) ? $categoryLabels[$milestone->category] : '' }}"
                                                        data-description="{{ $milestone->description ?? '' }}"
                                                        data-image="{{ $milestone->media_url ?? '' }}"
                                                        data-author="{{ $milestone->user?->name ?? __('Family member') }}"
                                                    >
                                                        <i class="ki-filled ki-eye"></i>
                                                        {{ __('View') }}
                                                    </button>
                                                    @if($milestone->user_id === auth()->id())
                                                        <a href="{{ route('families.timeline.edit', $milestone) }}" class="famledger-timeline-action-btn" title="{{ __('Edit memory') }}">
                                                            <i class="ki-filled ki-pencil"></i>
                                                            {{ __('Edit') }}
                                                        </a>
                                                        <form action="{{ route('families.timeline.destroy', $milestone) }}" method="POST" class="famledger-timeline-delete-form" data-confirm="{{ __('Delete this memory?') }}" onsubmit="return confirm(this.dataset.confirm)">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="famledger-timeline-icon-btn" title="{{ __('Remove') }}">
                                                                <i class="ki-filled ki-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($milestone->description)
                                                <p class="famledger-timeline-card__desc">{{ $milestone->description }}</p>
                                            @endif
                                            @if($milestone->media_url)
                                                <div class="famledger-timeline-media">
                                                    <img src="{{ $milestone->media_url }}" alt="" loading="lazy" decoding="async" />
                                                </div>
                                            @endif
                                            <div class="famledger-timeline-card__foot">
                                                <span class="famledger-timeline-author">
                                                    <i class="ki-filled ki-user famledger-timeline-inline-icon"></i>
                                                    {{ $milestone->user?->name ?? __('Family member') }}
                                                </span>
                                                <span class="famledger-timeline-reactions">
                                                    <i class="ki-filled ki-heart famledger-timeline-inline-icon--heart"></i>
                                                    {{ $milestone->reactions->count() }}
                                                    {{ $milestone->reactions->count() === 1 ? __('reaction') : __('reactions') }}
                                                </span>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="famledger-timeline-empty rounded-2xl bg-gradient-to-b from-slate-50/80 to-white px-6 py-16 sm:py-20 text-center">
                    <div class="famledger-timeline-empty__icon mx-auto mb-6 flex size-20 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-600 shadow-inner">
                        <i class="ki-filled ki-calendar-add text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-2">{{ __('No memories yet') }}</h3>
                    <p class="text-sm text-slate-600 max-w-md mx-auto mb-8 leading-relaxed">
                        {{ __('Capture birthdays, trips, and milestones—your family story grows here.') }}
                    </p>
                    <x-famledger.pulse-button variant="primary" :href="route('families.timeline.create')">
                        <i class="ki-filled ki-plus"></i>
                        {{ __('Add your first memory') }}
                    </x-famledger.pulse-button>
                </div>
            @endif
        </div>
    </div>
</div>

<div id="timeline-view-modal" class="famledger-timeline-modal" aria-hidden="true">
    <div class="famledger-timeline-modal__backdrop js-timeline-close"></div>
    <div class="famledger-timeline-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="timeline-view-title">
        <div class="famledger-timeline-modal__head">
            <h3 id="timeline-view-title" class="famledger-timeline-modal__title"></h3>
            <button type="button" class="famledger-timeline-icon-btn js-timeline-close" title="{{ __('Close') }}">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <p id="timeline-view-meta" class="famledger-timeline-modal__meta"></p>
        <p id="timeline-view-desc" class="famledger-timeline-modal__desc"></p>
        <div id="timeline-view-image-wrap" class="famledger-timeline-media hidden">
            <img id="timeline-view-image" src="" alt="" loading="lazy" decoding="async" />
        </div>
        <div class="famledger-timeline-modal__foot">
            <span id="timeline-view-author" class="famledger-timeline-author"></span>
            <button type="button" class="famledger-timeline-create-btn famledger-timeline-create-btn--secondary js-timeline-close">{{ __('Close') }}</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Timeline — layout & theme (Metronic build often omits Tailwind margin utilities; use explicit CSS.) */
.famledger-timeline-page {
    display: block;
}
/* Space between filter card and timeline / empty state (reliable in all browsers) */
.famledger-timeline-page > .famledger-timeline-track,
.famledger-timeline-page > .famledger-timeline-empty {
    margin-top: 2.5rem;
}
@media (min-width: 640px) {
    .famledger-timeline-page > .famledger-timeline-track,
    .famledger-timeline-page > .famledger-timeline-empty {
        margin-top: 3rem;
    }
}

.famledger-timeline-filters {
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.famledger-timeline-filters-head {
    margin-bottom: 1.25rem;
}
.famledger-timeline-filter-form--spaced {
    padding-top: 0.25rem;
    padding-bottom: 0.75rem;
}

.famledger-timeline-flash {
    margin-bottom: 1.5rem;
}

.famledger-timeline-empty {
    border: 1px dashed #e2e8f0;
}
.famledger-timeline-filter-form { margin: 0; }
.famledger-timeline-label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: #334155;
    margin-bottom: 0.5rem;
}
.famledger-timeline-filter-grid {
    display: grid;
    gap: 1rem 1.25rem;
    grid-template-columns: 1fr;
    align-items: end;
}
@media (min-width: 640px) {
    .famledger-timeline-filter-grid {
        grid-template-columns: 1fr 1fr auto;
    }
}
.famledger-timeline-filter-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.famledger-timeline-filter-field .kt-select {
    border-radius: 0.75rem;
    min-height: 2.75rem;
}

.famledger-timeline-track {
    position: relative;
    padding-left: 0;
}

.famledger-timeline-year-block {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0 1.25rem;
    margin-bottom: 3rem;
}
.famledger-timeline-year-block:last-child {
    margin-bottom: 0;
}
@media (min-width: 640px) {
    .famledger-timeline-year-block {
        gap: 0 2rem;
    }
}

.famledger-timeline-year-sticky {
    position: relative;
    padding-top: 0.25rem;
    width: 4.5rem;
    flex-shrink: 0;
}
@media (min-width: 640px) {
    .famledger-timeline-year-sticky { width: 5.5rem; }
}

.famledger-timeline-year-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 3.5rem;
    padding: 0.35rem 0.65rem;
    border-radius: 9999px;
    font-size: 0.8125rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #1e40af;
    background: linear-gradient(135deg, #eff6ff 0%, #eef2ff 100%);
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.famledger-timeline-year-items {
    position: relative;
    padding-left: 1.25rem;
    border-left: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
@media (min-width: 640px) {
    .famledger-timeline-year-items {
        gap: 1.75rem;
    }
}

.famledger-timeline-item {
    position: relative;
}

.famledger-timeline-node {
    position: absolute;
    left: calc(-1.25rem - 5px);
    top: 1.25rem;
    width: 0.625rem;
    height: 0.625rem;
    border-radius: 9999px;
    background: #94a3b8;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px #e2e8f0;
    z-index: 1;
}
@media (min-width: 640px) {
    .famledger-timeline-node { left: calc(-1.25rem - 6px); }
}

.famledger-timeline-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.25rem 1.35rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
}
.famledger-timeline-card:hover {
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.famledger-timeline-card__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.75rem;
}
.famledger-timeline-card__titles { min-width: 0; }
.famledger-timeline-card__title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    margin: 0.35rem 0 0.25rem;
    letter-spacing: -0.02em;
}
.famledger-timeline-card__date {
    display: block;
    font-size: 0.8125rem;
    font-weight: 500;
    color: #64748b;
}
.famledger-timeline-card__desc {
    font-size: 0.9375rem;
    line-height: 1.65;
    color: #475569;
    margin: 0 0 1rem;
}

.famledger-timeline-badge {
    display: inline-block;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.2rem 0.55rem;
    border-radius: 0.375rem;
    background: #f1f5f9;
    color: #475569;
}
.famledger-timeline-badge[data-category="birthday"] { background: #fce7f3; color: #9d174d; }
.famledger-timeline-badge[data-category="wedding"] { background: #fce7f3; color: #831843; }
.famledger-timeline-badge[data-category="graduation"] { background: #e0e7ff; color: #3730a3; }
.famledger-timeline-badge[data-category="anniversary"] { background: #ffedd5; color: #9a3412; }
.famledger-timeline-badge[data-category="achievement"] { background: #d1fae5; color: #065f46; }
.famledger-timeline-badge[data-category="travel"] { background: #e0f2fe; color: #0369a1; }
.famledger-timeline-badge[data-category="purchase"] { background: #f3e8ff; color: #6b21a8; }
.famledger-timeline-badge[data-category="other"] { background: #f1f5f9; color: #475569; }

.famledger-timeline-media {
    border-radius: 0.75rem;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    margin-bottom: 0.5rem;
}
.famledger-timeline-media img {
    display: block;
    width: 100%;
    height: auto;
    max-height: 18rem;
    object-fit: cover;
}

.famledger-timeline-card__foot {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem 1.25rem;
    padding-top: 1rem;
    margin-top: 0.5rem;
    border-top: 1px solid #e2e8f0;
    font-size: 0.8125rem;
    color: #64748b;
}
.famledger-timeline-author {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-weight: 500;
    color: #475569;
}
.famledger-timeline-reactions {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: #94a3b8;
}

.famledger-timeline-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.5rem;
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
    transition: color 0.15s, background 0.15s;
}
.famledger-timeline-icon-btn:hover {
    color: #dc2626;
    background: rgba(254, 226, 226, 0.6);
}

.famledger-timeline-card__actions {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.famledger-timeline-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    min-height: 2.1rem;
    padding: 0.25rem 0.7rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.famledger-timeline-action-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}

.famledger-timeline-delete-form { margin: 0; }

.famledger-timeline-inline-icon {
    font-size: 0.85rem;
    opacity: 0.72;
}
.famledger-timeline-inline-icon--heart {
    font-size: 0.95rem;
    opacity: 0.85;
}

.famledger-timeline-modal {
    position: fixed;
    inset: 0;
    z-index: 1200;
    display: none;
}
.famledger-timeline-modal.is-open {
    display: block;
}
.famledger-timeline-modal__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
}
.famledger-timeline-modal__dialog {
    position: relative;
    max-width: 42rem;
    margin: 5vh auto;
    width: calc(100% - 2rem);
    max-height: 90vh;
    overflow: auto;
    background: #fff;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 24px 48px rgba(15, 23, 42, 0.22);
    padding: 1rem 1rem 1.25rem;
}
@media (min-width: 640px) {
    .famledger-timeline-modal__dialog {
        padding: 1.25rem 1.5rem 1.5rem;
    }
}
.famledger-timeline-modal__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}
.famledger-timeline-modal__title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
}
.famledger-timeline-modal__meta {
    margin: 0.35rem 0 0;
    font-size: 0.8125rem;
    color: #64748b;
}
.famledger-timeline-modal__desc {
    margin: 0.9rem 0 1rem;
    font-size: 0.95rem;
    line-height: 1.7;
    color: #475569;
    white-space: pre-wrap;
}
.famledger-timeline-modal__foot {
    margin-top: 1rem;
    padding-top: 0.85rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('timeline-view-modal');
    if (!modal) return;

    const titleEl = document.getElementById('timeline-view-title');
    const metaEl = document.getElementById('timeline-view-meta');
    const descEl = document.getElementById('timeline-view-desc');
    const authorEl = document.getElementById('timeline-view-author');
    const imageWrapEl = document.getElementById('timeline-view-image-wrap');
    const imageEl = document.getElementById('timeline-view-image');

    const openModal = (btn) => {
        const title = btn.getAttribute('data-title') || '';
        const date = btn.getAttribute('data-date') || '';
        const category = btn.getAttribute('data-category') || '';
        const description = btn.getAttribute('data-description') || '';
        const image = btn.getAttribute('data-image') || '';
        const author = btn.getAttribute('data-author') || '';

        titleEl.textContent = title;
        metaEl.textContent = [date, category].filter(Boolean).join(' • ');
        descEl.textContent = description || "{{ __('No description provided.') }}";
        authorEl.textContent = author;

        if (image) {
            imageEl.src = image;
            imageWrapEl.classList.remove('hidden');
        } else {
            imageEl.removeAttribute('src');
            imageWrapEl.classList.add('hidden');
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('.js-timeline-view').forEach((btn) => {
        btn.addEventListener('click', () => openModal(btn));
    });

    modal.querySelectorAll('.js-timeline-close').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
})();
</script>
@endpush
