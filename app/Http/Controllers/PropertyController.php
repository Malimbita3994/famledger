<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Property;
use App\Models\PropertyAttribute;
use App\Models\PropertyAttributeValue;
use App\Models\PropertyCategory;
use App\Models\PropertyMaintenance;
use App\Models\PropertyDocument;
use App\Models\PropertyValuation;
use App\Models\PropertyDepreciation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PropertyController extends Controller
{
    protected function authorizePropertyManager(Family $family): void
    {
        $userId = auth()->id();
        if (! $userId) {
            abort(403, 'You must be logged in.');
        }

        $member = FamilyMember::where('family_id', $family->id)
            ->where('user_id', $userId)
            ->with('role')
            ->first();

        $roleName = $member && $member->role ? mb_strtolower($member->role->name) : null;

        if (! $member || ! in_array($roleName, ['owner', 'co-owner'], true)) {
            abort(403, 'Only family owners and co-owners can manage properties.');
        }
    }

    public function index(Request $request, Family $family): View
    {
        $this->authorizePropertyManager($family);

        $categories = PropertyCategory::where('is_active', true)->orderBy('name')->get();

        $query = Property::with(['category', 'subcategory'])
            ->where('family_id', $family->id);

        $categoryId = $request->query('category_id');
        $ownership = $request->query('ownership_type');
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        if ($categoryId) {
            $query->where('category_id', (int) $categoryId);
        }
        if ($ownership) {
            $query->where('ownership_type', $ownership);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('property_code', 'like', '%'.$search.'%');
            });
        }

        $base = (clone $query);

        $totalValue = (float) $base->clone()
            ->select(DB::raw('COALESCE(current_estimated_value, purchase_price, 0) as v'))
            ->get()
            ->sum('v');

        $activeCount = (clone $base)->clone()->where('status', 'active')->count();
        $soldCount = (clone $base)->clone()->where('status', 'sold')->count();

        $properties = $query->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('families.properties.assets', [
            'family' => $family,
            'properties' => $properties,
            'categories' => $categories,
            'filters' => [
                'category_id' => $categoryId,
                'ownership_type' => $ownership,
                'status' => $status,
                'q' => $search,
            ],
            'kpis' => [
                'total_value' => $totalValue,
                'active_count' => $activeCount,
                'sold_count' => $soldCount,
            ],
        ]);
    }

    public function create(Request $request, Family $family): View
    {
        $this->authorizePropertyManager($family);

        $categories = PropertyCategory::where('is_active', true)->orderBy('name')->get();
        $categoryId = (int) $request->query('category_id', $categories->first()->id ?? 0);

        $attributes = $categoryId
            ? PropertyAttribute::where('category_id', $categoryId)->orderBy('sort_order')->get()
            : collect();

        $members = $family->familyMembers()->with('user')->get();

        return view('families.properties.create', [
            'family' => $family,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
            'attributes' => $attributes,
            'members' => $members,
        ]);
    }

    public function edit(Family $family, Property $property): View
    {
        $this->authorizePropertyManager($family);

        if ($property->family_id !== $family->id) {
            abort(404);
        }

        $categories = PropertyCategory::where('is_active', true)->orderBy('name')->get();
        $categoryId = $property->category_id ?? ($categories->first()->id ?? 0);

        $attributes = $categoryId
            ? PropertyAttribute::where('category_id', $categoryId)->orderBy('sort_order')->get()
            : collect();

        $members = $family->familyMembers()->with('user')->get();

        return view('families.properties.edit', [
            'family' => $family,
            'property' => $property,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
            'attributes' => $attributes,
            'members' => $members,
        ]);
    }

    public function show(Family $family, Property $property): View
    {
        $this->authorizePropertyManager($family);

        if ($property->family_id !== $family->id) {
            abort(404);
        }

        $property->load(['category', 'subcategory']);

        return view('families.properties.show', [
            'family' => $family,
            'property' => $property,
        ]);
    }

    public function update(Request $request, Family $family, Property $property)
    {
        $this->authorizePropertyManager($family);

        if ($property->family_id !== $family->id) {
            abort(404);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:property_categories,id'],
            'subcategory_id' => ['nullable', 'exists:property_categories,id'],
            'ownership_type' => ['nullable', 'string', 'max:100'],
            'owner_family_member_id' => ['nullable', 'integer'],
            'acquisition_date' => ['nullable', 'date', 'before_or_equal:today'],
            'acquisition_method' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'current_estimated_value' => ['nullable', 'numeric', 'min:0'],
            'valuation_date' => ['nullable', 'date'],
            'currency_code' => ['nullable', 'string', 'max:3'],
            'status' => ['nullable', 'string', 'max:100'],
            'insurance_status' => ['nullable', 'string', 'max:100'],
            'legal_status' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'region_city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'gps_lat' => ['nullable', 'numeric'],
            'gps_lng' => ['nullable', 'numeric'],
            'title_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];

        $categoryId = (int) ($request->input('category_id') ?: $property->category_id);
        if ($categoryId) {
            $requiredAttributes = PropertyAttribute::where('category_id', $categoryId)
                ->where('is_required', true)
                ->get();

            foreach ($requiredAttributes as $attr) {
                $rules['attr_'.$attr->id] = ['required'];
            }
        }

        $validator = Validator::make($request->all(), $rules);
        $validated = $validator->validate();

        $property->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? $property->category_id,
            'subcategory_id' => $validated['subcategory_id'] ?? $property->subcategory_id,
            'ownership_type' => $validated['ownership_type'] ?? null,
            'owner_family_member_id' => $validated['owner_family_member_id'] ?? null,
            'acquisition_date' => $validated['acquisition_date'] ?? null,
            'acquisition_method' => $validated['acquisition_method'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? null,
            'current_estimated_value' => $validated['current_estimated_value'] ?? null,
            'valuation_date' => $validated['valuation_date'] ?? null,
            'currency_code' => $validated['currency_code'] ?? $property->currency_code ?? ($family->currency_code ?? config('currencies.default', 'TZS')),
            'status' => $validated['status'] ?? $property->status,
            'insurance_status' => $validated['insurance_status'] ?? null,
            'legal_status' => $validated['legal_status'] ?? null,
            'country' => $validated['country'] ?? $family->country,
            'region_city' => $validated['region_city'] ?? null,
            'address' => $validated['address'] ?? null,
            'gps_lat' => $validated['gps_lat'] ?? null,
            'gps_lng' => $validated['gps_lng'] ?? null,
            'title_number' => $validated['title_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        if ($categoryId) {
            $attributes = PropertyAttribute::where('category_id', $categoryId)->get();
            foreach ($attributes as $attr) {
                $key = 'attr_' . $attr->id;
                if (! $request->has($key)) {
                    continue;
                }
                $value = $request->input($key);

                $record = PropertyAttributeValue::firstOrNew([
                    'property_id' => $property->id,
                    'attribute_id' => $attr->id,
                ]);

                if ($value === null || $value === '') {
                    if ($record->exists) {
                        $record->delete();
                    }
                    continue;
                }

                $record->value = $value;
                $record->save();
            }
        }

        return redirect()->route('families.properties.show', [$family, $property])->with('success', 'Property updated.');
    }
    public function store(Request $request, Family $family)
    {
        $this->authorizePropertyManager($family);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:property_categories,id'],
            'subcategory_id' => ['nullable', 'exists:property_categories,id'],
            'ownership_type' => ['nullable', 'string', 'max:100'],
            'owner_family_member_id' => ['nullable', 'integer'],
            'acquisition_date' => ['nullable', 'date', 'before_or_equal:today'],
            'acquisition_method' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'current_estimated_value' => ['nullable', 'numeric', 'min:0'],
            'valuation_date' => ['nullable', 'date'],
            'currency_code' => ['nullable', 'string', 'max:3'],
            'status' => ['nullable', 'string', 'max:100'],
            'insurance_status' => ['nullable', 'string', 'max:100'],
            'legal_status' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'region_city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'gps_lat' => ['nullable', 'numeric'],
            'gps_lng' => ['nullable', 'numeric'],
            'title_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];

        $categoryId = (int) $request->input('category_id');
        if ($categoryId) {
            $requiredAttributes = PropertyAttribute::where('category_id', $categoryId)
                ->where('is_required', true)
                ->get();

            foreach ($requiredAttributes as $attr) {
                $rules['attr_'.$attr->id] = ['required'];
            }
        }

        $validator = Validator::make($request->all(), $rules);
        $validated = $validator->validate();

        $propertyCode = 'PROP-' . strtoupper(Str::random(8));

        $property = Property::create([
            'family_id' => $family->id,
            'property_code' => $propertyCode,
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'subcategory_id' => $validated['subcategory_id'] ?? null,
            'ownership_type' => $validated['ownership_type'] ?? null,
            'owner_family_member_id' => $validated['owner_family_member_id'] ?? null,
            'acquisition_date' => $validated['acquisition_date'] ?? null,
            'acquisition_method' => $validated['acquisition_method'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? null,
            'current_estimated_value' => $validated['current_estimated_value'] ?? null,
            'valuation_date' => $validated['valuation_date'] ?? null,
            'currency_code' => $validated['currency_code'] ?? ($family->currency_code ?? config('currencies.default', 'TZS')),
            'status' => $validated['status'] ?? 'active',
            'insurance_status' => $validated['insurance_status'] ?? null,
            'legal_status' => $validated['legal_status'] ?? null,
            'country' => $validated['country'] ?? $family->country,
            'region_city' => $validated['region_city'] ?? null,
            'address' => $validated['address'] ?? null,
            'gps_lat' => $validated['gps_lat'] ?? null,
            'gps_lng' => $validated['gps_lng'] ?? null,
            'title_number' => $validated['title_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Dynamic attributes
        if (! empty($validated['category_id'])) {
            $attributes = PropertyAttribute::where('category_id', $validated['category_id'])->get();
            foreach ($attributes as $attr) {
                $key = 'attr_' . $attr->id;
                if (! $request->has($key)) {
                    continue;
                }
                $value = $request->input($key);
                if ($value === null || $value === '') {
                    continue;
                }
                PropertyAttributeValue::create([
                    'property_id' => $property->id,
                    'attribute_id' => $attr->id,
                    'value' => $value,
                ]);
            }
        }

        return redirect()->route('families.properties.assets', $family)->with('success', 'Property created.');
    }

    public function maintenance(Request $request, Family $family): View
    {
        $this->authorizePropertyManager($family);

        // Only list properties that realistically need maintenance (e.g. buildings, vehicles),
        // excluding assets like bare land.
        $properties = Property::where('family_id', $family->id)
            ->with(['category', 'subcategory'])
            ->orderBy('name')
            ->get()
            ->filter(function (Property $prop) {
                $categoryName = mb_strtolower($prop->category->name ?? '');
                $subcategoryName = mb_strtolower($prop->subcategory->name ?? '');

                // Exclude "Land" category/subcategory from maintenance list
                return $categoryName !== 'land' && $subcategoryName !== 'land';
            })
            ->values();

        $propertyId = (int) $request->query('property_id', 0);
        $from = $request->query('from');
        $to = $request->query('to');

        $query = PropertyMaintenance::with('property')
            ->whereHas('property', function ($q) use ($family) {
                $q->where('family_id', $family->id);
            })
            ->orderByDesc('service_date');

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }
        if ($from) {
            $query->whereDate('service_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('service_date', '<=', $to);
        }

        $maintenances = $query->paginate(25)->withQueryString();

        return view('families.properties.maintenance', [
            'family' => $family,
            'properties' => $properties,
            'maintenances' => $maintenances,
            'filters' => [
                'property_id' => $propertyId ?: null,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    /**
     * Valuation history: derived from depreciation (no manual entry).
     * Each depreciation year's book_value is shown as the estimated value for end-of-year.
     */
    public function valuations(Request $request, Family $family): View
    {
        $this->authorizePropertyManager($family);

        $properties = Property::where('family_id', $family->id)
            ->orderBy('name')
            ->get();

        $propertyId = (int) $request->query('property_id', 0);
        $from = $request->query('from');
        $to = $request->query('to');

        $query = PropertyDepreciation::with('property')
            ->whereHas('property', function ($q) use ($family) {
                $q->where('family_id', $family->id);
            })
            ->orderByDesc('year');

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }
        if ($from) {
            $query->whereRaw('CONCAT(year, ?) >= ?', ['-12-31', $from]);
        }
        if ($to) {
            $query->whereRaw('CONCAT(year, ?) <= ?', ['-12-31', $to]);
        }

        $valuations = $query->paginate(25)->withQueryString();

        return view('families.properties.valuations', [
            'family' => $family,
            'properties' => $properties,
            'valuations' => $valuations,
            'filters' => [
                'property_id' => $propertyId ?: null,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function documents(Family $family): View
    {
        $this->authorizePropertyManager($family);

        $properties = Property::where('family_id', $family->id)
            ->orderBy('name')
            ->get();

        $propertyId = (int) request()->query('property_id', 0);
        $type = request()->query('document_type');
        $search = trim((string) request()->query('q', ''));

        $query = PropertyDocument::with('property')
            ->whereHas('property', function ($q) use ($family) {
                $q->where('family_id', $family->id);
            })
            ->orderByDesc('created_at');

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }
        if ($type) {
            $query->where('document_type', $type);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', '%'.$search.'%')
                  ->orWhere('document_type', 'like', '%'.$search.'%');
            });
        }

        $documents = $query->paginate(25)->withQueryString();

        return view('families.properties.documents', [
            'family' => $family,
            'properties' => $properties,
            'documents' => $documents,
            'filters' => [
                'property_id' => $propertyId ?: null,
                'document_type' => $type ?: null,
                'q' => $search,
            ],
        ]);
    }

    public function depreciation(Request $request, Family $family): View
    {
        $this->authorizePropertyManager($family);

        $properties = Property::where('family_id', $family->id)
            ->with('category:id,name,slug')
            ->orderBy('name')
            ->get();

        $propertyIdsLand = $properties->filter(function ($p) {
            $name = $p->name ?? '';
            $catName = $p->category->name ?? '';

            return stripos($name, 'land') !== false || stripos($catName, 'land') !== false;
        })->pluck('id')->values()->all();

        $propertyId = (int) $request->query('property_id', 0);
        $year = $request->query('year');

        $query = PropertyDepreciation::with('property')
            ->whereHas('property', function ($q) use ($family) {
                $q->where('family_id', $family->id);
            })
            ->orderByDesc('year');

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }
        if ($year) {
            $query->where('year', (int) $year);
        }

        $depreciations = $query->paginate(25)->withQueryString();

        return view('families.properties.depreciation', [
            'family' => $family,
            'properties' => $properties,
            'depreciations' => $depreciations,
            'filters' => [
                'property_id' => $propertyId ?: null,
                'year' => $year,
            ],
            'propertyIdsLand' => $propertyIdsLand,
        ]);
    }

    public function storeDocument(Request $request, Family $family)
    {
        $this->authorizePropertyManager($family);

        $rules = [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'document_type' => ['nullable', 'string', 'max:100'],
            'file' => ['required', 'file', 'max:8192', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,txt'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $first = $validator->errors()->first();

            return back()
                ->withErrors($validator)
                ->with('error', $first)
                ->withInput();
        }

        $validated = $validator->validated();

        // Ensure property belongs to this family
        $property = Property::where('family_id', $family->id)
            ->where('id', $validated['property_id'])
            ->firstOrFail();

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();
        $size = $file->getSize();

        $path = $file->storeAs(
            'property-documents/'.$family->id.'/'.$property->id,
            now()->format('Ymd_His').'_'.$originalName,
            'public'
        );

        PropertyDocument::create([
            'property_id' => $property->id,
            'document_type' => $validated['document_type'] ?? null,
            'original_name' => $originalName,
            'path' => $path,
            'size' => $size,
            'mime_type' => $mime,
            'is_archived' => false,
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()
            ->route('families.properties.documents', $family)
            ->with('success', 'Document uploaded.');
    }

    public function storeValuation(Request $request, Family $family)
    {
        $this->authorizePropertyManager($family);

        $rules = [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'valuation_date' => ['required', 'date', 'before_or_equal:today'],
            'estimated_value' => ['required', 'numeric', 'min:0'],
            'valuator' => ['nullable', 'string', 'max:255'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $first = $validator->errors()->first();

            return back()
                ->withErrors($validator)
                ->with('error', $first)
                ->withInput();
        }

        $validated = $validator->validated();

        $property = Property::where('family_id', $family->id)
            ->where('id', $validated['property_id'])
            ->firstOrFail();

        PropertyValuation::create([
            'property_id' => $property->id,
            'valuation_date' => $validated['valuation_date'],
            'estimated_value' => $validated['estimated_value'],
            'valuator' => $validated['valuator'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('families.properties.valuations', $family)
            ->with('success', 'Valuation recorded.');
    }

    public function storeMaintenance(Request $request, Family $family)
    {
        $this->authorizePropertyManager($family);

        $rules = [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'service_date' => ['required', 'date', 'before_or_equal:today'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'service_provider' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:service_date'],
        ];

        $validated = $request->validate($rules);

        // Ensure the property belongs to this family
        $property = Property::where('family_id', $family->id)
            ->where('id', $validated['property_id'])
            ->firstOrFail();

        PropertyMaintenance::create([
            'property_id' => $property->id,
            'service_date' => $validated['service_date'],
            'cost' => $validated['cost'] ?? null,
            'service_provider' => $validated['service_provider'] ?? null,
            'description' => $validated['description'] ?? null,
            'next_due_date' => $validated['next_due_date'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('families.properties.maintenance', $family)
            ->with('success', 'Maintenance record added.');
    }

    public function storeDepreciation(Request $request, Family $family)
    {
        $this->authorizePropertyManager($family);

        $currentYear = now()->year;

        $rules = [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'method' => ['required', 'string', 'in:straight_line,declining_balance,manual'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.($currentYear + 10)],
            'depreciation_amount' => ['required', 'numeric', 'min:0'],
            'book_value' => ['required', 'numeric', 'min:0'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $first = $validator->errors()->first();

            return back()
                ->withErrors($validator)
                ->with('error', $first)
                ->withInput();
        }

        $validated = $validator->validated();

        $property = Property::where('family_id', $family->id)
            ->where('id', $validated['property_id'])
            ->firstOrFail();

        PropertyDepreciation::updateOrCreate(
            [
                'property_id' => $property->id,
                'year' => $validated['year'],
                'method' => $validated['method'],
            ],
            [
                'depreciation_amount' => $validated['depreciation_amount'],
                'book_value' => $validated['book_value'],
                'created_by' => auth()->id(),
            ]
        );

        return redirect()
            ->route('families.properties.depreciation', $family)
            ->with('success', 'Depreciation updated.');
    }
}

