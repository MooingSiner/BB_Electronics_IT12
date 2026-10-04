<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $showArchived = $request->boolean('archived');

        $products = Product::query()
            ->with('category')
            ->where('is_active', ! $showArchived)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(fn ($q) => $q
                    ->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%"));
            })
            ->when($request->filled('category'), fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('category_name', 'like', '%'.$request->string('category').'%')
            ))
            ->when($request->input('status') === 'Out of Stock', fn ($query) => $query->where('quantity_on_hand', '<=', 0))
            ->when($request->input('status') === 'Low Stock', fn ($query) => $query
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->where('quantity_on_hand', '>', 0))
            ->when($request->input('status') === 'In Stock', fn ($query) => $query->whereColumn('quantity_on_hand', '>', 'reorder_level'))
            ->when($request->input('status') === 'Needs Restock', fn ($query) => $query->whereColumn('quantity_on_hand', '<=', 'reorder_level'))
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product) => (object) [
                'id' => $product->product_id,
                'code' => $product->product_code,
                'name' => $product->product_name,
                'category' => $product->category->category_name ?? '—',
                'unit_price' => (float) $product->unit_price,
                'cost_price' => (float) $product->cost_price,
                'stock' => $product->quantity_on_hand,
                'reorder_level' => $product->reorder_level,
            ]);

        $categories = Category::orderBy('category_name')->pluck('category_name');

        return view('owner.inventory.index', [
            'products' => $products,
            'showArchived' => $showArchived,
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        $categories = Category::orderBy('category_name')->pluck('category_name');

        return view('owner.inventory.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $category = $this->resolveCategory($validated['category']);

        $code = Product::generateCode($category);

        $product = Product::create([
            'category_id' => $category->category_id,
            'product_code' => $code,
            'barcode' => $validated['barcode'] ?? $code,
            'product_name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image_url' => $this->resolveImageUrl($request, $validated),
            'unit_price' => $validated['unit_price'],
            'cost_price' => $validated['cost_price'] ?? 0,
            'quantity_on_hand' => $validated['initial_qty'],
            'reorder_level' => $validated['reorder_level'],
            'warranty_period_days' => $this->parseWarrantyPeriod($validated['warranty_period'] ?? null),
            'is_active' => true,
        ]);

        return redirect()->route('owner.inventory.show', $product->product_id)
            ->with('success', "Product added ({$product->product_code}).");
    }

    public function show(Product $product): View
    {
        $product->load('category');

        $item = (object) [
            'id' => $product->product_id,
            'barcode' => $product->barcode,
            'code' => $product->product_code,
            'name' => $product->product_name,
            'category' => $product->category->category_name ?? '—',
            'description' => $product->description,
            'image_url' => $product->image_url,
            'unit_price' => (float) $product->unit_price,
            'cost_price' => (float) $product->cost_price,
            'stock' => $product->quantity_on_hand,
            'reorder_level' => $product->reorder_level,
            'warranty_period_days' => $product->warranty_period_days,
            'is_active' => $product->is_active,
        ];

        return view('owner.inventory.show', compact('item'));
    }

    public function label(Request $request, Product $product): View
    {
        $copies = min(60, max(1, (int) $request->query('copies', 1)));
        $size = in_array($request->query('size'), ['xxsmall', 'xsmall', 'small', 'medium', 'large'], true) ? $request->query('size') : 'small';
        [$width, $scale] = ['xxsmall' => [32, 0.55], 'xsmall' => [45, 0.8], 'small' => [62, 1], 'medium' => [92, 1.5], 'large' => [186, 2.5]][$size];

        $item = (object) [
            'id' => $product->product_id,
            'name' => $product->product_name,
            'barcode' => $product->barcode ?: $product->product_code,
            'unit_price' => (float) $product->unit_price,
            'cost_code' => $product->costCode(),
        ];

        return view('owner.inventory.label', compact('item', 'copies', 'size', 'width', 'scale'));
    }

    public function edit(Product $product): View
    {
        $product->load('category');
        $categories = Category::orderBy('category_name')->pluck('category_name');

        $item = (object) [
            'id' => $product->product_id,
            'code' => $product->product_code,
            'barcode' => $product->barcode,
            'name' => $product->product_name,
            'category' => $product->category->category_name ?? '',
            'description' => $product->description,
            'image_url' => $product->image_url,
            'unit_price' => (float) $product->unit_price,
            'cost_price' => (float) $product->cost_price,
            'reorder_level' => $product->reorder_level,
            'warranty_period' => $this->formatWarrantyPeriod($product->warranty_period_days),
        ];

        return view('owner.inventory.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, forUpdate: true, ignoreId: $product->product_id);
        $oldPrice = (float) $product->unit_price;

        $product->update([
            'category_id' => $this->resolveCategory($validated['category'])->category_id,
            'barcode' => $validated['barcode'] ?? $product->product_code,
            'product_name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image_url' => $this->resolveImageUrl($request, $validated),
            'unit_price' => $validated['unit_price'],
            'cost_price' => $validated['cost_price'] ?? 0,
            'reorder_level' => $validated['reorder_level'],
            'warranty_period_days' => $this->parseWarrantyPeriod($validated['warranty_period'] ?? null),
        ]);

        if ($oldPrice !== (float) $validated['unit_price']) {
            AuditLog::record(
                'price_change',
                "Changed unit price of {$product->product_code} ({$product->product_name}) from ₱".number_format($oldPrice, 2).' to ₱'.number_format((float) $validated['unit_price'], 2)
            );
        }

        return redirect()->route('owner.inventory.show', $product->product_id)
            ->with('success', 'Product updated.');
    }

    public function stockIn(Product $product): View
    {
        return view('owner.inventory.stock-in', ['item' => (object) [
            'id' => $product->product_id,
            'code' => $product->product_code,
            'name' => $product->product_name,
            'stock' => $product->quantity_on_hand,
        ]]);
    }

    public function stockInStore(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
            'date_received' => ['required', 'date', 'before_or_equal:today'],
        ]);

        StockAdjustment::create([
            'product_id' => $product->product_id,
            'user_id' => Auth::id(),
            'adjustment_date' => $validated['date_received'],
            'quantity_change' => $validated['quantity'],
            'reason' => $validated['reason'] ?? 'Manual stock-in',
        ]);

        AuditLog::record(
            'stock_adjustment',
            "Added {$validated['quantity']} unit(s) to {$product->product_code} ({$product->product_name})".(isset($validated['reason']) ? " — {$validated['reason']}" : '')
        );

        return redirect()->route('owner.inventory.show', $product->product_id)
            ->with('success', "Added {$validated['quantity']} unit(s) to stock.");
    }

    public function stockOut(Product $product): View
    {
        return view('owner.inventory.stock-out', ['item' => (object) [
            'id' => $product->product_id,
            'code' => $product->product_code,
            'name' => $product->product_name,
            'stock' => $product->quantity_on_hand,
        ]]);
    }

    public function stockOutStore(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$product->quantity_on_hand],
            'reason' => ['required', 'string', 'max:255'],
            'date_adjusted' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'quantity.max' => 'Quantity can\'t be more than the '.$product->quantity_on_hand.' unit(s) currently in stock.',
        ]);

        StockAdjustment::create([
            'product_id' => $product->product_id,
            'user_id' => Auth::id(),
            'adjustment_date' => $validated['date_adjusted'],
            'quantity_change' => -$validated['quantity'],
            'reason' => $validated['reason'],
        ]);

        AuditLog::record(
            'stock_adjustment',
            "Removed {$validated['quantity']} unit(s) from {$product->product_code} ({$product->product_name}) — {$validated['reason']}"
        );

        return redirect()->route('owner.inventory.show', $product->product_id)
            ->with('success', "Removed {$validated['quantity']} unit(s) from stock.");
    }

    public function bulkStockIn(): View
    {
        $products = Product::where('is_active', true)
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product) => (object) [
                'id' => $product->product_id,
                'code' => $product->product_code,
                'barcode' => $product->barcode,
                'name' => $product->product_name,
                'stock' => $product->quantity_on_hand,
            ]);

        return view('owner.inventory.stock-in-bulk', compact('products'));
    }

    public function bulkStockInStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date_received' => ['required', 'date', 'before_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', 'exists:product,product_id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.reason' => ['nullable', 'string', 'max:255'],
        ]);

        $products = Product::whereIn('product_id', collect($validated['items'])->pluck('product_id'))->get()->keyBy('product_id');

        foreach ($validated['items'] as $item) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                continue;
            }

            StockAdjustment::create([
                'product_id' => $product->product_id,
                'user_id' => Auth::id(),
                'adjustment_date' => $validated['date_received'],
                'quantity_change' => $item['quantity'],
                'reason' => $item['reason'] ?? 'Manual stock-in',
            ]);

            AuditLog::record(
                'stock_adjustment',
                "Added {$item['quantity']} unit(s) to {$product->product_code} ({$product->product_name})".(isset($item['reason']) ? " — {$item['reason']}" : '')
            );
        }

        return redirect()->route('owner.inventory.index')
            ->with('success', count($validated['items']).' product(s) restocked.');
    }

    public function history(Product $product): View
    {
        $adjustments = StockAdjustment::where('product_id', $product->product_id)
            ->with('user')
            ->get()
            ->map(fn (StockAdjustment $adjustment) => (object) [
                'date' => $adjustment->adjustment_date,
                'type' => $adjustment->quantity_change >= 0 ? 'In' : 'Out',
                'quantity' => abs($adjustment->quantity_change),
                'reason' => $adjustment->reason,
                'by' => $adjustment->user->full_name ?? '—',
            ]);

        $received = OrderItem::where('product_id', $product->product_id)
            ->where('quantity_received', '>', 0)
            ->with('purchaseOrder.supplier')
            ->get()
            ->map(fn (OrderItem $item) => (object) [
                'date' => $item->purchaseOrder->order_date ?? null,
                'type' => 'In',
                'quantity' => $item->quantity_received,
                'reason' => 'Supplier delivery — '.($item->purchaseOrder->supplier->supplier_name ?? 'Unknown supplier'),
                'by' => $item->purchaseOrder->user->full_name ?? '—',
            ]);

        $sold = SaleItem::where('product_id', $product->product_id)
            ->with('sale.user')
            ->get()
            ->map(fn (SaleItem $item) => (object) [
                'date' => $item->sale->sale_date ?? null,
                'type' => 'Out',
                'quantity' => $item->quantity,
                'reason' => 'Sale #'.$item->sale_id,
                'by' => $item->sale->user->full_name ?? '—',
            ]);

        $movements = $adjustments->concat($received)->concat($sold)
            ->sortByDesc('date')
            ->values();

        return view('owner.inventory.history', [
            'item' => (object) ['id' => $product->product_id, 'code' => $product->product_code, 'name' => $product->product_name],
            'movements' => $movements,
        ]);
    }

    public function archive(Product $product): RedirectResponse
    {
        $product->update(['is_active' => false]);

        return back()->with('success', "{$product->product_name} archived.");
    }

    public function restore(Product $product): RedirectResponse
    {
        $product->update(['is_active' => true]);

        return back()->with('success', "{$product->product_name} restored.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request, bool $forUpdate = false, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('product', 'barcode')->ignore($ignoreId, 'product_id')],
            'category' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'image_file' => ['nullable', 'image', 'max:4096'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'initial_qty' => [$forUpdate ? 'sometimes' : 'required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'warranty_period' => ['nullable', 'string', 'max:50'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveImageUrl(Request $request, array $validated): ?string
    {
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');

            return Storage::disk('public')->url($path);
        }

        return $validated['image_url'] ?? null;
    }

    private function resolveCategory(string $label): Category
    {
        return Category::where('category_name', 'like', "%{$label}%")->first()
            ?? Category::create(['category_name' => $label]);
    }

    private function parseWarrantyPeriod(?string $period): ?int
    {
        if (! $period || trim($period) === '') {
            return null;
        }

        if (! preg_match('/(\d+)\s*(day|week|month|year)/i', $period, $matches)) {
            return null;
        }

        $amount = (int) $matches[1];

        return match (strtolower($matches[2])) {
            'day' => $amount,
            'week' => $amount * 7,
            'month' => $amount * 30,
            'year' => $amount * 365,
            default => null,
        };
    }

    private function formatWarrantyPeriod(?int $days): ?string
    {
        if (! $days) {
            return null;
        }

        if ($days % 365 === 0) {
            $years = $days / 365;

            return $years.' '.($years === 1 ? 'year' : 'years');
        }

        if ($days % 30 === 0) {
            $months = $days / 30;

            return $months.' '.($months === 1 ? 'month' : 'months');
        }

        return $days.' days';
    }
}
