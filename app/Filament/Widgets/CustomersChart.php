<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CustomersChart extends ChartWidget
{
    protected static ?string $heading = 'Users per month';

    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        // Get the start and end dates for the previous year
        $startOfYear = Carbon::now()->subYear()->startOfYear();
        $endOfYear = Carbon::now()->subYear()->endOfYear();

        // Fetch customer counts grouped by month for the previous year
        $customersData = User::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Generate data for all months, filling missing months with 0
        $monthlyCustomers = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyCustomers[] = $customersData->get($month, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Users',
                    'data' => $monthlyCustomers,
                    'fill' => 'start',
                    'borderColor' => '#4A90E2',
                    'backgroundColor' => 'rgba(74, 144, 226, 0.2)',
                ],
            ],
            'labels' => [
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec',
            ],
        ];
    }
}
