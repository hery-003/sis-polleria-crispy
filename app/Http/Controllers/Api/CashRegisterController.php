<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Attributes\Middleware;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CashMovementApiRequest;
use App\Http\Requests\Api\CloseCashRegisterApiRequest;
use App\Http\Requests\Api\OpenCashRegisterApiRequest;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CashRegisterController extends Controller
{
    #[Middleware(['auth:sanctum', 'role:admin'])]
    protected CashRegisterService $cashRegisterService;

    public function __construct(CashRegisterService $cashRegisterService)
    {
        $this->cashRegisterService = $cashRegisterService;
    }

    public function index()
    {
        return $this->cashRegisterService->getActiveRegister();
    }

    public function open(OpenCashRegisterApiRequest $request)
    {
        try {
            $register = $this->cashRegisterService->openRegister(
                $request->validated(),
                $request->user()->id
            );

            return response()->json($register, 201);
        } catch (\Exception $e) {
            Log::error('Error opening cash register: '.$e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function close(CloseCashRegisterApiRequest $request)
    {
        try {
            $register = $this->cashRegisterService->closeRegister(
                $request->register_id,
                $request->actual_closing_balance,
                $request->notes
            );
            $summary = $this->cashRegisterService->getCloseSummary(
                $register,
                $request->actual_closing_balance
            );

            return response()->json([
                'register' => $register,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('Error closing cash register: '.$e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function movement(CashMovementApiRequest $request)
    {
        try {
            $movement = $this->cashRegisterService->createMovement(
                $request->register_id,
                $request->validated(),
                $request->user()->id
            );

            return response()->json($movement, 201);
        } catch (\Exception $e) {
            Log::error('Error creating cash movement: '.$e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function summary(Request $request)
    {
        $register = $this->cashRegisterService->getActiveRegister();

        if (! $register) {
            return response()->json(['message' => 'No hay caja abierta'], 404);
        }

        return response()->json(
            $this->cashRegisterService->calculateExpectedCash($register)
        );
    }
}
