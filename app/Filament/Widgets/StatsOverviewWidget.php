<?php
namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    // Helper method to calculate percentage change
    private function calculatePercentageChange($current, $previous): string
    {
        if ($previous == 0) {
            return '0'; // Avoid division by zero
        }
        $change = (($current - $previous) / $previous) * 100;
        return number_format($change, 2) . '%';
    }

    private function getTotalRevenue(): float
    {
        return Order::sum('total_price');
    }

    private function getNewCustomersCount(): int
    {
        // -> whereHas('roles', function ($query) {
        //     $query->where('name', 'customer');
        // })
        return User:: 
            where('created_at', '>=', now()->startOfMonth())
            ->count();
    }

    private function getPreviousCustomersCount(): int
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'customer');
        })
            ->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])
            ->count();
    }

    private function getNewOrdersCount(): int
    {
        return Order::where('created_at', '>=', now()->startOfMonth())->count();
    }

    private function getPreviousOrdersCount(): int
    {
        return Order::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->count();
    }

    protected function getStats(): array
    {
        $currentRevenue = $this->getTotalRevenue();
        $previousRevenue = Order::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->sum('total_price');

        $currentCustomers = $this->getNewCustomersCount();
        $previousCustomers = $this->getPreviousCustomersCount();

        $currentOrders = $this->getNewOrdersCount();
        $previousOrders = $this->getPreviousOrdersCount();

        return [
            Stat::make('Total Revenue', 'RS ' . number_format($currentRevenue, 2))
                ->description($this->calculatePercentageChange($currentRevenue, $previousRevenue) . ' increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color($currentRevenue >= $previousRevenue ? 'success' : 'danger'),

            Stat::make('New Users', $currentCustomers)
                ->description($this->calculatePercentageChange($currentCustomers, $previousCustomers) . ' increase')
                ->descriptionIcon($currentCustomers >= $previousCustomers ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([17, 16, 14, 15, 14, 13, 12])
                ->color($currentCustomers >= $previousCustomers ? 'success' : 'danger'),

            Stat::make('New Orders', $currentOrders)
                ->description($this->calculatePercentageChange($currentOrders, $previousOrders) . ' increase')
                ->descriptionIcon($currentOrders >= $previousOrders ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->color($currentOrders >= $previousOrders ? 'success' : 'danger'),
        ];
    }
}
