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
}
