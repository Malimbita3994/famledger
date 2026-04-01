@extends('layouts.metronic')

@section('title', __('Family Vision Board'))
@section('page_title', __('Family Vision Board'))

@push('styles')
<style>
    .vision-board-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.5rem;
        width: 100%;
    }
    @media (min-width: 640px) {
        .vision-board-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (min-width: 1024px) {
        .vision-board-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (min-width: 1280px) {
        .vision-board-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
    
    .vision-card {
        background: linear-gradient(to bottom, #f4faff 0%, #ffffff 100%);
        border: 1.5px solid #22d3ee; /* cyan-400 */
        border-radius: 0.75rem; /* rounded-xl */
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(34, 211, 238, 0.1);
    }
    
    .vision-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(34, 211, 238, 0.2);
    }

    .vision-card-img-wrapper {
        height: 12rem;
        max-height: 40vh;
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid #bae6fd; /* border-sky-200 */
        background: #f0f9ff;
    }
    
    .vision-card-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        max-width: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.5s ease;
    }
    
    .vision-card:hover .vision-card-img {
        transform: scale(1.05);
    }

    .vision-card-body {
        padding: 1.25rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .vision-card-title {
        font-weight: 700;
        font-size: 1.125rem;
        color: #2563eb; /* text-blue-600 */
        margin-bottom: 0.5rem;
    }

    .vision-card-desc {
        color: #475569; /* text-slate-600 */
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
</style>
@endpush

@section('content')
{{-- Toolbar / Header --}}
<div class="pb-6">
    <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
        <div class="flex flex-col items-start gap-1">
            <h1 class="font-semibold text-2xl text-foreground">Family Vision Board</h1>
            <p class="text-sm text-muted-foreground">This is what we want our family life to look like.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('families.goals.create') }}" class="kt-btn kt-btn-primary shadow-sm">
                <i class="ki-filled ki-plus"></i> Add New Vision
            </a>
        </div>
    </div>
</div>

<div class="kt-container-fixed">
    <div class="vision-board-grid mb-10">
        @forelse($goals as $goal)
            <div class="vision-card group">
                <div class="vision-card-img-wrapper">
                    @php
                        $fallbackImage = "https://images.unsplash.com/photo-1511895426328-dc8714191300?auto=format&fit=crop&q=80&w=600";
                        $titleLower = strtolower($goal->title);
                        if(str_contains($titleLower, 'home') || str_contains($titleLower, 'house')) $fallbackImage = "https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=600";
                        if(str_contains($titleLower, 'travel') || str_contains($titleLower, 'trip')) $fallbackImage = "https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&q=80&w=600";
                        if(str_contains($titleLower, 'money') || str_contains($titleLower, 'save')) $fallbackImage = "https://images.unsplash.com/photo-1553729459-efe14ef6055d?auto=format&fit=crop&q=80&w=600";
                    @endphp
                    <img src="{{ $goal->resolved_image_url ?? $fallbackImage }}" class="vision-card-img" alt="{{ $goal->title }}" onerror="this.onerror=null;this.src='{{ $fallbackImage }}'">
                    
                    <a href="{{ route('families.goals.show', $goal) }}" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <span class="bg-white/90 text-primary px-5 py-2.5 rounded-full font-bold text-sm shadow-lg group-hover:bg-white transition-transform pointer-events-none">
                            {{ __('View details') }}
                        </span>
                    </a>

                    @if($goal->status === 'completed')
                        <div class="absolute top-3 right-3 bg-green-500 text-white p-1.5 rounded-full shadow-md border-2 border-white">
                            <i class="ki-filled ki-check text-sm"></i>
                        </div>
                    @endif
                </div>

                <div class="vision-card-body">
                    <h2 class="vision-card-title line-clamp-1">{{ $goal->title }}</h2>
                    <p class="vision-card-desc line-clamp-3 flex-grow">
                        {{ $goal->description ?? __('Visualizing our family achievement.') }}
                    </p>
                    
                    <div class="mt-4 pt-4 border-t border-sky-100">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Progress</span>
                            <span class="text-[11px] font-bold text-blue-600">{{ $goal->progress }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-1000" style="width: {{ $goal->progress }}%;"></div>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('families.goals.show', $goal) }}" class="kt-btn kt-btn-sm kt-btn-primary flex-1 min-w-[6rem] justify-center">{{ __('View') }}</a>
                        <a href="{{ route('families.goals.edit', $goal) }}" class="kt-btn kt-btn-sm kt-btn-outline flex-1 min-w-[6rem] justify-center">{{ __('Edit') }}</a>
                    </div>
                </div>
            </div>
        @empty
            <!-- Sample Cards if no goals -->
            @php
                $samples = [
                    ['title' => 'Home & Living', 'desc' => 'Build a modern, peaceful home with comfort and security.', 'img' => 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=600'],
                    ['title' => 'Financial Stability', 'desc' => 'Grow wealth through smart savings and multiple income streams.', 'img' => 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?auto=format&fit=crop&q=80&w=600'],
                    ['title' => 'Family Bond', 'desc' => 'Strengthen relationships through shared values and unity.', 'img' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?auto=format&fit=crop&q=80&w=600'],
                    ['title' => 'Education', 'desc' => 'Ensure continuous learning and skill development.', 'img' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&q=80&w=600'],
                ];
            @endphp

            @foreach($samples as $sample)
            <div class="vision-card opacity-80">
                <div class="vision-card-img-wrapper">
                    <!-- Added onerror to simulate the user's broken image visual gracefully if Unsplash is blocked -->
                    <img src="{{ $sample['img'] }}" class="vision-card-img" alt="{{ $sample['title'] }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100%\' height=\'100%\'><rect width=\'100%\' height=\'100%\' fill=\'%23f0f9ff\'/></svg>'">
                </div>
                <div class="vision-card-body">
                    <h2 class="vision-card-title">{{ $sample['title'] }}</h2>
                    <p class="vision-card-desc flex-grow">
                        {{ $sample['desc'] }}
                    </p>
                </div>
            </div>
            @endforeach
        @endforelse
    </div>
</div>
@endsection
