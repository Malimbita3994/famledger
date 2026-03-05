@extends('layouts.metronic')

@section('title', __('Audit trail'))
@section('page_title', __('Audit trail'))

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">{{ __('Audit trail') }}</h1>
            <p class="text-sm text-muted-foreground mt-0.5">{{ __('Application and database activity for this family. Filter by date, user, type or action.') }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-8">
        <div class="kt-card-header flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="kt-card-title text-sm">{{ __('Filter activity') }}</h3>
            </div>
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Auditor'))
            <a href="{{ route('settings.audit-log') }}" class="kt-btn kt-btn-xs kt-btn-outline shrink-0">
                <i class="ki-filled ki-chart-line text-sm"></i>
                {{ __('Whole system audit') }}
            </a>
            @endif
        </div>
        <div class="kt-card-content pt-4">
            <form method="get" action="{{ route('families.audit-trail.index', $family) }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">{{ __('From') }}</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">{{ __('To') }}</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">{{ __('Member') }}</label>
                    <select name="user_id" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[120px] max-w-[180px]">
                        <option value="">{{ __('All members') }}</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">{{ __('Type') }}</label>
                    <select name="type" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                        <option value="">{{ __('All') }}</option>
                        <option value="application" {{ request('type') === 'application' ? 'selected' : '' }}>{{ __('Application') }}</option>
                        <option value="database" {{ request('type') === 'database' ? 'selected' : '' }}>{{ __('Database') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">{{ __('Action') }}</label>
                    <select name="action" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                        <option value="">{{ __('All') }}</option>
                        <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>{{ __('Login') }}</option>
                        <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>{{ __('Logout') }}</option>
                        <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>{{ __('Created') }}</option>
                        <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>{{ __('Updated') }}</option>
                        <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>{{ __('Deleted') }}</option>
                    </select>
                </div>
                <button type="submit" class="kt-btn kt-btn-primary">{{ __('Apply') }}</button>
                <a href="{{ route('families.audit-trail.index', $family) }}" class="kt-btn kt-btn-ghost">{{ __('Reset') }}</a>
            </form>
        </div>
    </div>

    {{-- Audit table --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mt-4">
        <div class="kt-card-header items-center justify-between gap-3">
            <h3 class="kt-card-title text-sm">{{ __('Recent activity') }}</h3>
            <div class="flex items-center gap-2">
                <span class="text-xs text-muted-foreground">{{ $logs->total() }} {{ __('entries') }}</span>
                <a href="{{ route('families.audit-trail.export', ['family' => $family] + request()->query()) }}" class="kt-btn kt-btn-xs kt-btn-outline" target="_blank" rel="noopener">
                    <i class="ki-filled ki-file-down text-sm"></i>
                    {{ __('Export PDF') }}
                </a>
            </div>
        </div>
        <div class="kt-card-content px-0 pb-3">
            <div class="kt-scrollable-x-auto overflow-x-auto">
                <table class="kt-table align-middle text-xs text-secondary-foreground w-full">
                    <thead>
                        <tr class="bg-accent/40">
                            <th class="px-4 py-2 text-start font-medium min-w-[140px]">{{ __('When') }}</th>
                            <th class="px-4 py-2 text-start font-medium min-w-[120px]">{{ __('Who') }}</th>
                            <th class="px-4 py-2 text-start font-medium min-w-[100px]">{{ __('Type') }}</th>
                            <th class="px-4 py-2 text-start font-medium min-w-[80px]">{{ __('Action') }}</th>
                            <th class="px-4 py-2 text-start font-medium min-w-[100px]">{{ __('Area') }}</th>
                            <th class="px-4 py-2 text-start font-medium">{{ __('Description') }}</th>
                            <th class="px-4 py-2 text-end font-medium min-w-[90px]">{{ __('IP') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="border-b border-border last:border-0 hover:bg-accent/20">
                            <td class="px-4 py-2 align-top whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-medium text-mono text-xs">{{ $log->created_at->format('Y-m-d · H:i') }}</span>
                                    <span class="text-[11px] text-muted-foreground">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2 align-top">
                                @if($log->user)
                                    <div class="flex flex-col">
                                        <span class="text-xs font-medium text-mono">{{ $log->user->name }}</span>
                                        <span class="text-[11px] text-muted-foreground">{{ $log->user->email }}</span>
                                    </div>
                                @else
                                    <span class="text-[11px] text-muted-foreground">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 align-top">
                                <span class="kt-badge kt-badge-xs {{ $log->type === 'database' ? 'kt-badge-outline kt-badge-primary' : 'kt-badge-outline kt-badge-secondary' }}">
                                    {{ $log->type === 'database' ? __('Database') : __('Application') }}
                                </span>
                            </td>
                            <td class="px-4 py-2 align-top">
                                <span class="text-xs font-medium text-foreground">{{ $log->action }}</span>
                            </td>
                            <td class="px-4 py-2 align-top">
                                <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-primary">{{ $log->area }}</span>
                            </td>
                            <td class="px-4 py-2 align-top max-w-xs">
                                <span class="text-xs text-foreground line-clamp-2" title="{{ $log->description }}">{{ $log->description ?? '—' }}</span>
                                @if($log->type === 'database' && $log->properties)
                                    @php
                                        $props = $log->properties;
                                        $old = $props['old_values'] ?? [];
                                        $new = $props['new_values'] ?? [];
                                    @endphp
                                    @if(!empty($new) && ($log->action === 'updated'))
                                        <div class="text-[11px] text-muted-foreground mt-0.5">
                                            @foreach(array_slice(array_keys($new), 0, 3) as $key)
                                                {{ $key }}: {{ is_scalar($old[$key] ?? null) ? $old[$key] : '…' }} → {{ is_scalar($new[$key] ?? null) ? $new[$key] : '…' }}{{ $loop->last ? '' : '; ' }}
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-2 align-top text-end">
                                <span class="text-[11px] text-muted-foreground">{{ $log->ip ?? '—' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground text-sm">{{ __('No audit entries yet. Activity will appear here.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="kt-card-footer justify-between items-center flex-wrap gap-3 px-4 pt-3">
                <span class="text-xs text-muted-foreground">{{ __('Showing') }} {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} {{ __('of') }} {{ $logs->total() }}</span>
                <div class="flex items-center gap-2">
                    @if($logs->onFirstPage())
                        <span class="kt-btn kt-btn-xs kt-btn-ghost opacity-50">{{ __('Previous') }}</span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="kt-btn kt-btn-xs kt-btn-outline">{{ __('Previous') }}</a>
                    @endif
                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="kt-btn kt-btn-xs kt-btn-outline">{{ __('Next') }}</a>
                    @else
                        <span class="kt-btn kt-btn-xs kt-btn-ghost opacity-50">{{ __('Next') }}</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
