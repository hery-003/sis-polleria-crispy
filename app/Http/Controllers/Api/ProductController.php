<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Attributes\Middleware;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiProductRequest;
use App\Http\Requests\Api\UpdateApiProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;

class ProductController extends Controller
{
    #[Middleware('auth:sanctum')]
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index()
    {
        return Product::with('category', 'variants')->get();
    }

    public function show(Product $product)
    {
        return $product->load('category', 'variants');
    }

    public function store(StoreApiProductRequest $request)
    {
        $data = $request->safe()->except('image', 'variants');

        $data['image'] = $this->productService->handleImageUpload($request->file('image'));

        $product = Product::create($data);

        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                $product->variants()->create($variant);
            }
        }

        return response()->json($product->load('category', 'variants'), 201);
    }

    public function update(UpdateApiProductRequest $request, Product $product)
    {
        $data = $request->safe()->except('image');

        $data['image'] = $this->productService->handleImageUpload(
            $request->file('image'),
            $product->image
        );

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

        $this->productService->deleteProductImage($product->image);
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
