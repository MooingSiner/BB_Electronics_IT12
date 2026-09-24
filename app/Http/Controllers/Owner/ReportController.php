<?php

namespace App\Http\Controllers\Owner;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->input('type');

        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : now()->subDays(29)->startOfDay();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : now()->endOfDay();

        $report = match ($type) {
            'sales' => $this->salesReport($dateFrom, $dateTo),
            'inventory' => $this->inventoryReport($dateFrom, $dateTo),
            default => null,
        };

        return view('owner.reports.index', [
            'report' => $report,
            'type' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function salesReport(Carbon $dateFrom, Carbon $dateTo): array
    {
        $sales = Sale::with('items.product')
            ->where('status', SaleStatus::Completed)
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
            ->get();

        $rows = $sales->groupBy(fn (Sale $sale) => $sale->sale_date->format('Y-m-d'))
            ->map(function ($daySales, $date) {
                $productTotals = $daySales->flatMap->items->groupBy('product_id')
                    ->map(fn ($items) => $items->sum('quantity'));
                $topProductId = $productTotals->sortDesc()->keys()->first();
                $topProduct = $daySales->flatMap->items->firstWhere('product_id', $topProductId);

                return [
                    'date' => Carbon::parse($date)->format('M d, Y'),
                    'transactions' => $daySales->count(),
                    'revenue' => (float) $daySales->sum('total_amount'),
                    'top_product' => $topProduct->product->product_name ?? '—',
                ];
            })
            ->sortKeysDesc()
            ->values();

        return [
            'revenue' => (float) $sales->sum('total_amount'),
            'count' => $sales->count(),
            'average' => $sales->count() > 0 ? (float) $sales->avg('total_amount') : 0.0,
            'rows' => $rows,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inventoryReport(Carbon $dateFrom, Carbon $dateTo): array
    {
        $soldByProduct = Sale::where('status', SaleStatus::Completed)
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
            ->with('items')
            ->get()
            ->flatMap->items
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'));

        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('product_name')
            ->get()
            ->map(function (Product $product) use ($soldByProduct) {
                $unitsSold = (int) ($soldByProduct[$product->product_id] ?? 0);

                return [
                    'name' => $product->product_name,
                    'code' => $product->product_code,
                    'category' => $product->category->category_name ?? '—',
                    'stock' => $product->quantity_on_hand,
                    'reorder_level' => $product->reorder_level,
                    'units_sold' => $unitsSold,
                    'movement' => $unitsSold === 0 ? 'No Movement' : ($unitsSold >= 10 ? 'Fast-Moving' : 'Slow-Moving'),
                ];
            });

        return [
            'total_products' => $products->count(),
            'total_stock_value' => (float) Product::where('is_active', true)->get()->sum(fn (Product $p) => $p->quantity_on_hand * (float) $p->unit_price),
            'low_stock_count' => Product::where('is_active', true)->whereColumn('quantity_on_hand', '<=', 'reorder_level')->count(),
            'rows' => $products,
        ];
    }
}
