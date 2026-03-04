@extends('layouts.metronic')

@section('title', 'Property Documents')
@section('page_title', 'Property Documents')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.properties.assets', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to assets list
    </a>

    <style>
    .documents-main-row {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .documents-main-row .documents-main-col {
        width: 100%;
    }

    .documents-filter-row {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }

    .documents-filter-row .documents-filter-col {
        width: 100%;
    }

    @media (min-width: 900px) {
        .documents-main-row {
            flex-direction: row;
        }

        .documents-main-row .documents-main-col {
            flex: 1 1 0;
        }

        .documents-filter-row {
            flex-direction: row;
            align-items: flex-end;
        }

        .documents-filter-row .documents-filter-col {
            flex: 1 1 0;
        }
    }
    </style>

    <div class="kt-card p-5 lg:p-7.5 mb-5">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-lg font-semibold text-mono">Documents</h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Store title deeds, contracts and other important documents for this family&rsquo;s properties.
                </p>
            </div>
            <form method="GET" class="documents-filter-row">
                <div class="documents-filter-col">
                    <select name="property_id" class="kt-select w-full">
                        <option value="">All properties</option>
                        @foreach ($properties as $prop)
                            <option value="{{ $prop->id }}" @selected(($filters['property_id'] ?? null) == $prop->id)>
                                {{ $prop->property_code }} · {{ $prop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="documents-filter-col">
                    <select name="document_type" class="kt-select w-full">
                        <option value="">All types</option>
                        @foreach (['title' => 'Title deed / ownership', 'id' => 'ID / passport', 'contract' => 'Contract', 'insurance' => 'Insurance', 'other' => 'Other'] as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['document_type'] ?? null) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="documents-filter-col">
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name or type" class="kt-input w-full">
                </div>
                <div class="documents-filter-col flex justify-end">
                    <button type="submit" class="kt-btn kt-btn-outline kt-btn-sm">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <form method="POST" action="{{ route('families.properties.documents.store', $family) }}" enctype="multipart/form-data" class="grid gap-4 lg:gap-5">
            @csrf
            <div class="documents-main-row">
                <div class="documents-main-col grid gap-1.5">
                    <label for="property_id_upload" class="kt-form-label text-xs">Property <span class="text-destructive">*</span></label>
                    <select id="property_id_upload" name="property_id" class="kt-select">
                        <option value="">Select property</option>
                        @foreach ($properties as $prop)
                            <option value="{{ $prop->id }}" @selected(old('property_id') == $prop->id)>
                                {{ $prop->property_code }} · {{ $prop->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('property_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
                <div class="documents-main-col grid gap-1.5">
                    <label for="document_type" class="kt-form-label text-xs">Document type</label>
                    <select id="document_type" name="document_type" class="kt-select">
                        <option value="">Select type</option>
                        @foreach (['title' => 'Title deed / ownership', 'id' => 'ID / passport', 'contract' => 'Contract', 'insurance' => 'Insurance', 'other' => 'Other'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('document_type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('document_type')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
                <div class="documents-main-col grid gap-1.5">
                    <label for="file" class="kt-form-label text-xs">File <span class="text-destructive">*</span></label>
                    <input id="file" type="file" name="file" class="kt-input">
                    @error('file')<p class="kt-form-message">{{ $message }}</p>@enderror
                </div>
                <div class="documents-main-col grid gap-1.5">
                    <label for="description" class="kt-form-label text-xs">Description (optional)</label>
                    <input id="description" type="text" name="description" class="kt-input" value="{{ old('description') }}">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="kt-btn kt-btn-primary kt-btn-sm">
                    <i class="ki-filled ki-upload me-1"></i>
                    Upload document
                </button>
            </div>
        </form>
    </div>

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-content p-0">
            @if ($documents->isEmpty())
                <div class="py-10 text-center text-muted-foreground">
                    <i class="ki-filled ki-file text-4xl mb-2"></i>
                    <p class="text-sm">No documents uploaded yet.</p>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[170px]">Property</th>
                                <th class="min-w-[130px]">Type</th>
                                <th class="min-w-[220px]">File</th>
                                <th class="min-w-[120px]">Uploaded on</th>
                                <th class="min-w-[90px]">Size</th>
                                <th class="w-[80px]">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($documents as $doc)
                                <tr>
                                    <td class="text-sm">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-foreground">{{ $doc->property->name ?? '—' }}</span>
                                            <span class="text-xs text-muted-foreground">{{ $doc->property->property_code ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-sm">
                                        @php
                                            $labels = ['title' => 'Title deed / ownership', 'id' => 'ID / passport', 'contract' => 'Contract', 'insurance' => 'Insurance', 'other' => 'Other'];
                                            $typeKey = $doc->document_type ?? 'other';
                                        @endphp
                                        {{ $labels[$typeKey] ?? ucfirst($doc->document_type ?? 'Other') }}
                                    </td>
                                    <td class="text-sm">
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($doc->path) }}" target="_blank" class="kt-link">
                                            {{ $doc->original_name }}
                                        </a>
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        {{ $doc->created_at?->format('Y-m-d') }}
                                    </td>
                                    <td class="text-sm tabular-nums">
                                        @if ($doc->size)
                                            {{ number_format($doc->size / 1024, 1) }} KB
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-sm">
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($doc->path) }}" target="_blank" class="kt-btn kt-btn-xs kt-btn-outline">
                                            <i class="ki-filled ki-exit-up text-xs me-1"></i>
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3 border-t border-border">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if ($errors->any())
<script>
 document.addEventListener('DOMContentLoaded', function () {
  if (typeof Swal === 'undefined') {
   return;
  }
  Swal.fire({
   icon: 'error',
   title: 'Upload failed',
   text: @json($errors->first()),
   confirmButtonText: 'OK',
   confirmButtonColor: '#2563eb'
  });
 });
</script>
@endif
@endpush
