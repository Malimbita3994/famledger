@extends('layouts.metronic')

@section('title', __('Family Announcements'))
@section('page_title', __('Family Announcements'))

@section('content')
<div class="min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="fin-pulse-eyebrow mb-1.5">{{ __('Family') }}</p>
                <h1 class="fin-pulse-title truncate">{{ __('Announcements') }}</h1>
                <div class="fin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('families.index') }}">{{ __('Families') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium truncate">{{ __('Announcements') }}</span>
                </div>
            </div>
            <div class="shrink-0 w-full sm:w-auto sm:ms-auto flex flex-wrap items-center justify-end gap-2 max-w-full">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#newAnnouncementForm">
                    <i class="ki-filled ki-plus"></i>
                    {{ __('Post Announcement') }}
                </button>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
        <div class="w-full max-w-4xl mx-auto">
            <!-- New Announcement Form -->
            <div id="newAnnouncementForm" class="collapse mb-8">
                <div class="bg-card rounded-xl border border-primary/20 shadow-sm p-5">
                    <form action="{{ route('families.announcements.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label font-bold mb-2">Message</label>
                            <textarea name="message" class="form-control form-control-solid" rows="3" placeholder="What's on your mind?" required></textarea>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="form-check form-check-custom form-check-solid flex items-center gap-2 cursor-pointer">
                                <input class="form-check-input" type="checkbox" name="pinned" value="1" />
                                <span class="form-check-label text-sm font-medium">Pin this announcement</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#newAnnouncementForm">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm">Post to Board</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @if(isset($announcements) && $announcements->count() > 0)
                <div class="flex flex-col gap-4">
                    @foreach($announcements as $announcement)
                        <div class="bg-card rounded-xl border border-border/60 shadow-sm p-5 relative {{ $announcement->pinned ? 'border-amber-400 bg-amber-50/10' : '' }}">
                            @if($announcement->pinned)
                                <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-amber-100 border-2 border-amber-400 flex items-center justify-center shadow-sm">
                                    <i class="ki-filled ki-pin text-amber-500 font-bold"></i>
                                </div>
                            @endif
                            
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold shrink-0">
                                    {{ substr($announcement->user->name ?? 'F', 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-semibold text-sm text-foreground">{{ $announcement->user->name ?? 'Family Member' }}</h4>
                                        <span class="text-xs text-muted-foreground">{{ $announcement->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm mt-2 text-foreground/90 whitespace-pre-wrap">{{ $announcement->message }}</p>
                                    
                                    <div class="mt-4 pt-3 flex items-center gap-4 text-xs font-medium text-muted-foreground border-t border-border/40">
                                        @if($announcement->user_id === auth()->id())
                                            <form action="{{ route('families.announcements.pin', $announcement->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex items-center gap-1.5 hover:text-amber-500 transition-colors">
                                                    <i class="ki-filled ki-pin text-base"></i> {{ $announcement->pinned ? 'Unpin' : 'Pin' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('families.announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Delete this announcement?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center gap-1.5 hover:text-red-500 transition-colors">
                                                    <i class="ki-filled ki-trash text-base"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                        <button class="flex items-center gap-1.5 hover:text-primary transition-colors ms-auto">
                                            <i class="ki-filled ki-heart text-base"></i> {{ $announcement->reactions->count() }} Likes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-card rounded-xl border border-dashed border-border/60 p-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-message-text-2 text-5xl mb-4 text-muted-foreground/50"></i>
                    <h3 class="text-lg font-medium text-foreground mb-2">No Announcements</h3>
                    <p class="text-sm max-w-sm mx-auto mb-6">Keep everyone in the loop! Share news, upcoming meetings, or important changes.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
