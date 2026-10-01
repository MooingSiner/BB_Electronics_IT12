<?php

namespace App\Http\Controllers\Owner;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->input('period', 'today');
        [$dateFrom, $dateTo, $periodLabel] = $this->resolvePeriod($request, $period);

        $totalProducts = Product::where('is_active', true)->count();
        $totalStock = (int) Product::where('is_active', true)->sum('quantity_on_hand');

        $periodSalesTotal = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])
            ->where('status', SaleStatus::Completed)
            ->sum('total_amount');

        $txnCount = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])->count();

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->where('quantity_on_hand', '>', 0)
            ->count();

        $outOfStockCount = Product::where('is_active', true)
            ->where('quantity_on_hand', '<=', 0)
            ->count();

        $transactions = Sale::with(['user', 'items.product'])
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
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

        $salesTrend = $this->buildSalesTrend($dateFrom, $dateTo);

        return view('owner.dashboard', [
            'totalProducts' => $totalProducts,
            'totalStock' => number_format($totalStock),
            'todaySales' => '₱'.number_format((float) $periodSalesTotal, 2),
            'txnCount' => $txnCount,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'transactions' => $transactions,
            'lowStockProducts' => $lowStockProducts,
            'recentOrders' => $recentOrders,
            'fastMoving' => $fastMoving,
            'slowMoving' => $slowMoving,
            'salesTrend' => $salesTrend,
            'period' => $period,
            'periodLabel' => $periodLabel,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);
    }

    /**
     * @return array{unit: string, points: array<int, array{label: string, value: float}>}
     */
    private function buildSalesTrend(Carbon $dateFrom, Carbon $dateTo): array
    {
        $sales = Sale::where('status', SaleStatus::Completed)
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
            ->get(['sale_date', 'total_amount']);

        if ($dateFrom->isSameDay($dateTo)) {
            $grouped = $sales->groupBy(fn (Sale $s) => $s->sale_date->format('H'));

            $points = collect(range(0, 23))
                ->map(fn (int $hour) => [
                    'label' => Carbon::createFromTime($hour)->format('ga'),
                    'value' => (float) ($grouped->get(str_pad((string) $hour, 2, '0', STR_PAD_LEFT)) ?? collect())->sum('total_amount'),
                ])
                ->all();

            return ['unit' => 'hour', 'points' => $points];
        }

        if ($dateFrom->diffInDays($dateTo) > 60) {
            $grouped = $sales->groupBy(fn (Sale $s) => $s->sale_date->format('Y-m'));
            $points = [];

            for ($cursor = $dateFrom->copy()->startOfMonth(), $end = $dateTo->copy()->startOfMonth(); $cursor <= $end; $cursor->addMonth()) {
                $key = $cursor->format('Y-m');
                $points[] = [
                    'label' => $cursor->format('M'),
                    'value' => (float) ($grouped->get($key) ?? collect())->sum('total_amount'),
                ];
            }

            return ['unit' => 'month', 'points' => $points];
        }

        $grouped = $sales->groupBy(fn (Sale $s) => $s->sale_date->format('Y-m-d'));
        $points = [];

        for ($cursor = $dateFrom->copy()->startOfDay(), $end = $dateTo->copy()->startOfDay(); $cursor <= $end; $cursor->addDay()) {
            $key = $cursor->format('Y-m-d');
            $points[] = [
                'label' => $cursor->format('M j'),
                'value' => (float) ($grouped->get($key) ?? collect())->sum('total_amount'),
            ];
        }

        return ['unit' => 'day', 'points' => $points];
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
