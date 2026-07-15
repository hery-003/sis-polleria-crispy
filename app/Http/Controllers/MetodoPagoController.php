<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Authorize;
use Illuminate\Routing\Attributes\Middleware;
use App\Http\Requests\StoreMetodoPagoRequest;
use App\Http\Requests\UpdateMetodoPagoRequest;
use App\Models\MetodoPago;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MetodoPagoController extends Controller
{
    #[Middleware(['auth', 'role:admin'])]
    #[Authorize('manage-metodos-pago')]
    public function index()
    {
        $metodos = MetodoPago::orderBy('name')->get();

        return inertia('MetodosPago/Index', compact('metodos'));
    }

    public function store(StoreMetodoPagoRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('qr_image')) {
            $data['qr_image'] = $request->file('qr_image')->store('qrs', 'public');
        }

        MetodoPago::create($data);

        Cache::forget('pos_metodos_pago');

        return redirect()->route('metodos-pago.index')->with('success', 'Método de pago creado correctamente');
    }

    public function update(UpdateMetodoPagoRequest $request, MetodoPago $metodoPago)
    {
        $data = $request->validated();

        if ($request->hasFile('qr_image')) {
            if ($metodoPago->qr_image) {
                Storage::disk('public')->delete($metodoPago->qr_image);
            }
            $data['qr_image'] = $request->file('qr_image')->store('qrs', 'public');
        } else {
            unset($data['qr_image']);
        }

        if ($request->boolean('remove_qr') && $metodoPago->qr_image) {
            Storage::disk('public')->delete($metodoPago->qr_image);
            $data['qr_image'] = null;
        }

        $metodoPago->update($data);

        Cache::forget('pos_metodos_pago');

        return redirect()->route('metodos-pago.index')->with('success', 'Método de pago actualizado correctamente');
    }

    public function destroy(MetodoPago $metodoPago)
    {
        $hasOrders = $metodoPago->orders()->exists();

        if ($hasOrders) {
            return redirect()->route('metodos-pago.index')->with('error', 'No se puede eliminar porque tiene pedidos asociados');
        }

        if ($metodoPago->qr_image) {
            Storage::disk('public')->delete($metodoPago->qr_image);
        }

        $metodoPago->delete();

        Cache::forget('pos_metodos_pago');

        return redirect()->route('metodos-pago.index')->with('success', 'Método de pago eliminado correctamente');
    }
}
