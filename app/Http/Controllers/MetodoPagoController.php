<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use Illuminate\Http\Request;

class MetodoPagoController extends Controller
{
    public function index()
    {
        $metodos = MetodoPago::orderBy('name')->get();
        return inertia('MetodosPago/Index', compact('metodos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:metodos_pago,slug',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        MetodoPago::create($validated);

        return redirect()->route('metodos-pago.index')->with('success', 'Método de pago creado correctamente');
    }

    public function update(Request $request, MetodoPago $metodoPago)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:metodos_pago,slug,' . $metodoPago->id,
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $metodoPago->update($validated);

        return redirect()->route('metodos-pago.index')->with('success', 'Método de pago actualizado correctamente');
    }

    public function destroy(MetodoPago $metodoPago)
    {
        $hasOrders = $metodoPago->orders()->exists();

        if ($hasOrders) {
            return redirect()->route('metodos-pago.index')->with('error', 'No se puede eliminar porque tiene pedidos asociados');
        }

        $metodoPago->delete();

        return redirect()->route('metodos-pago.index')->with('success', 'Método de pago eliminado correctamente');
    }
}
