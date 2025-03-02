<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function getRevenueHistory(Request $request)
    {
        // Get the authenticated user (supplier)
        // $supplier = Auth::user();

        // // Calculate revenue history grouped by day
        // $revenueHistory = OrderItem::selectRaw('DATE(orders.created_at) as transaction_date, 
        //                                         SUM(order_items.price) as total_revenue')
        //     ->join('orders', 'order_items.order_id', '=', 'orders.id')
        //     // ->where('orders.status', 'paid') // Ensure the order is paid
        //     ->where('order_items.supplier_user_id', $supplier->id) // Filter by authenticated supplier
        //     ->groupByRaw('DATE(orders.created_at)')
        //     ->orderByDesc('transaction_date') // Order by the latest transaction date
        //     ->get();

        // return response()->json($revenueHistory);
        return response()->json('note implemented yet');
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
