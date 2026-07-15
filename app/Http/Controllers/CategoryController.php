<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Middleware;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\AuditLog;
use App\Models\Category;

class CategoryController extends Controller
{
    #[Middleware(['auth', 'role:admin,cashier'])]
    public function index()
    {
        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->get();

        $trashed = Category::onlyTrashed()
            ->withCount('products')
            ->orderBy('deleted_at', 'desc')
            ->get();

        return inertia('Categories/Index', [
            'categories' => $categories,
            'trashed' => $trashed,
        ]);
    }

    public function restore($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();

        AuditLog::log('category_restored', 'Category', $category->id, null, $category->toArray());

        return redirect()->route('categories.index')->with('success', 'Categoría restaurada correctamente');
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        AuditLog::log('category_created', 'Category', $category->id, null, $category->toArray());

        return redirect()->route('categories.index')->with('success', 'Categoría creada correctamente');
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $old = $category->toArray();
        $category->update($request->validated());

        AuditLog::log('category_updated', 'Category', $category->id, $old, $category->fresh()->toArray());

        return redirect()->route('categories.index')->with('success', 'Categoría actualizada correctamente');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')->with('error', 'No se puede eliminar una categoría con productos asociados');
        }

        $category->delete();

        AuditLog::log('category_deleted', 'Category', $category->id);

        return redirect()->route('categories.index')->with('success', 'Categoría eliminada correctamente');
    }
}
