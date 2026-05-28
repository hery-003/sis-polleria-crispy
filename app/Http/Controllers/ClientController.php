<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Clients/Index', [
            'clients' => $clients
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'document_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        Client::create($validated);

        return redirect()->back()->with('success', 'Cliente registrado correctamente');
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'document_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $client->update($validated);

        return redirect()->back()->with('success', 'Cliente actualizado correctamente');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->back()->with('success', 'Cliente eliminado correctamente');
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query);

        $clients = Client::where('is_active', true)
            ->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                  ->orWhere('phone', 'like', "%{$escaped}%")
                  ->orWhere('document_number', 'like', "%{$escaped}%");
            })
            ->limit(10)
            ->get();

        return response()->json($clients);
    }

    public function orders(Client $client)
    {
        $orders = Order::where('client_id', $client->id)
            ->whereIn('status', ['completed'])
            ->latest()
            ->take(5)
            ->get(['id', 'order_number', 'total_amount', 'status', 'created_at']);

        return response()->json($orders);
    }
}
