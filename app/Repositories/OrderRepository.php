<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getPendingOrders(): Collection
    {
        return $this->model->whereIn('status', ['pending', 'cooking', 'ready'])
            ->with(['items.product', 'items.variant', 'user', 'mesa'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model->where('order_number', $orderNumber)->first();
    }

    public function getRecentSales(int $limit = 5): Collection
    {
        return $this->model->where('status', 'completed')
            ->with(['user', 'metodoPago'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
