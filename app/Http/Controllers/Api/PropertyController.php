<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Property;
use App\Models\PropertyMaintenance;
use App\Models\PropertyDepreciation;
use App\Models\PropertyDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    use AuthorizesFamilyMember;

    /**
     * Assets list (properties).
     */
    public function index(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = Property::with(['category:id,name', 'subcategory:id,name'])
            ->where('family_id', $family->id)
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $perPage = min((int) $request->get('per_page', 20), 50);
        $properties = $query->paginate($perPage);

        return response()->json([
            'properties' => $properties->getCollection()->map(fn (Property $p) => [
                'id' => $p->id,
                'property_code' => $p->property_code,
                'name' => $p->name,
                'category' => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
                'subcategory' => $p->subcategory ? ['id' => $p->subcategory->id, 'name' => $p->subcategory->name] : null,
                'status' => $p->status,
                'purchase_price' => (float) ($p->purchase_price ?? 0),
                'current_estimated_value' => (float) ($p->current_estimated_value ?? 0),
                'currency_code' => $p->currency_code,
                'acquisition_date' => $p->acquisition_date?->format('Y-m-d'),
            ]),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ],
        ]);
    }

    /**
     * Single property detail.
     */
    public function show(Family $family, Property $property): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($property->family_id !== $family->id) {
            abort(404);
        }

        $property->load(['category:id,name', 'subcategory:id,name']);

        return response()->json([
            'id' => $property->id,
            'property_code' => $property->property_code,
            'name' => $property->name,
            'category' => $property->category ? ['id' => $property->category->id, 'name' => $property->category->name] : null,
            'subcategory' => $property->subcategory ? ['id' => $property->subcategory->id, 'name' => $property->subcategory->name] : null,
            'ownership_type' => $property->ownership_type,
            'status' => $property->status,
            'purchase_price' => (float) ($property->purchase_price ?? 0),
            'current_estimated_value' => (float) ($property->current_estimated_value ?? 0),
            'currency_code' => $property->currency_code,
            'acquisition_date' => $property->acquisition_date?->format('Y-m-d'),
            'valuation_date' => $property->valuation_date?->format('Y-m-d'),
            'address' => $property->address,
            'region_city' => $property->region_city,
            'country' => $property->country,
            'notes' => $property->notes,
        ]);
    }

    /**
     * Maintenance records.
     */
    public function maintenance(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = PropertyMaintenance::with('property:id,name,property_code')
            ->whereHas('property', fn ($q) => $q->where('family_id', $family->id))
            ->orderByDesc('service_date');

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $perPage = min((int) $request->get('per_page', 25), 50);
        $items = $query->paginate($perPage);

        return response()->json([
            'maintenances' => $items->getCollection()->map(fn ($m) => [
                'id' => $m->id,
                'property' => $m->property ? ['id' => $m->property->id, 'name' => $m->property->name] : null,
                'service_date' => $m->service_date?->format('Y-m-d'),
                'cost' => (float) ($m->cost ?? 0),
                'service_provider' => $m->service_provider,
                'description' => $m->description,
                'next_due_date' => $m->next_due_date?->format('Y-m-d'),
            ]),
            'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()],
        ]);
    }

    public function storeMaintenance(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'service_date' => ['required', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'service_provider' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:service_date'],
        ]);

        $property = Property::where('family_id', $family->id)->findOrFail($validated['property_id']);

        $maintenance = PropertyMaintenance::create([
            'property_id' => $property->id,
            'service_date' => $validated['service_date'],
            'cost' => $validated['cost'] ?? null,
            'service_provider' => $validated['service_provider'] ?? null,
            'description' => $validated['description'] ?? null,
            'next_due_date' => $validated['next_due_date'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Maintenance record created.',
            'maintenance' => [
                'id' => $maintenance->id,
            ],
        ], 201);
    }

    public function updateMaintenance(Request $request, Family $family, PropertyMaintenance $maintenance): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if (! $maintenance->property || $maintenance->property->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'service_date' => ['required', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'service_provider' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:service_date'],
        ]);

        $maintenance->update($validated);

        return response()->json([
            'message' => 'Maintenance record updated.',
        ]);
    }

    public function destroyMaintenance(Family $family, PropertyMaintenance $maintenance): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if (! $maintenance->property || $maintenance->property->family_id !== $family->id) {
            abort(404);
        }

        $maintenance->delete();

        return response()->json([
            'message' => 'Maintenance record deleted.',
        ]);
    }

    /**
     * Valuation history (from depreciation year-end book values).
     */
    public function valuations(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = PropertyDepreciation::with('property:id,name,property_code')
            ->whereHas('property', fn ($q) => $q->where('family_id', $family->id))
            ->orderByDesc('year');

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $perPage = min((int) $request->get('per_page', 25), 50);
        $items = $query->paginate($perPage);

        return response()->json([
            'valuations' => $items->getCollection()->map(fn ($v) => [
                'id' => $v->id,
                'property' => $v->property ? ['id' => $v->property->id, 'name' => $v->property->name] : null,
                'year' => $v->year,
                'book_value' => (float) ($v->book_value ?? 0),
            ]),
            'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()],
        ]);
    }

    /**
     * Documents list (metadata only).
     */
    public function documents(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = PropertyDocument::with('property:id,name,property_code')
            ->whereHas('property', fn ($q) => $q->where('family_id', $family->id))
            ->orderByDesc('created_at');

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $perPage = min((int) $request->get('per_page', 25), 50);
        $items = $query->paginate($perPage);

        return response()->json([
            'documents' => $items->getCollection()->map(fn ($d) => [
                'id' => $d->id,
                'property' => $d->property ? ['id' => $d->property->id, 'name' => $d->property->name] : null,
                'document_type' => $d->document_type,
                'original_name' => $d->original_name,
                'created_at' => $d->created_at?->toIso8601String(),
            ]),
            'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()],
        ]);
    }

    public function storeDocument(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'document_type' => ['nullable', 'string', 'max:100'],
            'original_name' => ['required', 'string', 'max:255'],
        ]);

        $property = Property::where('family_id', $family->id)->findOrFail($validated['property_id']);

        $document = PropertyDocument::create([
            'property_id' => $property->id,
            'document_type' => $validated['document_type'] ?? null,
            'original_name' => $validated['original_name'],
            'path' => null,
            'size' => null,
            'mime_type' => null,
            'is_archived' => false,
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Document created.',
            'document' => [
                'id' => $document->id,
            ],
        ], 201);
    }

    public function updateDocument(Request $request, Family $family, PropertyDocument $document): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if (! $document->property || $document->property->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'document_type' => ['nullable', 'string', 'max:100'],
            'original_name' => ['required', 'string', 'max:255'],
            'is_archived' => ['nullable', 'boolean'],
        ]);

        $document->update($validated);

        return response()->json([
            'message' => 'Document updated.',
        ]);
    }

    public function destroyDocument(Family $family, PropertyDocument $document): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if (! $document->property || $document->property->family_id !== $family->id) {
            abort(404);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document deleted.',
        ]);
    }

    /**
     * Depreciation records.
     */
    public function depreciation(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = PropertyDepreciation::with('property:id,name,property_code')
            ->whereHas('property', fn ($q) => $q->where('family_id', $family->id))
            ->orderByDesc('year');

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }
        if ($request->filled('year')) {
            $query->where('year', (int) $request->year);
        }

        $perPage = min((int) $request->get('per_page', 25), 50);
        $items = $query->paginate($perPage);

        return response()->json([
            'depreciations' => $items->getCollection()->map(fn ($d) => [
                'id' => $d->id,
                'property' => $d->property ? ['id' => $d->property->id, 'name' => $d->property->name] : null,
                'year' => $d->year,
                'method' => $d->method,
                'depreciation_amount' => (float) ($d->depreciation_amount ?? 0),
                'book_value' => (float) ($d->book_value ?? 0),
            ]),
            'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()],
        ]);
    }

    public function storeDepreciation(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (int) date('Y') + 1],
            'method' => ['nullable', 'string', 'max:100'],
            'depreciation_amount' => ['required', 'numeric', 'min:0'],
            'book_value' => ['required', 'numeric', 'min:0'],
        ]);

        $property = Property::where('family_id', $family->id)->findOrFail($validated['property_id']);

        $dep = PropertyDepreciation::create([
            'property_id' => $property->id,
            'year' => $validated['year'],
            'method' => $validated['method'] ?? null,
            'depreciation_amount' => $validated['depreciation_amount'],
            'book_value' => $validated['book_value'],
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Depreciation record created.',
            'depreciation' => [
                'id' => $dep->id,
            ],
        ], 201);
    }

    public function updateDepreciation(Request $request, Family $family, PropertyDepreciation $depreciation): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if (! $depreciation->property || $depreciation->property->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:1900', 'max:' . (int) date('Y') + 1],
            'method' => ['nullable', 'string', 'max:100'],
            'depreciation_amount' => ['required', 'numeric', 'min:0'],
            'book_value' => ['required', 'numeric', 'min:0'],
        ]);

        $depreciation->update($validated);

        return response()->json([
            'message' => 'Depreciation record updated.',
        ]);
    }

    public function destroyDepreciation(Family $family, PropertyDepreciation $depreciation): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if (! $depreciation->property || $depreciation->property->family_id !== $family->id) {
            abort(404);
        }

        $depreciation->delete();

        return response()->json([
            'message' => 'Depreciation record deleted.',
        ]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'subcategory_id' => ['nullable', 'integer'],
            'ownership_type' => ['nullable', 'string', 'max:100'],
            'acquisition_date' => ['nullable', 'date', 'before_or_equal:today'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'current_estimated_value' => ['nullable', 'numeric', 'min:0'],
            'valuation_date' => ['nullable', 'date'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'region_city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $property = $family->properties()->create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'subcategory_id' => $validated['subcategory_id'] ?? null,
            'ownership_type' => $validated['ownership_type'] ?? null,
            'acquisition_date' => $validated['acquisition_date'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? null,
            'current_estimated_value' => $validated['current_estimated_value'] ?? null,
            'valuation_date' => $validated['valuation_date'] ?? null,
            'currency_code' => $validated['currency_code'] ?? $family->currency_code ?? config('currencies.default', 'TZS'),
            'status' => $validated['status'] ?? 'active',
            'country' => $validated['country'] ?? $family->country,
            'region_city' => $validated['region_city'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Property created.',
            'property' => [
                'id' => $property->id,
                'name' => $property->name,
                'status' => $property->status,
                'currency_code' => $property->currency_code,
            ],
        ], 201);
    }

    public function update(Request $request, Family $family, Property $property): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($property->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'subcategory_id' => ['nullable', 'integer'],
            'ownership_type' => ['nullable', 'string', 'max:100'],
            'acquisition_date' => ['nullable', 'date', 'before_or_equal:today'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'current_estimated_value' => ['nullable', 'numeric', 'min:0'],
            'valuation_date' => ['nullable', 'date'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'region_city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $property->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? $property->category_id,
            'subcategory_id' => $validated['subcategory_id'] ?? $property->subcategory_id,
            'ownership_type' => $validated['ownership_type'] ?? $property->ownership_type,
            'acquisition_date' => $validated['acquisition_date'] ?? $property->acquisition_date,
            'purchase_price' => $validated['purchase_price'] ?? $property->purchase_price,
            'current_estimated_value' => $validated['current_estimated_value'] ?? $property->current_estimated_value,
            'valuation_date' => $validated['valuation_date'] ?? $property->valuation_date,
            'currency_code' => $validated['currency_code'] ?? $property->currency_code,
            'status' => $validated['status'] ?? $property->status,
            'country' => $validated['country'] ?? $property->country,
            'region_city' => $validated['region_city'] ?? $property->region_city,
            'address' => $validated['address'] ?? $property->address,
            'notes' => $validated['notes'] ?? $property->notes,
        ]);

        return response()->json([
            'message' => 'Property updated.',
        ]);
    }

    public function destroy(Family $family, Property $property): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($property->family_id !== $family->id) {
            abort(404);
        }

        $property->delete();

        return response()->json([
            'message' => 'Property deleted.',
        ]);
    }
}
