<?php

namespace App\Http\Modules\Dashboard\Services;

use App\Http\Modules\Services\Model\ServiceOrder;
use App\Http\Modules\Services\Model\ServicePerformance;
use App\Http\Modules\Sales\Model\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function getSummary($entityId)
    {
        // 1. Service Sales
        $serviceStats = ServiceOrder::where('entity_id', $entityId)
            ->with('items')
            ->get();

        $totalServiceRevenue = $serviceStats->sum('total_net');
        $totalOperatorCommissions = 0;

        foreach ($serviceStats as $order) {
            foreach ($order->items as $item) {
                // Commission is calculated based on total_net of the item
                $totalOperatorCommissions += ($item->total_net * ($item->commission_percentage_snapshot / 100));
            }
        }

        $inventoryStats = DB::table('sales')
            ->where('entity_id', $entityId)
            ->select(
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(total_profit) as total_profit')
            )
            ->first();

        $inventoryRevenue = $inventoryStats->total_revenue ?? 0;
        $inventoryProfit = $inventoryStats->total_profit ?? 0;

        // 3. Totals
        $totalRevenue = $totalServiceRevenue + $inventoryRevenue;
        $netProfit = ($totalServiceRevenue - $totalOperatorCommissions) + $inventoryProfit;

        // 4. Activity (Last 7 days)
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');

            $serviceDay = ServiceOrder::where('entity_id', $entityId)
                ->whereDate('date', $date)
                ->sum('total_net');

            $inventoryDay = DB::table('sales')
                ->where('entity_id', $entityId)
                ->whereDate('date', $date)
                ->sum('total');

            $last7Days[] = [
                'date' => $date,
                'services' => (float) $serviceDay,
                'inventory' => (float) $inventoryDay,
                'total' => (float) ($serviceDay + $inventoryDay)
            ];
        }

        // 5. Top Operators
        $topOperators = ServicePerformance::join('service_orders', 'service_performances.order_id', '=', 'service_orders.id')
            ->join('operators', 'service_performances.operator_id', '=', 'operators.id')
            ->join('users', 'operators.user_id', '=', 'users.id')
            ->where('service_orders.entity_id', $entityId)
            ->select(
                'users.name',
                DB::raw('SUM(service_performances.total_net) as total_generated'),
                DB::raw('SUM(service_performances.total_net * (service_performances.commission_percentage_snapshot / 100)) as commission')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_generated', 'desc')
            ->limit(5)
            ->get();

        return [
            'metrics' => [
                'total_revenue' => (float) $totalRevenue,
                'service_revenue' => (float) $totalServiceRevenue,
                'inventory_revenue' => (float) $inventoryRevenue,
                'operator_commissions' => (float) $totalOperatorCommissions,
                'net_profit' => (float) $netProfit,
            ],
            'chart_data' => $last7Days,
            'top_operators' => $topOperators
        ];
    }
}
