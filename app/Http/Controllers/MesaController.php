<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Authorize;
use Illuminate\Routing\Attributes\Middleware;
use App\Http\Requests\StoreMesaRequest;
use App\Http\Requests\UpdateMesaRequest;
use App\Models\Mesa;

class MesaController extends Controller
{
    #[Middleware(['auth', 'role:admin'])]
    #[Authorize('manage-mesas')]
    public function index()
    {
        $mesas = Mesa::withCount(['orders as active_orders_count' => function ($q) {
            $q->whereNotIn('status', ['completed', 'cancelled']);
        }])->orderBy('number')->get();

        $mesas->each(function ($mesa) {
            if (! $mesa->is_active) {
                $mesa->status = 'inactive';
            } elseif ($mesa->reserved_at) {
                $mesa->status = 'reserved';
            } elseif ($mesa->active_orders_count > 0) {
                $mesa->status = 'occupied';
            } else {
                $mesa->status = 'available';
            }
        });

        return inertia('Mesas/Index', ['mesas' => $mesas]);
    }

    public function store(StoreMesaRequest $request)
    {
        Mesa::create($request->validated());

        return redirect()->route('mesas.index')->with('success', 'Mesa creada correctamente');
    }

    public function update(UpdateMesaRequest $request, Mesa $mesa)
    {
        $mesa->update($request->validated());

        return redirect()->route('mesas.index')->with('success', 'Mesa actualizada correctamente');
    }

    public function destroy(Mesa $mesa)
    {
        $hasActiveOrders = $mesa->orders()->whereNotIn('status', ['completed', 'cancelled'])->exists();

        if ($hasActiveOrders) {
            return redirect()->route('mesas.index')->with('error', 'No se puede eliminar la mesa porque tiene pedidos activos');
        }

        $mesa->delete();

        return redirect()->route('mesas.index')->with('success', 'Mesa eliminada correctamente');
    }
}
