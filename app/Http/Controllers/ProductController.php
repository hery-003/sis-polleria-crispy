<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category', 'variants')->latest()->paginate(20);
        return inertia('Products/Index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return inertia('Products/Create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');
        $product = Product::create($validated);

        if ($request->variants) {
            foreach ($request->variants as $variant) {
                $product->variants()->create($variant);
            }
        }

        Cache::forget('pos_categories');
        return redirect()->route('products.index')->with('success', 'Producto creado');
    }

    public function show(Product $product)
    {
        return redirect()->route('products.edit', $product);
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $product->load('variants');
        return inertia('Products/Edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
            'remove_image' => 'nullable|boolean',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $validated['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $validated['image'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');
        $product->update($validated);

        if ($request->variants) {
            $existingIds = collect($request->variants)->pluck('id')->filter();
            $product->variants()->whereNotIn('id', $existingIds)->delete();
            
            foreach ($request->variants as $variant) {
                if (isset($variant['id'])) {
                    $product->variants()->where('id', $variant['id'])->update(collect($variant)->except('id')->toArray());
                } else {
                    $product->variants()->create($variant);
                }
            }
        }

        Cache::forget('pos_categories');
        return redirect()->route('products.index')->with('success', 'Producto actualizado');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->variants()->delete();
        $product->delete();
        Cache::forget('pos_categories');
        return redirect()->route('products.index')->with('success', 'Producto eliminado');
    }

    public function stockIndex()
    {
        $variants = ProductVariant::with('product.category')
            ->whereNotNull('stock')
            ->orderBy('stock')
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'product' => $v->product->name,
                'category' => $v->product->category->name ?? '',
                'variant' => $v->name,
                'stock' => $v->stock,
                'threshold' => $v->stock_threshold,
            ]);

        return inertia('Products/Stock', [
            'variants' => $variants,
        ]);
    }

    public function stockUpdate(Request $request)
    {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:product_variants,id',
            'updates.*.stock' => 'required|integer|min:0',
        ]);

        foreach ($validated['updates'] as $update) {
            $variant = ProductVariant::find($update['id']);
            $variant->update(['stock' => $update['stock']]);
        }

        Cache::forget('pos_categories');
        return redirect()->back()->with('success', 'Stock actualizado masivamente');
    }
}
