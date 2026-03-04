@extends('layouts.metronic')

@section('title', 'Contact message')
@section('page_title', 'Contact message')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('admin.contact-messages.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i> Back to contact messages
    </a>

    <div class="kt-card p-5 lg:p-7.5">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-lg font-semibold text-mono">{{ $contact_message->name }}</h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    <a href="mailto:{{ $contact_message->email }}" class="text-primary hover:underline">{{ $contact_message->email }}</a>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-muted-foreground">
                    {{ $contact_message->created_at->format('M j, Y \a\t H:i') }}
                </span>
                @if ($contact_message->read_at)
                    <span class="kt-badge kt-badge-sm kt-badge-success">Read {{ $contact_message->read_at->format('M j') }}</span>
                @else
                    <span class="kt-badge kt-badge-sm kt-badge-warning">New</span>
                @endif

                <form action="{{ route('admin.contact-messages.read-status', $contact_message) }}" method="POST" class="inline-flex">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="state" value="{{ $contact_message->read_at ? 'unread' : 'read' }}">
                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-outline">
                        {{ $contact_message->read_at ? 'Mark as unread' : 'Mark as read' }}
                    </button>
                </form>

                <form action="{{ route('admin.contact-messages.destroy', $contact_message) }}" method="POST" onsubmit="return confirm('Delete this message?');" class="inline-flex">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost text-destructive">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="border-t border-border pt-5">
            <h2 class="text-xs font-semibold tracking-wide text-muted-foreground uppercase mb-3">Message</h2>
            <div class="rounded-2xl border border-border bg-white text-sm sm:text-[15px] leading-relaxed text-slate-800 p-5 sm:p-6 whitespace-pre-wrap shadow-sm">
                {{ $contact_message->message }}
            </div>
        </div>
    </div>
</div>
@endsection
