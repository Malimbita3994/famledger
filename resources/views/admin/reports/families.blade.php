@extends('layouts.metronic')

@section('title', 'Family List Report')
@section('page_title', 'Family List Report')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Family List Report</h1>
            <p class="text-sm text-muted-foreground mt-0.5">All families on the platform with their owner and member count. Super Administrator report.</p>
        </div>
    </div>

    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header flex flex-wrap items-center justify-between gap-3">
            <h3 class="kt-card-title text-sm">Families &amp; Owners</h3>
            <button type="button" onclick="window.print()" class="kt-btn kt-btn-sm kt-btn-ghost"><i class="ki-filled ki-printer text-sm mr-1"></i> Print</button>
        </div>
        <div class="kt-card-content p-0">
            {{-- Desktop / tablet table --}}
            <div class="kt-scrollable-x-auto hidden md:block">
                <table class="kt-table table-auto kt-table-border">
                <thead>
                    <tr>
                        <th class="min-w-[120px]">Family</th>
                        <th class="min-w-[120px]">Owner</th>
                        <th class="min-w-[160px]">Email</th>
                        <th class="min-w-[80px] text-center">Members</th>
                        <th class="min-w-[90px]">Status</th>
                        <th class="min-w-[100px]">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($families as $f)
                        @php
                            $owner = $f->creator;
                            $primary = $f->familyMembers->where('is_primary', true)->first();
                            if (!$owner && $primary && $primary->user) {
                                $owner = $primary->user;
                            }
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('families.show', $f) }}" class="font-medium text-primary hover:underline">{{ $f->name }}</a>
                            </td>
                            <td class="text-foreground">{{ $owner ? $owner->name : '—' }}</td>
                            <td class="text-muted-foreground text-sm">{{ $owner && $owner->email ? $owner->email : '—' }}</td>
                            <td class="text-center tabular-nums">{{ $f->familyMembers->count() }}</td>
                            <td>
                                <span class="kt-badge kt-badge-sm {{ $f->status === 'active' ? 'kt-badge-success' : 'kt-badge-outline' }}">{{ ucfirst($f->status ?? '—') }}</span>
                            </td>
                            <td class="text-muted-foreground text-sm">{{ $f->created_at ? $f->created_at->format('M j, Y') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-muted-foreground text-sm">No families found.</td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden p-4 space-y-4">
                @forelse ($families as $f)
                    @php
                        $owner = $f->creator;
                        $primary = $f->familyMembers->where('is_primary', true)->first();
                        if (!$owner && $primary && $primary->user) {
                            $owner = $primary->user;
                        }
                    @endphp
                    <div class="rounded-2xl border border-border bg-background shadow-sm px-5 py-4 flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex flex-col min-w-0">
                                <a href="{{ route('families.show', $f) }}" class="text-sm font-semibold text-foreground hover:text-primary truncate">
                                    {{ $f->name }}
                                </a>
                                @if ($owner)
                                    <span class="text-xs text-secondary-foreground truncate mt-0.5">
                                        {{ $owner->name }}
                                    </span>
                                    @if ($owner->email)
                                        <span class="text-[11px] text-muted-foreground truncate mt-0.5">
                                            {{ $owner->email }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-muted-foreground">No owner set</span>
                                @endif
                            </div>
                            <span class="kt-badge kt-badge-sm {{ $f->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline shrink-0">
                                {{ ucfirst($f->status ?? '—') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-muted-foreground mt-2 pt-2 border-t border-border/60">
                            <span class="pr-2">
                                {{ __('Members:') }}
                                <span class="font-medium text-foreground tabular-nums">{{ $f->familyMembers->count() }}</span>
                            </span>
                            <span class="pl-2 text-right">
                                {{ __('Created:') }}
                                <span class="font-medium text-foreground">
                                    {{ $f->created_at ? $f->created_at->format('M j, Y') : '—' }}
                                </span>
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 px-4 text-center text-muted-foreground text-sm">No families found.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
