<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashRegisterService
{
    public function getActiveRegister(): ?CashRegister
    {
        return CashRegister::where('status', 'open')->first();
    }

    public function openRegister(array $data, int $userId): CashRegister
    {
        return DB::transaction(function () use ($data, $userId) {
            $active = CashRegister::where('status', 'open')->lockForUpdate()->first();
            if ($active) {
                throw new \Exception('Ya existe una caja abierta');
            }

            return CashRegister::create([
                'user_id' => $userId,
                'opened_at' => now(),
                'opening_balance' => $data['opening_balance'],
                'closing_balance' => 0,
                'status' => 'open',
            ]);
        });
    }

    public function closeRegister(int $registerId, float $actualClosingBalance, ?string $notes = null): CashRegister
    {
        $register = CashRegister::findOrFail($registerId);

        if ($register->status !== 'open') {
            throw new \Exception('La caja no está abierta');
        }

        $expectedCash = $this->calculateExpectedCash($register);

        $register->update([
            'closed_at' => now(),
            'closing_balance' => $actualClosingBalance,
            'status' => 'closed',
            'notes' => $notes,
        ]);

        return $register->fresh();
    }

    public function calculateExpectedCash(CashRegister $register): array
    {
        $openingBalance = $register->opening_balance;

        $cashSales = Order::whereBetween('orders.created_at', [$register->opened_at, $register->closed_at ?? now()])
            ->where('payment_method', 'cash')
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $cashInMovements = CashMovement::where('cash_register_id', $register->id)
            ->where('type', 'in')
            ->sum('amount');

        $cashOutMovements = CashMovement::where('cash_register_id', $register->id)
            ->where('type', 'out')
            ->sum('amount');

        $expectedCash = $openingBalance + $cashSales + $cashInMovements - $cashOutMovements;
        $difference = 0;
        $status = 'ok';

        return [
            'opening_balance' => $openingBalance,
            'cash_sales' => $cashSales,
            'cash_in' => $cashInMovements,
            'cash_out' => $cashOutMovements,
            'expected_cash' => $expectedCash,
            'total_orders' => Order::whereBetween('orders.created_at', [$register->opened_at, $register->closed_at ?? now()])
                ->where('payment_status', 'paid')
                ->count(),
            'movements_count' => CashMovement::where('cash_register_id', $register->id)->count(),
        ];
    }

    public function getCloseSummary(CashRegister $register, float $actualClosing): array
    {
        $expected = $this->calculateExpectedCash($register);
        $difference = $actualClosing - $expected['expected_cash'];

        return [
            'expected_cash' => $expected['expected_cash'],
            'actual_cash' => $actualClosing,
            'difference' => $difference,
            'difference_status' => abs($difference) < 0.01 ? 'ok' : ($difference > 0 ? 'surplus' : 'shortage'),
            'opening_balance' => $expected['opening_balance'],
            'cash_sales' => $expected['cash_sales'],
            'cash_in' => $expected['cash_in'],
            'cash_out' => $expected['cash_out'],
            'total_orders' => $expected['total_orders'],
            'movements_count' => $expected['movements_count'],
        ];
    }

    public function createMovement(int $registerId, array $data, int $userId): CashMovement
    {
        $register = CashRegister::findOrFail($registerId);

        if ($register->status !== 'open') {
            throw new \Exception('La caja no está abierta');
        }

        return CashMovement::create([
            'cash_register_id' => $registerId,
            'user_id' => $userId,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'],
        ]);
    }
}
