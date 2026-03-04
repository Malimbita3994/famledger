@extends('layouts.metronic')

@section('title', 'Add Project Funding')
@section('page_title', 'Add Project Funding')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.projects.funding.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to projects funding
    </a>

    <form action="{{ route('families.projects.funding.store', $family) }}" method="POST">
        @csrf
        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Add funding to project</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">Transfer money from a family wallet to the project. A dedicated project wallet will be created if needed.</p>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="project_id" class="kt-form-label max-w-56">Project <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="project_id" id="project_id" required class="kt-select">
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ ($selectedProject && $selectedProject->id === $p->id) ? 'selected' : '' }}>
                                        {{ $p->name }} ({{ number_format($p->fundings_sum_amount ?? 0, 0) }} / {{ number_format($p->planned_budget, 0) }} {{ $p->currency_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="wallet_id" class="kt-form-label max-w-56">Source wallet <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="wallet_id" id="wallet_id" required class="kt-select">
                                @foreach($wallets as $w)
                                    <option value="{{ $w->id }}" {{ old('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('wallet_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="amount" class="kt-form-label max-w-56">Amount <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required placeholder="0.00" class="kt-input" />
                            @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="funding_date" class="kt-form-label max-w-56">Funding date <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="date" name="funding_date" id="funding_date" value="{{ old('funding_date', now()->format('Y-m-d')) }}" required class="kt-input" />
                            @error('funding_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="source_type" class="kt-form-label max-w-56">Source type</label>
                        <div class="grow">
                            <select name="source_type" id="source_type" class="kt-select">
                                @foreach($sourceTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('source_type', 'transfer') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="reference" class="kt-form-label max-w-56">Reference</label>
                        <div class="grow">
                            <input type="text" name="reference" id="reference" value="{{ old('reference') }}" placeholder="Optional" class="kt-input" />
                            @error('reference')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.projects.funding.index', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary"><i class="ki-filled ki-wallet"></i> Add funding</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@push('scripts')
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const firstError = @json($errors->first());
                if (window.Swal && firstError) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Funding failed',
                        text: firstError,
                        confirmButtonText: 'OK',
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
            });
        </script>
    @endif
@endpush
@endsection
