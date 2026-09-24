<?php

namespace App\Http\Controllers\Owner;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::where('is_active', true)->count();
        $totalStock = (int) Product::where('is_active', true)->sum('quantity_on_hand');

        $todaySalesTotal = Sale::whereDate('sale_date', today())
            ->where('status', SaleStatus::Completed)
            ->sum('total_amount');

        $txnCount = Sale::whereDate('sale_date', today())->count();

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->where('quantity_on_hand', '>', 0)
            ->count();

        $outOfStockCount = Product::where('is_active', true)
            ->where('quantity_on_hand', '<=', 0)
            ->count();

        $transactions = Sale::with(['user', 'items.product'])
            ->latest('sale_date')
            ->take(5)
            ->get()
            ->map(fn (Sale $sale) => (object) [
                'id' => $sale->sale_id,
                'code' => $sale->code(),
                'products' => $sale->items->pluck('product.product_name')->filter()->implode(', '),
                'total' => '₱'.number_format((float) $sale->total_amount, 2),
                'processed_by' => $sale->user->full_name ?? '—',
                'status' => $sale->status === SaleStatus::Completed ? 'Completed' : 'Voided',
            ]);

        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('quantity_on_hand')
            ->take(5)
            ->get()
            ->map(fn (Product $product) => (object) [
                'name' => $product->product_name,
                'stock' => $product->quantity_on_hand,
            ]);

        $recentOrders = PurchaseOrder::with(['supplier', 'items'])
            ->latest('order_date')
            ->take(5)
            ->get()
            ->map(fn (PurchaseOrder $order) => (object) [
                'supplier' => $order->supplier->supplier_name ?? '—',
                'items' => $order->items->count(),
                'date' => optional($order->order_date)->format('M d, Y'),
            ]);

        $soldByProduct = Sale::where('status', SaleStatus::Completed)
            ->where('sale_date', '>=', now()->subDays(30)->startOfDay())
            ->with('items')
            ->get()
            ->flatMap->items
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'));

        $activeProducts = Product::where('is_active', true)->get();

        $fastMoving = $activeProducts
            ->map(fn (Product $product) => (object) [
                'name' => $product->product_name,
                'units_sold' => (int) ($soldByProduct[$product->product_id] ?? 0),
            ])
            ->filter(fn ($product) => $product->units_sold >= 10)
            ->sortByDesc('units_sold')
            ->take(5)
            ->values();

        $slowMoving = $activeProducts
            ->map(fn (Product $product) => (object) [
                'name' => $product->product_name,
                'units_sold' => (int) ($soldByProduct[$product->product_id] ?? 0),
            ])
            ->filter(fn ($product) => $product->units_sold < 10)
            ->sortBy('units_sold')
            ->take(5)
            ->values();

        return view('owner.dashboard', [
            'totalProducts' => $totalProducts,
            'totalStock' => number_format($totalStock),
            'todaySales' => '₱'.number_format((float) $todaySalesTotal, 2),
            'txnCount' => $txnCount,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'transactions' => $transactions,
            'lowStockProducts' => $lowStockProducts,
            'recentOrders' => $recentOrders,
            'fastMoving' => $fastMoving,
            'slowMoving' => $slowMoving,
        ]);
    }
}
