<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class CashRegisterController extends Controller
{
    public function __construct(
        protected CashRegisterService $cashRegisterService
    ) {}

    public function index()
    {
        $activeRegister = $this->cashRegisterService->getActiveRegister();
        $recentMovements = [];
        $closeSummary = null;
        $expectedCash = null;

        if ($activeRegister) {
            $recentMovements = CashMovement::where('cash_register_id', $activeRegister->id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            $expectedCash = $this->cashRegisterService->calculateExpectedCash($activeRegister);
        }

        return Inertia::render('CashRegister/Index', [
            'activeRegister' => $activeRegister,
            'recentMovements' => $recentMovements,
            'closeSummary' => $closeSummary,
            'expectedCash' => $expectedCash,
        ]);
    }

    public function open(Request $request)
    {
        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        try {
            $this->cashRegisterService->openRegister($validated, auth()->id());
            return redirect()->back()->with('success', 'Caja abierta correctamente');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function close(Request $request)
    {
        $validated = $request->validate([
            'cash_register_id' => 'required|integer',
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $register = CashRegister::findOrFail($validated['cash_register_id']);
            $closed = $this->cashRegisterService->closeRegister(
                $register->id,
                $validated['closing_balance'],
                $validated['notes']
            );

            $summary = $this->cashRegisterService->getCloseSummary($register, $validated['closing_balance']);

            return redirect()->back()->with([
                'success' => 'Caja cerrada correctamente',
                'close_summary' => $summary,
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function storeMovement(Request $request)
    {
        $activeRegister = $this->cashRegisterService->getActiveRegister();

        if (!$activeRegister) {
            return redirect()->back()->with('error', 'No hay caja abierta');
        }

        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        try {
            $this->cashRegisterService->createMovement($activeRegister->id, $validated, auth()->id());
            return redirect()->back()->with('success', 'Movimiento registrado');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getSummary(CashRegister $register)
    {
        $summary = $this->cashRegisterService->getCloseSummary($register, $register->closing_balance ?? 0);

        return response()->json($summary);
    }
}
