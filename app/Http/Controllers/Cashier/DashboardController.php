<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $period = $request->input('period', 'today');
        [$dateFrom, $dateTo, $periodLabel] = $this->resolvePeriod($request, $period);

        $todaysSales = (float) Sale::whereBetween('sale_date', [$dateFrom, $dateTo])
            ->where('status', SaleStatus::Completed)
            ->sum('total_amount');

        $todaysSalesCount = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])->where('status', SaleStatus::Completed)->count();

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();

        $pendingReturnsCount = ReturnRecord::where('status', 'open')->count();

        $mySalesTodayCount = Sale::where('user_id', $user->user_id)
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
            ->count();

        $mySalesTodayTotal = (float) Sale::where('user_id', $user->user_id)
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
            ->where('status', SaleStatus::Completed)
            ->sum('total_amount');

        $transactions = Sale::with(['user', 'items.product'])
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
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

        return view('cashier.dashboard', [
            'todaysSales' => $todaysSales,
            'todaysSalesCount' => $todaysSalesCount,
            'lowStockCount' => $lowStockCount,
            'pendingReturnsCount' => $pendingReturnsCount,
            'mySalesTodayCount' => $mySalesTodayCount,
            'mySalesTodayTotal' => $mySalesTodayTotal,
            'transactions' => $transactions,
            'lowStockItems' => $lowStockItems,
            'recentReturns' => $recentReturns,
            'period' => $period,
            'periodLabel' => $periodLabel,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolvePeriod(Request $request, string $period): array
    {
        if ($period === 'custom' && $request->filled('date_from') && $request->filled('date_to')) {
            $from = Carbon::parse($request->input('date_from'))->startOfDay();
            $to = Carbon::parse($request->input('date_to'))->endOfDay();

            return [$from, $to, $from->format('M d').' – '.$to->format('M d, Y')];
        }

        return match ($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek(), 'This Week'],
            'month' => [now()->startOfMonth(), now()->endOfMonth(), 'This Month'],
            'year' => [now()->startOfYear(), now()->endOfYear(), 'This Year'],
            default => [now()->startOfDay(), now()->endOfDay(), 'Today'],
        };
    }
}
