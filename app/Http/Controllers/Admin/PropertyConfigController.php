<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyAttribute;
use App\Models\PropertyAttributeOption;
use App\Models\PropertyCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PropertyConfigController extends Controller
{
    public function index(): View
    {
        $categories = PropertyCategory::orderBy('name')->get();

        $attributes = PropertyAttribute::with('category')
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('settings.property', [
            'categories' => $categories,
            'attributes' => $attributes,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:property_categories,id'],
        ]);

        $slug = Str::slug($validated['name'], '_');

        PropertyCategory::create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'slug' => $slug,
            'is_active' => true,
        ]);

        return redirect()->route('settings.property.index')->with('success', 'Property category created.');
    }

    public function updateCategory(Request $request, PropertyCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:property_categories,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => $validated['is_active'] ?? $category->is_active,
        ]);

        return redirect()->route('settings.property.index')->with('success', 'Property category updated.');
    }

    public function destroyCategory(PropertyCategory $category)
    {
        $category->delete();

        return redirect()->route('settings.property.index')->with('success', 'Property category deleted.');
    }

    public function storeAttribute(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:property_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'data_type' => ['required', 'string', 'max:50'],
            'is_required' => ['nullable', 'boolean'],
            'is_searchable' => ['nullable', 'boolean'],
            'is_reportable' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = Str::slug($validated['name'], '_');

        PropertyAttribute::create([
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'data_type' => $validated['data_type'],
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'is_searchable' => (bool) ($validated['is_searchable'] ?? false),
            'is_reportable' => (bool) ($validated['is_reportable'] ?? false),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('settings.property.index')->with('success', 'Property attribute created.');
    }

    public function updateAttribute(Request $request, PropertyAttribute $attribute)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'data_type' => ['required', 'string', 'max:50'],
            'is_required' => ['nullable', 'boolean'],
            'is_searchable' => ['nullable', 'boolean'],
            'is_reportable' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $attribute->update([
            'name' => $validated['name'],
            'data_type' => $validated['data_type'],
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'is_searchable' => (bool) ($validated['is_searchable'] ?? false),
            'is_reportable' => (bool) ($validated['is_reportable'] ?? false),
            'sort_order' => $validated['sort_order'] ?? $attribute->sort_order,
        ]);

        return redirect()->route('settings.property.index')->with('success', 'Property attribute updated.');
    }

    public function destroyAttribute(PropertyAttribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('settings.property.index')->with('success', 'Property attribute deleted.');
    }

    public function storeOption(Request $request, PropertyAttribute $attribute)
    {
        $validated = $request->validate([
            'value' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        PropertyAttributeOption::create([
            'attribute_id' => $attribute->id,
            'value' => $validated['value'],
            'label' => $validated['label'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('settings.property.index')->with('success', 'Attribute option added.');
    }

    public function destroyOption(PropertyAttributeOption $option)
    {
        $option->delete();

        return redirect()->route('settings.property.index')->with('success', 'Attribute option deleted.');
    }
}

