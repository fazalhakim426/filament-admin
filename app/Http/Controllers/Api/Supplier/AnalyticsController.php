<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Support\Facades\DB; 
use App\Models\InventoryMovement; 

class AnalyticsController extends Controller
{
    
        public function getDashboardStats(Request $request)
        {
            // Get the authenticated user (supplier)
            $supplierId = auth()->id();
    
            // Total Sales (count of all completed orders)
            $totalSales = DB::table('orders')
                ->where('supplier_user_id', $supplierId)
                // ->where('order_status', 'delivered')
                ->count();
    
            // Total Orders (all orders regardless of status)
            $totalOrders = DB::table('orders')
                ->where('supplier_user_id', $supplierId)
                ->count();
    
            // Total Revenue (sum of all completed order values)
            $totalRevenue = DB::table('orders')
                ->where('supplier_user_id', $supplierId)
                // ->where('order_status', 'delivered')
                ->sum('total_price');
    
            // Monthly Revenue (last 12 months)
            $monthlyRevenue = DB::table('orders')
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('SUM(total_price) as revenue')
                )
                ->where('supplier_user_id', $supplierId)
                // ->where('order_status', 'delivered')
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
    
            // Format monthly data as array of values
            $monthlyData = array_fill(0, 12, 0);
            foreach ($monthlyRevenue as $record) {
                $index = (int)$record->month - 1;
                $monthlyData[$index] = (float)$record->revenue;
            }
    
            // Weekly Revenue (last 8 weeks)
            $weeklyRevenue = DB::table('orders')
                ->select(
                    DB::raw('WEEK(created_at) as week'),
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('SUM(total_price) as revenue')
                )
                ->where('supplier_user_id', $supplierId)
                // ->where('order_status', 'delivered')
                ->where('created_at', '>=', Carbon::now()->subWeeks(8))
                ->groupBy('year', 'week')
                ->orderBy('year', 'asc')
                ->orderBy('week', 'asc')
                ->get();
    
            // Format weekly data as array of values
            $weeklyData = array_fill(0, 8, 0);
            foreach ($weeklyRevenue as $record) {
                $index = (int)$record->week - (int)Carbon::now()->subWeeks(8)->week;
                if ($index >= 0 && $index < 8) {
                    $weeklyData[$index] = (float)$record->revenue;
                }
            }
    
            // Today's Revenue
            $todayRevenue = DB::table('orders')
                ->where('supplier_user_id', $supplierId)
                // ->where('order_status', 'delivered')
                ->whereDate('created_at', Carbon::today())
                ->sum('total_price');
    
            // Today's Orders
            $todayOrders = DB::table('orders')
                ->where('supplier_user_id', $supplierId)
                ->whereDate('created_at', Carbon::today())
                ->count();
    
            return response()->json([
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'revenue_breakdown' => [
                    'monthly' => $monthlyData,
                    'weekly' => $weeklyData,
                    'today' => [$todayRevenue]
                ],
                'today_orders' => $todayOrders
            ]);
        }
    

    // public function getDashboardStats()
    // {
    //     $now = Carbon::now();

    //     // Total sale from inventory_movements (type: deduction, movement_type: sale)
    //     $totalSale = InventoryMovement::where('type', 'deduction')
    //         ->where('movement_type', 'sale')
    //         ->sum('total_price');

    //     // Total Orders (completed orders)
    //     $totalOrders = Order::whereNotIn('order_status', ['canceled'])->count();

    //     // Total Revenue (from deposits with type 'credit' or 'paid' orders)
    //     $totalRevenue = Deposit::where('transaction_type', 'credit')->sum('amount');

    //     // Revenue breakdowns
    //     $monthlyRevenue = $this->getRevenueByPeriod('month', 12); // last 12 months
    //     $weeklyRevenue = $this->getRevenueByPeriod('week', 4); // last 4 weeks
    //     $todayRevenue = Deposit::whereDate('created_at', $now->toDateString())
    //         ->where('transaction_type', 'credit')
    //         ->sum('amount');

    //     return response()->json([
    //         'total_sale' => round($totalSale, 2),
    //         'total_orders' => $totalOrders,
    //         'total_revenue' => round($totalRevenue, 2),
    //         'revenue' => [
    //             'monthly' => $monthlyRevenue,
    //             'weekly' => $weeklyRevenue,
    //             'today' => [$todayRevenue],
    //         ],
    //     ]);
    // }

    // private function getRevenueByPeriod($period = 'month', $count = 12)
    // {
    //     $revenue = [];

    //     for ($i = $count - 1; $i >= 0; $i--) {
    //         $start = now()->copy()->sub($period . 's', $i)->startOf($period);
    //         $end = now()->copy()->sub($period . 's', $i)->endOf($period);

    //         $sum = Deposit::where('transaction_type', 'credit')
    //             ->whereBetween('created_at', [$start, $end])
    //             ->sum('amount');

    //         $revenue[] = round($sum, 2);
    //     }

    //     return $revenue;
    // }
    public function getMetrics(Request $request)
    {
        $supplierId = auth()->id(); // Or fetch from $request if needed

        $now = Carbon::now();
        $startOfCurrentMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $validStatuses = ['delivered', 'paid', 'shipped', 'ready-to-dispatched', 'processing'];

        // ========== Current Month Metrics ==========
        $totalOrders = Order::where('supplier_user_id', $supplierId)->count();

        $newOrders = Order::where('supplier_user_id', $supplierId)
            ->where('order_status', 'new')
            ->count();

        $totalRevenue = Order::where('supplier_user_id', $supplierId)
            ->whereIn('order_status', $validStatuses)
            ->sum('total_price');

        $totalSales = DB::table('order_items')
            ->where('supplier_user_id', $supplierId)
            ->sum('quantity');

        // ========== Last Month Metrics ==========
        $lastMonthOrders = Order::where('supplier_user_id', $supplierId)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();

        $lastMonthNewOrders = Order::where('supplier_user_id', $supplierId)
            ->where('order_status', 'new')
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();

        $lastMonthRevenue = Order::where('supplier_user_id', $supplierId)
            ->whereIn('order_status', $validStatuses)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_price');

        $lastMonthSales = DB::table('order_items')
            ->where('supplier_user_id', $supplierId)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('quantity');

        // ========== Percentage Increases ==========
        $orderGrowth = $this->calculateGrowth($lastMonthOrders, $totalOrders);
        $newOrderGrowth = $this->calculateGrowth($lastMonthNewOrders, $newOrders);
        $revenueGrowth = $this->calculateGrowth($lastMonthRevenue, $totalRevenue);
        $salesGrowth = $this->calculateGrowth($lastMonthSales, $totalSales);

        return response()->json([
            'metrics' => [
                'total_orders' => $totalOrders,
                'new_orders' => $newOrders,
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
            ],
            'growth' => [
                'total_orders_growth' => $orderGrowth,
                'new_orders_growth' => $newOrderGrowth,
                'total_sales_growth' => $salesGrowth,
                'total_revenue_growth' => $revenueGrowth,
            ]
        ]);
    }

    private function calculateGrowth($lastMonthValue, $currentValue)
    {
        if ($lastMonthValue == 0 && $currentValue == 0) {
            return 0;
        }

        if ($lastMonthValue == 0) {
            return 100;
        }

        return round((($currentValue - $lastMonthValue) / $lastMonthValue) * 100, 2);
    }


    public function getRevenueGraphData(Request $request)
    {
        $supplierId = auth()->id();
        $range = $request->input('range', 'daily'); // daily | weekly | monthly | yearly

        $validStatuses = ['delivered', 'paid', 'shipped', 'ready-to-dispatched', 'processing'];

        $query = Order::select(
            DB::raw("DATE_FORMAT(created_at, " . $this->getDateFormat($range) . ") as label"),
            DB::raw("SUM(total_price) as revenue")
        )
            ->where('supplier_user_id', $supplierId)
            ->whereIn('order_status', $validStatuses)
            ->groupBy('label')
            ->orderBy('label');
        return response()->json($query->get());
    }

    private function getDateFormat($range)
    {
        return match ($range) {
            'daily' => "'%Y-%m-%d'",
            'weekly' => "'%Y-%u'", // Year-week number
            'monthly' => "'%Y-%m'",
            'yearly' => "'%Y'",
            default => "'%Y-%m-%d'"
        };
    }




    public function getProductSales()
    {
        // Get the authenticated user (supplier)
        $supplier = Auth::user();

        // Get sales data for the authenticated supplier's products
        $productsSales = OrderItem::selectRaw('products.name as product_name, 
                                                      SUM(order_items.quantity) as total_quantity_sold,
                                                      SUM(order_items.price) as total_revenue')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            // ->where('orders.order_status', 'delivered') // Ensure the orders are delivered
            ->where('order_items.supplier_user_id', $supplier->id) // Filter by the authenticated supplier
            ->groupBy('products.id')
            ->orderByDesc('total_revenue') // Order by total revenue
            ->get();

        return response()->json($productsSales);
    }
}
