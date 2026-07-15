<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Middleware;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    #[Middleware(['auth', 'role:admin,cashier'])]
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Product::class);
        $page = request('page', 1);
        $products = Cache::tags(['products'])->remember("products_page_{$page}", 300, function () {
            return Product::with('category:id,name', 'variants:id,product_id,name,price,is_active')
                ->select('id', 'name', 'image', 'image_thumbnail', 'category_id', 'is_active', 'created_at')
                ->latest()
                ->paginate(20);
        });

        return inertia('Products/Index', compact('products'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);
        $categories = Category::where('is_active', true)->get();

        return inertia('Products/Create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);
        $validated = $request->validated();

        $validated['slug'] = Str::slug($validated['name']);
        $validated['image'] = $this->productService->handleImageUpload($request->file('image'));
        $validated['image_thumbnail'] = $this->productService->generateThumbnail($validated['image']);
        $validated['is_active'] = $request->boolean('is_active');
        $product = Product::create($validated);

        $variants = $request->input('variants', []);
        if (is_string($variants)) {
            $variants = json_decode($variants, true) ?? [];
        }

        foreach ($variants as $i => $variant) {
            $variantImage = $request->file("variant_images.{$i}");
            if ($variantImage) {
                $variant['image'] = $variantImage->store('variants', 'public');
            }
            $product->variants()->create($variant);
        }

        AuditLog::log('product_created', 'Product', $product->id, null, $product->load('variants')->toArray());

        $this->productService->invalidateCache();
        Cache::tags(['products'])->flush();

        return redirect()->route('products.index')->with('success', 'Producto creado');
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);
        return redirect()->route('products.edit', $product);
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        $categories = Category::where('is_active', true)->get();
        $product->load('variants');

        return inertia('Products/Edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $validated = $request->validated();

        $hasNewImage = $request->hasFile('image');
        $validated['image'] = $this->productService->handleImageUpload(
            $request->file('image'),
            $product->image
        );

        if ($hasNewImage && $validated['image'] && $product->image !== $validated['image']) {
            $this->productService->deleteProductImage($product->image_thumbnail);
            $validated['image_thumbnail'] = $this->productService->generateThumbnail($validated['image']);
        }

        if (! $hasNewImage) {
            $oldImage = $validated['image'] ?? $product->image;
            $wasRemoved = $request->boolean('remove_image') && $oldImage;
            if ($wasRemoved) {
                $this->productService->deleteProductImage($product->image_thumbnail);
                $validated['image_thumbnail'] = null;
            }
            $validated['image'] = $this->productService->handleImageRemoval(
                $request->boolean('remove_image'),
                $oldImage
            );
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $old = $product->toArray();
        $product->update($validated);

        $variants = $request->input('variants', []);
        if (is_string($variants)) {
            $variants = json_decode($variants, true) ?? [];
        }

        $this->productService->syncVariants($product, $variants, $request);

        AuditLog::log('product_updated', 'Product', $product->id, $old, $product->fresh()->load('variants')->toArray());

        Cache::tags(['products'])->flush();

        return redirect()->route('products.index')->with('success', 'Producto actualizado');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $old = $product->load('variants')->toArray();
        $this->productService->deleteProductImage($product->image);
        $this->productService->deleteProductImage($product->image_thumbnail);
        $product->variants()->delete();
        $product->delete();

        AuditLog::log('product_deleted', 'Product', $product->id, $old);

        $this->productService->invalidateCache();
        Cache::tags(['products'])->flush();

        return redirect()->route('products.index')->with('success', 'Producto eliminado');
    }

    public function stockIndex()
    {
        $this->authorize('viewAny', Product::class);
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
        $this->authorize('create', Product::class);
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:product_variants,id',
            'updates.*.stock' => 'required|integer|min:0',
        ]);

        foreach ($validated['updates'] as $update) {
            $variant = ProductVariant::find($update['id']);
            $variant->update(['stock' => $update['stock']]);
        }

        $this->productService->invalidateCache();

        return redirect()->back()->with('success', 'Stock actualizado masivamente');
    }
}
