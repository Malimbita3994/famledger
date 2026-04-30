@extends('layouts.metronic')

@section('title', __('Family Vision Board'))
@section('page_title', __('Family Vision Board'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet"/>
<style>
    .vision-board-page {
        --vb-dark: #2f2f2f;
        --vb-yellow: #e9cf5c;
        --vb-yellow-bg: #f7f1d4;
        --vb-peach: #f0e0d4;
        --vb-mist: #e6e6e2;
        --vb-white: #ffffff;
        --vb-ink: #1a1a1a;
        --vb-muted: #3a3a3a;
        font-family: 'Fraunces', 'Source Serif 4', Georgia, 'Times New Roman', serif;
    }

    .vision-board-shell {
        background: linear-gradient(160deg, #eceae6 0%, #e0ddd8 100%);
        border-radius: 1rem;
        padding: 3px;
        overflow: hidden;
        box-shadow:
            0 1px 0 rgba(255, 255, 255, 0.75) inset,
            0 12px 40px -12px rgba(15, 23, 42, 0.12);
    }

    .vision-board-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 3px;
        width: 100%;
        min-height: 12rem;
        background: var(--vb-white);
        overflow: hidden;
    }

    @media (min-width: 640px) {
        .vision-board-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-auto-rows: minmax(9rem, auto);
        }
    }

    @media (min-width: 1024px) {
        .vision-board-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-auto-rows: minmax(8.5rem, auto);
        }

        .vision-board-intro {
            grid-column: span 1;
            grid-row: span 2;
            min-height: 18rem;
        }

        .vision-tile--tall {
            grid-row: span 2;
            min-height: 17rem;
        }
    }

    @media (min-width: 1280px) {
        .vision-board-intro {
            min-height: 20rem;
        }
    }

    .vision-board-intro {
        background: var(--vb-dark);
        color: var(--vb-white);
        padding: 1.75rem 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 0.75rem;
        position: relative;
        overflow: hidden;
    }

    .vision-board-intro::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(233, 207, 92, 0.12), transparent 55%);
        pointer-events: none;
    }

    .vision-board-intro-badge {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        background: var(--vb-yellow);
        box-shadow: 0 0 0 3px rgba(233, 207, 92, 0.25);
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .vision-board-intro h2 {
        font-family: inherit;
        font-size: clamp(1.35rem, 2.8vw, 1.85rem);
        font-weight: 700;
        letter-spacing: 0.06em;
        color: var(--vb-yellow);
        margin: 0;
        line-height: 1.15;
        position: relative;
        z-index: 1;
    }

    .vision-board-intro p {
        font-family: 'Source Serif 4', Georgia, serif;
        font-size: 0.95rem;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.88);
        margin: 0;
        max-width: 22rem;
        position: relative;
        z-index: 1;
    }

    .vision-toolbar {
        font-family: ui-sans-serif, system-ui, sans-serif;
    }

    .vision-tile {
        position: relative;
        display: flex;
        flex-direction: column;
        min-height: 11rem;
        min-width: 0;
        overflow: hidden;
        isolation: isolate;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .vision-tile:hover {
        box-shadow: 0 18px 38px -18px rgba(15, 23, 42, 0.22);
    }

    .vision-tile--tone-yellow { background: var(--vb-yellow-bg); }
    .vision-tile--tone-peach { background: var(--vb-peach); }
    .vision-tile--tone-mist { background: var(--vb-mist); }

    .vision-tile--tone-yellow .vision-tile-title { color: #b8860b; }
    .vision-tile--tone-peach .vision-tile-title { color: #a65d3a; }
    .vision-tile--tone-mist .vision-tile-title { color: var(--vb-ink); }

    /* Background image avoids Metronic’s global `img { max-width:100%; height:auto }` fighting layout and leaking into adjacent grid cells. */
    .vision-tile-media {
        position: relative;
        flex: 0 0 auto;
        height: 7.75rem;
        max-height: 7.75rem;
        min-height: 0;
        overflow: hidden;
        background-color: rgba(0, 0, 0, 0.06);
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
        contain: paint;
    }

    .vision-tile--no-image .vision-tile-media {
        display: none;
    }

    .vision-tile--no-image .vision-tile-inner {
        flex: 1;
        justify-content: center;
    }

    .vision-tile-inner {
        flex: 1 1 auto;
        min-height: 0;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        text-align: center;
        padding: 1.25rem 1.25rem 1.1rem;
        gap: 0.5rem;
    }

    .vision-tile-title {
        font-family: inherit;
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
        word-break: break-word;
    }

    .vision-tile-desc {
        font-family: 'Source Serif 4', Georgia, serif;
        font-size: 0.875rem;
        line-height: 1.45;
        color: var(--vb-muted);
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .vision-tile-meta {
        width: 100%;
        margin-top: auto;
        padding-top: 0.75rem;
        border-top: 1px solid rgba(0, 0, 0, 0.06);
    }

    .vision-tile-progress-label {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        font-family: ui-sans-serif, system-ui, sans-serif;
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(0, 0, 0, 0.38);
        margin-bottom: 0.35rem;
    }

    .vision-tile-progress-label span:last-child {
        color: rgba(0, 0, 0, 0.55);
        font-size: 0.7rem;
    }

    .vision-tile-progress-track {
        height: 5px;
        border-radius: 9999px;
        background: rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .vision-tile-progress-fill {
        height: 100%;
        border-radius: 9999px;
        background: linear-gradient(90deg, #6b5a3e, #a67c52);
        transition: width 0.6s ease;
    }

    .vision-tile-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        justify-content: center;
        margin-top: 0.65rem;
    }

    .vision-tile-actions .kt-btn {
        font-family: ui-sans-serif, system-ui, sans-serif;
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
    }

    .vision-tile-done {
        position: absolute;
        top: 0.6rem;
        right: 0.6rem;
        z-index: 3;
        width: 1.65rem;
        height: 1.65rem;
        border-radius: 9999px;
        background: #16a34a;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }

    .vision-tile-done i {
        font-size: 0.75rem;
    }

    .vision-empty-hint {
        font-family: ui-sans-serif, system-ui, sans-serif;
        grid-column: 1 / -1;
        text-align: center;
        padding: 2rem 1rem;
        color: var(--text-muted-foreground, #64748b);
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="vision-board-page">
    {{-- Toolbar --}}
    <div class="vision-toolbar pb-6">
        <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
            <div class="flex flex-col items-start gap-1">
                <h1 class="font-semibold text-2xl text-foreground">Family Vision Board</h1>
                <p class="text-sm text-muted-foreground">The things we want to accomplish together.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('families.goals.create') }}" class="kt-btn kt-btn-primary shadow-sm">
                    <i class="ki-filled ki-plus"></i> {{ __('Add vision') }}
                </a>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed">
        <div class="vision-board-shell mb-10">
            <div class="vision-board-grid">
                <div class="vision-board-intro">
                    <div class="vision-board-intro-badge" aria-hidden="true"></div>
                    <h2>{{ __('FAMILY VISION BOARD') }}</h2>
                    <p>{{ __('The things that we wish to accomplish together as a family.') }}</p>
                </div>

                @php
                    $tones = ['yellow', 'peach', 'mist'];
                    $fallbackImage = "https://images.unsplash.com/photo-1511895426328-dc8714191300?auto=format&fit=crop&q=80&w=600";
                    $imgByKeyword = [
                        'home' => "https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=600",
                        'house' => "https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=600",
                        'travel' => "https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&q=80&w=600",
                        'trip' => "https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&q=80&w=600",
                        'money' => "https://images.unsplash.com/photo-1553729459-efe14ef6055d?auto=format&fit=crop&q=80&w=600",
                        'save' => "https://images.unsplash.com/photo-1553729459-efe14ef6055d?auto=format&fit=crop&q=80&w=600",
                        'financial' => "https://images.unsplash.com/photo-1553729459-efe14ef6055d?auto=format&fit=crop&q=80&w=600",
                    ];
                @endphp

                @forelse($goals as $goal)
                    @php
                        $tone = $tones[$loop->index % 3];
                        $titleLower = strtolower($goal->title);
                        $hasImage = (bool) ($goal->resolved_image_url);
                        $img = $goal->resolved_image_url ?? $fallbackImage;
                        if (!$hasImage) {
                            foreach ($imgByKeyword as $kw => $url) {
                                if (str_contains($titleLower, $kw)) {
                                    $img = $url;
                                    break;
                                }
                            }
                        }
                        $tall = ($loop->index % 5 === 1);
                    @endphp
                    <article class="vision-tile vision-tile--tone-{{ $tone }} {{ $hasImage ? '' : 'vision-tile--no-image' }} {{ $tall ? 'vision-tile--tall' : '' }} group">
                        @if($hasImage)
                            {{-- Single-quoted style so json_encode("url") double-quotes do not break the attribute --}}
                            <div class="vision-tile-media" style='background-image: url({{ json_encode($img) }});'>
                                <a href="{{ route('families.goals.show', $goal) }}" class="absolute inset-0 z-[2]" aria-label="{{ __('View') }}: {{ $goal->title }}"></a>
                                @if($goal->status === 'completed')
                                    <div class="vision-tile-done"><i class="ki-filled ki-check"></i></div>
                                @endif
                            </div>
                        @else
                            @if($goal->status === 'completed')
                                <div class="vision-tile-done"><i class="ki-filled ki-check"></i></div>
                            @endif
                        @endif
                        <div class="vision-tile-inner">
                            <h2 class="vision-tile-title">{{ $goal->title }}</h2>
                            <p class="vision-tile-desc">{{ $goal->description ?? __('Visualizing our family achievement.') }}</p>
                            <div class="vision-tile-meta relative z-[2]">
                                <div class="vision-tile-progress-label">
                                    <span>{{ __('Progress') }}</span>
                                    <span>{{ $goal->progress }}%</span>
                                </div>
                                <div class="vision-tile-progress-track">
                                    <div class="vision-tile-progress-fill" style="width: {{ min(100, max(0, (int) $goal->progress)) }}%;"></div>
                                </div>
                                <div class="vision-tile-actions">
                                    <a href="{{ route('families.goals.show', $goal) }}" class="kt-btn kt-btn-sm kt-btn-primary">{{ __('View') }}</a>
                                    <a href="{{ route('families.goals.edit', $goal) }}" class="kt-btn kt-btn-sm kt-btn-outline">{{ __('Edit') }}</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    @php
                        $samples = [
                            ['title' => __('Home & living'), 'desc' => __('Build a calm, secure place we love coming home to.'), 'tone' => 'peach'],
                            ['title' => __('Travel'), 'desc' => __('See new places together and share stories for years.'), 'tone' => 'yellow', 'tall' => true],
                            ['title' => __('Financial stability'), 'desc' => __('Save steadily and stay debt-free as a team.'), 'tone' => 'mist'],
                            ['title' => __('Family bond'), 'desc' => __('Protect Sundays and small rituals that keep us close.'), 'tone' => 'yellow'],
                            ['title' => __('Wellness'), 'desc' => __('Move more, stress less, and cheer each other on.'), 'tone' => 'mist'],
                            ['title' => __('Education'), 'desc' => __('Keep learning—kids and adults—out of curiosity.'), 'tone' => 'peach'],
                        ];
                    @endphp
                    <p class="vision-empty-hint">{{ __('No visions yet—the samples below show how your board will look. Add your first goal to replace them.') }}</p>
                    @foreach($samples as $i => $sample)
                        <article class="vision-tile vision-tile--tone-{{ $sample['tone'] }} vision-tile--no-image {{ !empty($sample['tall']) ? 'vision-tile--tall' : '' }} opacity-90">
                            <div class="vision-tile-inner">
                                <h2 class="vision-tile-title">{{ $sample['title'] }}</h2>
                                <p class="vision-tile-desc">{{ $sample['desc'] }}</p>
                                <div class="vision-tile-meta">
                                    <div class="vision-tile-progress-label">
                                        <span>{{ __('Progress') }}</span>
                                        <span>0%</span>
                                    </div>
                                    <div class="vision-tile-progress-track">
                                        <div class="vision-tile-progress-fill" style="width: 0%;"></div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
