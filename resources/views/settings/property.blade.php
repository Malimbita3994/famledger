@extends('layouts.metronic')

@section('title', 'Property configuration')
@section('page_title', 'Property configuration')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('settings.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to settings
    </a>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5 lg:grid-cols-2">
        {{-- Categories --}}
        <div class="kt-card p-5 lg:p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-foreground">Property categories</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">Define categories and subcategories for family properties.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('settings.property.categories.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                @csrf
                <div class="sm:col-span-2 flex flex-col gap-1.5">
                    <label for="category_name" class="kt-form-label text-xs">New category</label>
                    <input id="category_name" type="text" name="name" class="kt-input" placeholder="e.g. Vehicles" required>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label for="parent_id" class="kt-form-label text-xs">Parent (optional)</label>
                    <select id="parent_id" name="parent_id" class="kt-select">
                        <option value="">None</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-3 flex justify-end">
                    <button type="submit" class="kt-btn kt-btn-primary kt-btn-sm">Add category</button>
                </div>
            </form>

            <div class="kt-scrollable-x-auto">
                <table class="kt-table table-auto kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[200px]">Name</th>
                            <th class="min-w-[160px]">Parent</th>
                            <th class="min-w-[80px]">Active</th>
                            <th class="w-[80px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $cat)
                            <tr>
                                <td class="text-sm font-medium text-foreground">{{ $cat->name }}</td>
                                <td class="text-sm text-muted-foreground">
                                    {{ optional($categories->firstWhere('id', $cat->parent_id))->name ?? '—' }}
                                </td>
                                <td>
                                    <span class="kt-badge kt-badge-sm {{ $cat->is_active ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">
                                        {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('settings.property.categories.update', $cat) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="name" value="{{ $cat->name }}">
                                        <input type="hidden" name="parent_id" value="{{ $cat->parent_id }}">
                                        <input type="hidden" name="is_active" value="{{ $cat->is_active ? 0 : 1 }}">
                                        <button type="submit" class="kt-btn kt-btn-ghost kt-btn-xs">
                                            {{ $cat->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('settings.property.categories.destroy', $cat) }}" class="inline js-confirm-delete" data-confirm-title="Delete category?" data-confirm-message="This will remove the category from configuration.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-ghost kt-btn-xs text-destructive">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-sm text-muted-foreground py-4">
                                    No property categories configured yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Attributes --}}
        <div class="kt-card p-5 lg:p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-foreground">Attribute builder</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">Define dynamic attributes for each property category.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('settings.property.attributes.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                @csrf
                <div class="sm:col-span-2 flex flex-col gap-1.5">
                    <label for="attr_name" class="kt-form-label text-xs">Attribute name</label>
                    <input id="attr_name" type="text" name="name" class="kt-input" placeholder="e.g. Registration Number" required>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label for="attr_category_id" class="kt-form-label text-xs">Category</label>
                    <select id="attr_category_id" name="category_id" class="kt-select">
                        <option value="">Any category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label for="attr_data_type" class="kt-form-label text-xs">Data type</label>
                    <select id="attr_data_type" name="data_type" class="kt-select" required>
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="boolean">Boolean</option>
                        <option value="file">File</option>
                        <option value="currency">Currency</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="inline-flex items-center gap-1 text-xs text-muted-foreground">
                        <input type="checkbox" name="is_required" value="1" class="kt-checkbox">
                        Required
                    </label>
                    <label class="inline-flex items-center gap-1 text-xs text-muted-foreground">
                        <input type="checkbox" name="is_searchable" value="1" class="kt-checkbox">
                        Searchable
                    </label>
                    <label class="inline-flex items-center gap-1 text-xs text-muted-foreground">
                        <input type="checkbox" name="is_reportable" value="1" class="kt-checkbox">
                        Reportable
                    </label>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label for="attr_sort_order" class="kt-form-label text-xs">Sort order</label>
                    <input id="attr_sort_order" type="number" name="sort_order" class="kt-input" value="0">
                </div>
                <div class="sm:col-span-3 flex justify-end">
                    <button type="submit" class="kt-btn kt-btn-primary kt-btn-sm">Add attribute</button>
                </div>
            </form>

            <div class="kt-scrollable-x-auto">
                <table class="kt-table table-auto kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[200px]">Attribute</th>
                            <th class="min-w-[140px]">Category</th>
                            <th class="min-w-[120px]">Data type</th>
                            <th class="min-w-[120px]">Flags</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attributes as $attr)
                            <tr>
                                <td class="text-sm font-medium text-foreground">{{ $attr->name }}</td>
                                <td class="text-sm text-muted-foreground">{{ $attr->category->name ?? 'Any' }}</td>
                                <td class="text-sm text-secondary-foreground">{{ ucfirst($attr->data_type) }}</td>
                                <td class="text-xs text-muted-foreground">
                                    @if ($attr->is_required)
                                        <span class="me-2">Required</span>
                                    @endif
                                    @if ($attr->is_searchable)
                                        <span class="me-2">Searchable</span>
                                    @endif
                                    @if ($attr->is_reportable)
                                        <span class="me-2">Reportable</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-sm text-muted-foreground py-4">
                                    No property attributes configured yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

