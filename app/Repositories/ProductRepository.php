<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getActiveProductsWithVariants(): Collection
    {
        return $this->model->where('is_active', true)
            ->with(['variants' => function ($query) {
                $query->where('is_active', true);
            }, 'category'])
            ->get();
    }

    public function findWithCategory(int $id): ?Product
    {
        return $this->model->with('category')->find($id);
    }

    public function getLowStockProducts(): Collection
    {
        return $this->model->whereHas('variants', function ($query) {
            $query->whereColumn('stock', '<=', 'stock_threshold');
        })->with('variants')->get();
    }
}
