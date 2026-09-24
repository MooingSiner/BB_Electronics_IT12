<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $todaysSales = (float) Sale::whereDate('sale_date', today())
            ->where('status', SaleStatus::Completed)
            ->sum('total_amount');

        $todaysSalesCount = Sale::whereDate('sale_date', today())->count();

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();

        $pendingReturnsCount = ReturnRecord::where('status', 'open')->count();

        $mySalesTodayCount = Sale::where('user_id', $user->user_id)
            ->whereDate('sale_date', today())
            ->count();

        $mySalesTodayTotal = (float) Sale::where('user_id', $user->user_id)
            ->whereDate('sale_date', today())
            ->where('status', SaleStatus::Completed)
            ->sum('total_amount');

        $transactions = Sale::with(['user', 'items.product'])
            ->latest('sale_date')
            ->take(5)
            ->get()
            ->map(fn (Sale $sale) => (object) [
                'id' => $sale->sale_id,
                'code' => $sale->code(),
                'items_summary' => $sale->items->pluck('product.product_name')->filter()->implode(', '),
                'total' => (float) $sale->total_amount,
                'processed_by' => $sale->user->full_name ?? '—',
                'created_at' => $sale->sale_date,
                'status' => $sale->status === SaleStatus::Completed ? 'Completed' : 'Voided',
            ]);

        $lowStockItems = Product::with('category')
            ->where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('quantity_on_hand')
            ->take(5)
            ->get()
            ->map(fn (Product $product) => (object) [
                'name' => $product->product_name,
                'category' => $product->category->category_name ?? '—',
                'stock' => $product->quantity_on_hand,
            ]);

        $recentReturns = ReturnRecord::with('product')
            ->latest('return_date')
            ->take(5)
            ->get()
            ->map(fn (ReturnRecord $return) => (object) [
                'product_name' => $return->product->product_name ?? '—',
                'reason' => $return->reason,
                'created_at' => $return->return_date,
                'status' => $return->status->value === 'open' ? 'Pending' : 'Approved',
            ]);

        return view('cashier.dashboard', compact(
            'todaysSales', 'todaysSalesCount', 'lowStockCount', 'pendingReturnsCount',
            'mySalesTodayCount', 'mySalesTodayTotal', 'transactions', 'lowStockItems', 'recentReturns'
        ));
    }
}
