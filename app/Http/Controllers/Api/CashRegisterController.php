<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CashRegisterController extends Controller
{
    protected CashRegisterService $cashRegisterService;

    public function __construct(CashRegisterService $cashRegisterService)
    {
        $this->cashRegisterService = $cashRegisterService;
    }

    public function index()
    {
        return $this->cashRegisterService->getActiveRegister();
    }

    public function open(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'opening_balance' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $register = $this->cashRegisterService->openRegister(
                $request->all(),
                $request->user()->id
            );
            return response()->json($register, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function close(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'register_id' => 'required|exists:cash_registers,id',
            'actual_closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function movement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'register_id' => 'required|exists:cash_registers,id',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $movement = $this->cashRegisterService->createMovement(
                $request->register_id,
                $request->all(),
                $request->user()->id
            );
            return response()->json($movement, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function summary(Request $request)
    {
        $register = $this->cashRegisterService->getActiveRegister();

        if (!$register) {
            return response()->json(['message' => 'No hay caja abierta'], 404);
        }

        return response()->json(
            $this->cashRegisterService->calculateExpectedCash($register)
        );
    }
}
