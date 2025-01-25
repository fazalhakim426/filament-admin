<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Orders per Month';

    protected static ?int $sort = 1;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        // Get the start and end dates for the previous year
        $startOfYear = Carbon::now()->subYear()->startOfYear();
        $endOfYear = Carbon::now()->subYear()->endOfYear();

        // Fetch order counts grouped by month for the previous year
        $ordersData = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Generate data for all months, filling missing months with 0
        $monthlyOrders = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyOrders[] = $ordersData->get($month, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $monthlyOrders,
                    'fill' => 'start',
                    'borderColor' => '#4A90E2',
                    'backgroundColor' => 'rgba(74, 144, 226, 0.2)',
                ],
            ],
            'labels' => [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ],
        ];
    }
}
