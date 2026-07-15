<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Middleware;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ClientController extends Controller
{
    #[Middleware(['auth', 'role:admin,cashier'])]
    public function index()
    {
        $clients = Cache::tags(['clients'])->remember('clients_page_'.request('page', 1), 300, function () {
            return Client::where('is_active', true)
                ->select('id', 'name', 'phone', 'document_number', 'email', 'is_active', 'created_at')
                ->orderBy('name')
                ->paginate(20);
        });

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function store(StoreClientRequest $request)
    {
        Client::create($request->validated());

        Cache::tags(['clients'])->flush();

        return redirect()->back()->with('success', 'Cliente registrado correctamente');
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        Cache::tags(['clients'])->flush();

        return redirect()->back()->with('success', 'Cliente actualizado correctamente');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        Cache::tags(['clients'])->flush();

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
        $orders = $client->orders()
            ->whereIn('status', ['completed'])
            ->latest()
            ->take(5)
            ->get(['id', 'order_number', 'total_amount', 'status', 'created_at']);

        return response()->json($orders);
    }
}
