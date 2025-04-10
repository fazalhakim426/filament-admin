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

class AnalyticsController extends Controller
{ 

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
            ->where('orders.order_status', 'delivered') // Ensure the orders are delivered
            ->where('order_items.supplier_user_id', $supplier->id) // Filter by the authenticated supplier
            ->groupBy('products.id')
            ->orderByDesc('total_revenue') // Order by total revenue
            ->get();

        return response()->json($productsSales);
    }
}
