<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Authorize;
use Illuminate\Routing\Attributes\Middleware;
use App\Http\Requests\CloseCashRegisterRequest;
use App\Http\Requests\OpenCashRegisterRequest;
use App\Http\Requests\StoreCashMovementRequest;
use App\Models\AuditLog;
use App\Models\CashRegister;
use App\Services\CashRegisterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CashRegisterController extends Controller
{
    #[Middleware(['auth', 'role:admin'])]
    #[Authorize('manage-cash-register')]
    public function __construct(
        protected CashRegisterService $cashRegisterService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', CashRegister::class);
        $activeRegister = $this->cashRegisterService->getActiveRegister();
        $recentMovements = [];
        $closeSummary = null;
        $expectedCash = null;

        if ($activeRegister) {
            $recentMovements = $activeRegister->movements()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            $expectedCash = $this->cashRegisterService->calculateExpectedCash($activeRegister);
        }

        $closeHistory = CashRegister::with('user')
            ->where('status', 'closed')
            ->orderBy('closed_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'user_name' => $r->user->name ?? 'N/A',
                'opened_at' => $r->opened_at,
                'closed_at' => $r->closed_at,
                'opening_balance' => $r->opening_balance,
                'closing_balance' => $r->closing_balance,
                'difference' => ($r->closing_balance ?? 0) - $r->opening_balance,
                'notes' => $r->notes,
            ]);

        return Inertia::render('CashRegister/Index', [
            'activeRegister' => $activeRegister,
            'recentMovements' => $recentMovements,
            'closeSummary' => $closeHistory,
            'expectedCash' => $expectedCash,
        ]);
    }

    public function open(OpenCashRegisterRequest $request)
    {
        $this->authorize('create', CashRegister::class);
        $validated = $request->validated();

        try {
            $register = $this->cashRegisterService->openRegister($validated, auth()->id());

            AuditLog::log('cash_register_opened', 'CashRegister', $register->id, null, $register->toArray());

            return redirect()->back()->with('success', 'Caja abierta correctamente');
        } catch (Exception $e) {
            Log::error('Error opening register: '.$e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function close(CloseCashRegisterRequest $request)
    {
        $validated = $request->validated();

        try {
            $register = CashRegister::findOrFail($validated['cash_register_id']);
            $this->authorize('update', $register);
            $closed = $this->cashRegisterService->closeRegister(
                $register->id,
                $validated['closing_balance'],
                $validated['notes']
            );

            AuditLog::log('cash_register_closed', 'CashRegister', $register->id, $register->toArray(), $closed->toArray());

            $summary = $this->cashRegisterService->getCloseSummary($register, $validated['closing_balance']);

            return redirect()->back()->with([
                'success' => 'Caja cerrada correctamente',
                'close_summary' => $summary,
            ]);
        } catch (Exception $e) {
            Log::error('Error closing register: '.$e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function storeMovement(StoreCashMovementRequest $request)
    {
        $activeRegister = $this->cashRegisterService->getActiveRegister();

        if (! $activeRegister) {
            return redirect()->back()->with('error', 'No hay caja abierta');
        }

        $this->authorize('update', $activeRegister);

        $validated = $request->validated();

        try {
            $movement = $this->cashRegisterService->createMovement($activeRegister->id, $validated, auth()->id());

            AuditLog::log('cash_movement_created', 'CashMovement', $movement->id, null, $movement->toArray());

            return redirect()->back()->with('success', 'Movimiento registrado');
        } catch (Exception $e) {
            Log::error('Error creating movement: '.$e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getSummary(CashRegister $register)
    {
        $this->authorize('view', $register);
        $summary = $this->cashRegisterService->getCloseSummary($register, $register->closing_balance ?? 0);

        return response()->json($summary);
    }

    public function exportPdf(CashRegister $register)
    {
        $this->authorize('view', $register);
        $summary = $this->cashRegisterService->getCloseSummary($register, $register->closing_balance ?? 0);
        $register->load('user');

        $pdf = Pdf::loadView('reports.cash-register', [
            'register' => $register,
            'summary' => $summary,
        ]);

        $dateStr = $register->closed_at?->format('Y-m-d') ?? $register->opened_at->format('Y-m-d');
        return $pdf->download("cierre_caja_{$dateStr}.pdf");
    }
}
