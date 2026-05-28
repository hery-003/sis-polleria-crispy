<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with('category', 'variants')->get();
    }

    public function show(Product $product)
    {
        return $product->load('category', 'variants');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('image', 'variants');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                $product->variants()->create($variant);
            }
        }

        return response()->json($product->load('category', 'variants'), 201);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'exists:categories,id',
            'name' => 'string|max:255',
            'slug' => 'string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return response()->json($product->load('category', 'variants'));
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->whereHas('order', function ($q) {
            $q->whereIn('status', ['pending', 'cooking', 'ready']);
        })->exists()) {
            return response()->json(['message' => 'No se puede eliminar un producto con pedidos activos'], 409);
        }
        $product->delete();
        return response()->json(['message' => 'Producto eliminado']);
    }

    public function categories()
    {
        return Category::where('is_active', true)
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();
    }
}
