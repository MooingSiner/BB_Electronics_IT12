<?php

namespace App\Http\Controllers\Owner;

use App\Enums\ReturnCondition;
use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReturnRecord;
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
            'procurement' => $this->procurementReport($dateFrom, $dateTo),
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

        $topMovers = $products->sortByDesc('units_sold')
            ->filter(fn (array $row) => $row['units_sold'] > 0)
            ->take(5)
            ->values();

        return [
            'total_products' => $products->count(),
            'total_stock_value' => (float) Product::where('is_active', true)->get()->sum(fn (Product $p) => $p->quantity_on_hand * (float) $p->unit_price),
            'low_stock_count' => Product::where('is_active', true)->whereColumn('quantity_on_hand', '<=', 'reorder_level')->count(),
            'fast_moving_count' => $products->where('movement', 'Fast-Moving')->count(),
            'slow_moving_count' => $products->where('movement', 'Slow-Moving')->count(),
            'no_movement_count' => $products->where('movement', 'No Movement')->count(),
            'top_movers' => $topMovers,
            'rows' => $products,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function procurementReport(Carbon $dateFrom, Carbon $dateTo): array
    {
        $orders = PurchaseOrder::with(['supplier', 'store', 'items'])
            ->whereBetween('order_date', [$dateFrom, $dateTo])
            ->get();

        $damagedByOrder = ReturnRecord::whereIn('order_id', $orders->pluck('order_id'))
            ->where('condition', ReturnCondition::Damaged)
            ->get()
            ->groupBy('order_id')
            ->map(fn ($records) => $records->sum('quantity'));

        $rows = $orders->map(function (PurchaseOrder $order) use ($damagedByOrder) {
            $unitsReceived = $order->items->sum('quantity_received');
            $unitsDamaged = (int) ($damagedByOrder[$order->order_id] ?? 0);
            $spend = $order->items->sum(fn (OrderItem $i) => (float) $i->unit_cost * $i->quantity_ordered);

            return [
                'code' => ($order->supplier_id ? 'SO-' : 'PO-').str_pad((string) $order->order_id, 4, '0', STR_PAD_LEFT),
                'source_type' => $order->supplier_id ? 'Supplier' : 'Store',
                'source_name' => $order->supplier->supplier_name ?? $order->store->store_name ?? '—',
                'spend' => $spend,
                'units_received' => $unitsReceived,
                'units_damaged' => $unitsDamaged,
                'damage_rate' => $unitsReceived > 0 ? round($unitsDamaged / $unitsReceived * 100, 1) : 0.0,
            ];
        })->sortByDesc('spend')->values();

        $bySource = $rows->groupBy('source_name')
            ->map(function ($group) {
                $received = $group->sum('units_received');
                $damaged = $group->sum('units_damaged');

                return [
                    'name' => $group->first()['source_name'],
                    'type' => $group->first()['source_type'],
                    'orders' => $group->count(),
                    'spend' => $group->sum('spend'),
                    'units_received' => $received,
                    'units_damaged' => $damaged,
                    'damage_rate' => $received > 0 ? round($damaged / $received * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('spend')
            ->values();

        $totalReceived = $rows->sum('units_received');
        $totalDamaged = $rows->sum('units_damaged');

        return [
            'total_orders' => $orders->count(),
            'total_spend' => (float) $rows->sum('spend'),
            'total_units_received' => $totalReceived,
            'total_units_damaged' => $totalDamaged,
            'overall_damage_rate' => $totalReceived > 0 ? round($totalDamaged / $totalReceived * 100, 1) : 0.0,
            'by_source' => $bySource,
            'rows' => $rows,
        ];
    }
}
