<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use Illuminate\Http\Request;

class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::orderBy('number')->get();
        return inertia('Mesas/Index', compact('mesas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|integer|unique:mesas,number',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        Mesa::create($validated);

        return redirect()->route('mesas.index')->with('success', 'Mesa creada correctamente');
    }

    public function update(Request $request, Mesa $mesa)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|integer|unique:mesas,number,' . $mesa->id,
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $mesa->update($validated);

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
