@extends('layouts.metronic')

@section('title', __('Family Tree'))
@section('page_title', __('Family Tree'))

@section('content')
<div class="min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="fin-pulse-eyebrow mb-1.5">{{ __('Family') }}</p>
                <h1 class="fin-pulse-title truncate">{{ __('Tree') }}</h1>
                <div class="fin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('families.index') }}">{{ __('Families') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium truncate">{{ __('Tree') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
        @if(session('success'))
            <div class="max-w-6xl mx-auto mb-4 rounded-lg border border-success/15 bg-success/10 px-4 py-3 text-sm text-foreground" role="status">
                {{ session('success') }}
            </div>
        @endif
        <div class="w-full max-w-6xl mx-auto">
            @if($members->count() > 0)
                {{-- 1. Add relationship form (very top of content) --}}
                <section id="add-relationship" class="famledger-tree-add-rel max-w-4xl mx-auto w-full scroll-mt-24" aria-labelledby="famledger-add-rel-heading">
                    <div class="famledger-tree-add-rel__intro mb-5 md:mb-6">
                        <span class="famledger-tree-add-rel__eyebrow">{{ __('Connections') }}</span>
                        <h2 id="famledger-add-rel-heading" class="famledger-tree-add-rel__title">{{ __('Add relationship') }}</h2>
                        <p class="famledger-tree-add-rel__lede">{{ __('Connect two members and choose how they relate on your tree.') }}</p>
                    </div>
                    <div class="famledger-tree-add-rel__card rounded-2xl bg-white overflow-hidden">
                        <form action="{{ route('families.tree.relationships.store') }}" method="POST" class="js-family-tree-relationship-form famledger-tree-relationship-modal-form famledger-tree-add-rel-form px-5 py-5 sm:px-7 sm:py-6">
                            @csrf
                            @include('families.tree.partials.add-relationship-form-inner', ['members' => $members, 'idPrefix' => 'inline'])
                            <div class="famledger-tree-relationship-modal-actions">
                                <button type="reset" class="kt-btn kt-btn-outline px-5">{{ __('Cancel') }}</button>
                                <button type="submit" class="kt-btn kt-btn-primary px-6" style="min-width: 9rem;">{{ __('Save Relationship') }}</button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- 2. Relationships directory --}}
                <section class="famledger-tree-relationships max-w-4xl mx-auto w-full" aria-labelledby="famledger-rel-list-heading">
                    <div class="famledger-tree-section-head mb-5 md:mb-6">
                        <span class="famledger-tree-add-rel__eyebrow">{{ __('Directory') }}</span>
                        <h2 id="famledger-rel-list-heading" class="famledger-tree-add-rel__title text-[1.0625rem] sm:text-lg">{{ __('Relationships') }}</h2>
                        <p class="famledger-tree-add-rel__lede">{{ __('People linked on your tree. Remove a row to delete that connection.') }}</p>
                    </div>
                    @if($relationships->isNotEmpty())
                        <ul class="famledger-tree-relationships__list rounded-2xl bg-white overflow-hidden">
                            @foreach($relationships as $rel)
                                @php
                                    $typeLabel = match ($rel->type) {
                                        'parent' => __('Parent of'),
                                        'child' => __('Child of'),
                                        'spouse' => __('Spouse of'),
                                        'sibling' => __('Sibling of'),
                                        default => ucfirst($rel->type),
                                    };
                                @endphp
                                <li class="famledger-tree-relationships__row flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-5 py-4 sm:px-6 sm:py-4 min-h-[3.25rem]">
                                    <p class="text-sm leading-relaxed text-slate-800 min-w-0 flex-1">
                                        <span class="font-semibold text-slate-900">{{ $rel->user->name }}</span>
                                        <span class="inline-block text-slate-400 font-normal px-1.5 select-none" aria-hidden="true">·</span>
                                        <span class="text-slate-600 font-medium">{{ $typeLabel }}</span>
                                        <span class="inline-block text-slate-400 font-normal px-1.5 select-none" aria-hidden="true">·</span>
                                        <span class="font-semibold text-slate-900">{{ $rel->relatedUser->name }}</span>
                                    </p>
                                    @can('update', $currentFamily)
                                        <button
                                            type="button"
                                            class="famledger-tree-rel-remove inline-flex items-center justify-center rounded-lg bg-white px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 delete-family-relationship shrink-0"
                                            data-url="{{ route('families.tree.relationships.destroy', $rel) }}"
                                        >{{ __('Remove') }}</button>
                                    @endcan
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="famledger-tree-relationships__empty rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 px-5 py-10 text-center text-sm text-slate-600">
                            {{ __('No relationships yet. Use the form above to create one.') }}
                        </div>
                    @endif
                </section>

                {{-- 3. Family tree (bottom) --}}
                <section class="famledger-tree-shell max-w-4xl mx-auto bg-gradient-to-br from-white via-blue-50/30 to-purple-50/30 rounded-2xl" aria-labelledby="famledger-tree-heading">
                    <div class="famledger-tree-section-head mb-6 md:mb-8">
                        <span class="famledger-tree-add-rel__eyebrow">{{ __('Layout') }}</span>
                        <h3 id="famledger-tree-heading" class="famledger-tree-add-rel__title text-[1.0625rem] sm:text-lg">{{ __('Family tree') }}</h3>
                        <p class="famledger-tree-add-rel__lede">{{ __('Visual layout of members and branches.') }}</p>
                    </div>
                    <div id="family-tree" class="family-tree">
                        @include('families.tree.partials.tree-node', ['nodes' => $tree, 'level' => 0])
                    </div>
                </section>
            @else
                <div class="famledger-tree-empty bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-16 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-6">
                        <i class="ki-filled ki-people text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Build Your Family Tree</h3>
                    <p class="text-gray-600 text-lg max-w-md mx-auto mb-8 leading-relaxed">
                        Start connecting your family members and create beautiful visual relationships that tell your family's story.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('families.members.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl">
                            <i class="ki-filled ki-plus mr-2"></i>
                            Add First Member
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div
    id="famledger-tree-broadcast"
    style="display:none"
    data-enabled="{{ config('broadcasting.default') === 'reverb' ? '1' : '0' }}"
    data-family-id="{{ $currentFamily->id ?? '' }}"
></div>

@push('styles')
<style>
/* Shell: no border — Tailwind `border` + Metronic often stack into a dark outline; elevation only */
/* Padding in CSS (!important) so global/Metronic section resets cannot collapse it */
.famledger-tree-shell {
    border: none !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 28px rgba(15, 23, 42, 0.05);
    box-sizing: border-box !important;
    margin-top: 0;
    margin-bottom: 0;
    padding: 2.5rem 1.75rem 2.25rem !important;
}
@media (min-width: 640px) {
    .famledger-tree-shell {
        padding: 3rem 2.5rem 2.75rem !important;
    }
}
@media (min-width: 1024px) {
    .famledger-tree-shell {
        padding: 4rem 3.5rem 3.5rem !important;
    }
}
.famledger-tree-empty {
    border: none !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
}

.family-tree {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem 0.75rem;
    width: 100%;
    max-width: 840px;
    margin: 0 auto;
    box-sizing: border-box;
}
@media (min-width: 640px) {
    .family-tree {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

.tree-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 2rem 0;
    position: relative;
}

/* Internal person cards: visible 1px frame + soft lift (distinct from outer shell) */
#family-tree .tree-person-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    outline: none;
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    min-width: 160px;
    max-width: 200px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 4px 14px rgba(15, 23, 42, 0.05);
    transition: border-color 0.25s ease, box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

#family-tree .tree-person-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    opacity: 0.5;
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.55), rgba(139, 92, 246, 0.55), rgba(6, 182, 212, 0.55));
    border-radius: 16px 16px 0 0;
}

#family-tree .tree-person-card:hover {
    transform: translateY(-3px) scale(1.01);
    border-color: #cbd5e1;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
}

.tree-person-avatar {
    margin-bottom: 1rem;
}

.avatar-image {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(226, 232, 240, 0.9);
    transition: border-color 0.3s ease;
}

.avatar-placeholder {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.35);
    box-shadow: 0 4px 14px 0 rgba(102, 126, 234, 0.2);
}

.avatar-initial {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    text-transform: uppercase;
}

.tree-person-info {
    margin-bottom: 1rem;
}

.person-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
    line-height: 1.25;
}

.person-role {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.tree-person-decoration {
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
}

.decoration-line {
    width: 40px;
    height: 1px;
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.45), rgba(139, 92, 246, 0.45));
    border-radius: 1px;
}

.tree-children {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 3rem;
    position: relative;
}

.tree-connector {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 1rem 0;
}

.connector-line {
    width: 1px;
    height: 40px;
    background: linear-gradient(to bottom, rgba(203, 213, 225, 0.85), rgba(148, 163, 184, 0.65));
    border-radius: 1px;
}

.connector-dot {
    width: 8px;
    height: 8px;
    background: #3b82f6;
    border-radius: 50%;
    margin-top: 4px;
    box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.12);
}

.tree-siblings {
    display: flex;
    gap: 3rem;
    align-items: flex-start;
    flex-wrap: wrap;
    justify-content: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .tree-siblings {
        gap: 1.5rem;
    }

    #family-tree .tree-person-card {
        min-width: 140px;
        padding: 1.25rem;
    }

    .tree-children {
        margin-top: 2rem;
    }
}

@media (max-width: 480px) {
    #family-tree .tree-person-card {
        min-width: 120px;
        padding: 1rem;
    }

    .avatar-image,
    .avatar-placeholder {
        width: 48px;
        height: 48px;
    }

    .person-name {
        font-size: 1rem;
    }

    .tree-siblings {
        gap: 1rem;
    }
}

/* Animation for smooth loading */
#family-tree .tree-person-card {
    animation: fadeInUp 0.6s ease-out forwards;
    opacity: 0;
    transform: translateY(20px);
}

#family-tree .tree-person-card:nth-child(1) { animation-delay: 0.1s; }
#family-tree .tree-person-card:nth-child(2) { animation-delay: 0.2s; }
#family-tree .tree-person-card:nth-child(3) { animation-delay: 0.3s; }
#family-tree .tree-person-card:nth-child(4) { animation-delay: 0.4s; }
#family-tree .tree-person-card:nth-child(5) { animation-delay: 0.5s; }

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Section headings (tree + relationships) — shared typography */
.famledger-tree-add-rel__eyebrow {
    display: block;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 0.35rem;
}
.famledger-tree-add-rel__title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.3;
    color: #0f172a;
}
@media (min-width: 640px) {
    .famledger-tree-add-rel__title {
        font-size: 1.25rem;
    }
}
.famledger-tree-add-rel__lede {
    margin: 0.5rem 0 0;
    font-size: 0.875rem;
    line-height: 1.55;
    color: #64748b;
}
.famledger-tree-add-rel__intro {
    max-width: 40rem;
}
.famledger-tree-add-rel {
    margin-bottom: clamp(2.5rem, 5vw, 3.5rem);
}
.famledger-tree-add-rel__card {
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 4px 14px rgba(15, 23, 42, 0.05);
}

/* Field groups: label → hint → control (clear spacing; Metronic may ignore Tailwind gaps) */
.famledger-tree-field {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 0;
}
.famledger-tree-field-label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    line-height: 1.35;
    color: #334155;
    letter-spacing: 0.01em;
    margin: 0 0 0.375rem;
    padding: 0;
}
.famledger-tree-field-required {
    margin-left: 0.2rem;
    font-weight: 600;
    font-size: 0.9em;
    color: #94a3b8;
}
.famledger-tree-field-hint {
    margin: 0 0 0.5rem;
    font-size: 0.75rem;
    line-height: 1.45;
    color: #64748b;
}
.famledger-tree-field-select.kt-select,
.famledger-tree-field .kt-select {
    border-radius: 0.625rem;
    width: 100%;
}
.famledger-tree-relationship-fields.famledger-form-row-3 {
    gap: 1.25rem 1.5rem;
}
@media (min-width: 768px) {
    .famledger-tree-relationship-fields.famledger-form-row-3 {
        gap: 1.25rem 1.75rem;
    }
}
/* Add form → Relationships directory → Family tree: spacing between sections */
.famledger-tree-relationships {
    margin-top: 0;
    margin-bottom: clamp(2.75rem, 5vw, 4rem);
}
@media (min-width: 768px) {
    .famledger-tree-relationships {
        margin-bottom: clamp(3.25rem, 5.5vw, 4.5rem);
    }
}

/* Relationships panel — match inner tree cards: visible frame + row dividers */
.famledger-tree-relationships__list {
    font-feature-settings: "kern" 1, "liga" 1;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 4px 14px rgba(15, 23, 42, 0.05);
}
.famledger-tree-relationships__list > .famledger-tree-relationships__row:not(:first-child) {
    border-top: 1px solid #e2e8f0;
}
.famledger-tree-relationships__row {
    background: #fff;
}
.famledger-tree-relationships__row:hover {
    background: #f8fafc;
}
.famledger-tree-rel-remove {
    border: none !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
}
.famledger-tree-rel-remove:hover {
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
}
.famledger-tree-relationship-modal-form .kt-select {
    border-radius: 0.625rem;
}

/*
 * Add Relationship — inline form: tight gap between fields row and actions.
 */
.famledger-tree-add-rel form.famledger-tree-relationship-modal-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
@media (min-width: 640px) {
    .famledger-tree-add-rel form.famledger-tree-relationship-modal-form {
        gap: 1.5rem;
    }
}
.famledger-tree-add-rel .famledger-tree-relationship-modal-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    align-items: center;
    gap: 0.625rem;
    margin: 0;
    padding-top: 1rem;
    border-top: 1px solid rgba(226, 232, 240, 0.85);
}
</style>
@endpush

@push('scripts')
@vite(['resources/js/dashboard-realtime.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    function bindRelationshipFormSubmit(form) {
        if (!form || form.dataset.bound === '1') return;
        form.dataset.bound = '1';
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const userId = form.querySelector('select[name="user_id"]').value;
            const relatedUserId = form.querySelector('select[name="related_user_id"]').value;

            if (!userId || !relatedUserId) {
                if (window.Swal || window.swal) {
                    (window.Swal || window.swal).fire({
                        icon: 'warning',
                        title: 'Validation error',
                        text: 'Please select both family members',
                    });
                } else {
                    alert('Please select both family members');
                }
                return;
            }

            if (userId === relatedUserId) {
                if (window.Swal || window.swal) {
                    (window.Swal || window.swal).fire({
                        icon: 'warning',
                        title: 'Validation error',
                        text: 'First Person and Second Person must be different',
                    });
                } else {
                    alert('First Person and Second Person must be different');
                }
                return;
            }

            const formData = new FormData(form);

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfHidden = form.querySelector('input[name="_token"]');
            const csrfToken = csrfMeta?.content || csrfHidden?.value || null;

            const fetchOptions = {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            if (csrfToken) {
                fetchOptions.headers['X-CSRF-TOKEN'] = csrfToken;
            }

            fetch(form.action, {
                ...fetchOptions,
                credentials: 'same-origin'
            })
            .then(async response => {
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('family-tree: non-OK response', response.status, response.statusText, errorText);
                    throw new Error('Server returned ' + response.status + ': ' + response.statusText);
                }
                const contentType = response.headers.get('Content-Type') || '';
                if (contentType.includes('application/json')) {
                    return response.json();
                }
                const text = await response.text();
                console.error('family-tree: unexpected non-JSON response', contentType, text);
                throw new Error('Unexpected server response (expected JSON)');
            })
            .then(data => {
                if (data.error) {
                    if (window.Swal || window.swal) {
                        (window.Swal || window.swal).fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error,
                        });
                    } else {
                        alert(data.error);
                    }
                } else {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (window.Swal || window.swal) {
                    (window.Swal || window.swal).fire({
                        icon: 'error',
                        title: 'An error occurred',
                        text: error.message || 'Please try again.',
                    });
                } else {
                    alert('An error occurred');
                }
            });
        });
    }

    document.querySelectorAll('form.js-family-tree-relationship-form').forEach(bindRelationshipFormSubmit);

    document.querySelectorAll('.delete-family-relationship').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const url = btn.getAttribute('data-url');
            if (!url) return;

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta?.content || null;

            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            };
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            fetch(url, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers,
            })
            .then(function(response) {
                if (!response.ok) {
                    return response.text().then(function(t) { throw new Error(t || response.statusText); });
                }
                return response.json();
            })
            .then(function() {
                location.reload();
            })
            .catch(function(err) {
                console.error(err);
                if (window.Swal || window.swal) {
                    (window.Swal || window.swal).fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message || 'Could not remove relationship.',
                    });
                } else {
                    alert('Could not remove relationship.');
                }
            });
        });
    });
});
</script>
@endpush
@endsection