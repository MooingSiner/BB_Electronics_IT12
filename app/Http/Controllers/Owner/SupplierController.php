<?php

namespace App\Http\Controllers\Owner;

use App\Enums\PurchaseOrderStatus;
use App\Enums\ReturnCondition;
use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReturnRecord;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $statusLabels = [
            PurchaseOrderStatus::Pending->value => 'Ordered',
            PurchaseOrderStatus::PartiallyReceived->value => 'Partially Received',
            PurchaseOrderStatus::Received->value => 'Received',
            PurchaseOrderStatus::Cancelled->value => 'Cancelled',
        ];

        $showArchived = $request->boolean('archived');

        $orders = PurchaseOrder::whereNotNull('supplier_id')
            ->where('is_archived', $showArchived)
            ->with(['supplier', 'items'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(fn ($q) => $q
                    ->where('order_id', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('supplier_name', 'like', "%{$search}%")));
            })
            ->when($request->filled('status'), function ($query) use ($request, $statusLabels) {
                $value = array_search($request->string('status'), $statusLabels, true);

                if ($value !== false) {
                    $query->where('status', $value);
                }
            })
            ->latest('order_date')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (PurchaseOrder $order) => (object) [
                'id' => $order->order_id,
                'supplier' => $order->supplier->supplier_name ?? '—',
                'invoice_number' => $order->invoice_number,
                'order_date' => $order->order_date,
                'expected_date' => $order->date_received,
                'items_count' => $order->items->count(),
                'status' => $statusLabels[$order->status->value] ?? 'Ordered',
            ]);

        return view('owner.suppliers.index', compact('orders', 'showArchived'));
    }

    public function archive(PurchaseOrder $order): RedirectResponse
    {
        abort_unless($order->supplier_id, 404);

        $order->update(['is_archived' => true]);

        return back()->with('success', 'Order archived.');
    }

    public function cancelOrder(PurchaseOrder $order): RedirectResponse
    {
        abort_unless($order->supplier_id, 404);

        if ($reason = $order->cancelBlockedReason()) {
            return back()->with('error', $reason);
        }

        $order->items()->update(['is_cancelled' => true]);
        $order->update(['status' => PurchaseOrderStatus::Cancelled]);

        AuditLog::record('order_cancelled', "Cancelled order #{$order->order_id}.");

        return back()->with('success', 'Order cancelled.');
    }

    public function restore(PurchaseOrder $order): RedirectResponse
    {
        abort_unless($order->supplier_id, 404);

        $order->update(['is_archived' => false]);

        return back()->with('success', 'Order restored.');
    }

    public function orders(): RedirectResponse
    {
        return redirect()->route('owner.suppliers.index');
    }

    public function create(): View
    {
        $suppliers = Supplier::orderBy('supplier_name')->pluck('supplier_name');

        $products = Product::where('is_active', true)
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product) => (object) ['id' => $product->product_id, 'name' => $product->product_name, 'code' => $product->product_code, 'barcode' => $product->barcode, 'cost_price' => (float) $product->cost_price]);

        return view('owner.suppliers.create', compact('suppliers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier' => ['required', 'string', 'max:150'],
            'order_date' => ['required', 'date'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:product,product_id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $supplier = Supplier::where('supplier_name', $validated['supplier'])->first()
            ?? Supplier::create(['supplier_name' => $validated['supplier']]);

        $order = PurchaseOrder::create([
            'supplier_id' => $supplier->supplier_id,
            'user_id' => Auth::id(),
            'order_date' => $validated['order_date'],
            'invoice_number' => $validated['invoice_number'] ?? null,
            'status' => PurchaseOrderStatus::Pending,
        ]);

        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->order_id,
                'product_id' => $item['product_id'],
                'quantity_ordered' => $item['qty'],
                'quantity_received' => 0,
                'unit_cost' => $item['unit_cost'] ?? 0,
            ]);
        }

        return redirect()->route('owner.suppliers.show', $order->order_id)->with('success', 'Supplier order created.');
    }

    public function show(PurchaseOrder $order): View
    {
        abort_unless($order->supplier_id, 404);

        $order->load(['supplier', 'items.product']);

        $statusLabels = [
            PurchaseOrderStatus::Pending->value => 'Ordered',
            PurchaseOrderStatus::PartiallyReceived->value => 'Partially Received',
            PurchaseOrderStatus::Received->value => 'Received',
            PurchaseOrderStatus::Cancelled->value => 'Cancelled',
        ];

        $damageInfoByProduct = ReturnRecord::where('order_id', $order->order_id)
            ->where('condition', ReturnCondition::Damaged)
            ->get()
            ->groupBy('product_id')
            ->map(function ($records) {
                $resolvedRecords = $records->where('status', ReturnStatus::Resolved);
                $resolutions = $resolvedRecords->pluck('resolution')->unique();

                return (object) [
                    'total' => $records->sum('quantity'),
                    'open' => $records->where('status', ReturnStatus::Open)->sum('quantity'),
                    'resolution_label' => match (true) {
                        $resolvedRecords->isEmpty() => null,
                        $resolutions->count() === 1 && $resolutions->first() === ReturnResolution::Replacement => 'Replaced',
                        $resolutions->count() === 1 && $resolutions->first() === ReturnResolution::SupplierExchange => 'Returned to Supplier',
                        default => 'Resolved',
                    },
                ];
            });

        $item = (object) [
            'id' => $order->order_id,
            'supplier' => $order->supplier->supplier_name ?? '—',
            'supplier_id' => $order->supplier_id,
            'order_date' => $order->order_date,
            'invoice_number' => $order->invoice_number,
            'expected_date' => $order->date_received,
            'status' => $statusLabels[$order->status->value] ?? 'Ordered',
            'is_archived' => $order->is_archived,
            'can_cancel' => $order->cancelBlockedReason() === null,
            'total_cost' => $order->items->sum(fn (OrderItem $i) => (float) $i->unit_cost * $i->quantity_ordered),
            'items' => $order->items->map(function (OrderItem $i) use ($damageInfoByProduct) {
                $info = $damageInfoByProduct->get($i->product_id);
                $damaged = $info->total ?? 0;

                return (object) [
                    'id' => $i->order_item_id,
                    'product' => (object) ['id' => $i->product->product_id ?? null, 'name' => $i->product->product_name ?? '—'],
                    'qty_ordered' => $i->quantity_ordered,
                    'qty_received' => $i->quantity_received,
                    'qty_damaged' => $damaged,
                    'qty_open_damaged' => $info->open ?? 0,
                    'qty_accepted' => max(0, $i->quantity_received - $damaged),
                    'damage_resolution_label' => $info->resolution_label ?? null,
                    'unit_cost' => (float) $i->unit_cost,
                    'is_cancelled' => $i->is_cancelled,
                ];
            }),
        ];

        $damageReports = ReturnRecord::where('order_id', $order->order_id)
            ->where('condition', ReturnCondition::Damaged)
            ->with('product')
            ->latest('return_date')
            ->get()
            ->map(function (ReturnRecord $r) {
                $status = match (true) {
                    $r->status === ReturnStatus::Resolved && $r->resolution === ReturnResolution::Replacement => 'Replacement Received',
                    $r->status === ReturnStatus::Resolved && $r->resolution === ReturnResolution::SupplierExchange => 'Returned to Supplier',
                    $r->status === ReturnStatus::Resolved => 'Resolved',
                    default => 'Reported',
                };

                $resolution = in_array($status, ['Replacement Received', 'Returned to Supplier'], true)
                    ? StockAdjustment::where('product_id', $r->product_id)
                        ->where('reason', 'like', "%damaged report #{$r->return_id}%")
                        ->latest('adjustment_date')
                        ->first()
                    : null;

                return (object) [
                    'id' => $r->return_id,
                    'product_name' => $r->product->product_name ?? '—',
                    'quantity' => $r->quantity,
                    'date' => $r->return_date,
                    'description' => $r->reason,
                    'status' => $status,
                    'resolved_date' => $resolution?->adjustment_date,
                    'resolved_qty_change' => $resolution?->quantity_change,
                ];
            });

        $openDamaged = $damageReports->filter(fn ($r) => $r->status === 'Reported')->values();

        return view('owner.suppliers.show', ['order' => $item, 'openDamaged' => $openDamaged, 'damageReports' => $damageReports]);
    }

    public function receipt(PurchaseOrder $order): View
    {
        abort_unless($order->supplier_id, 404);

        $order->load(['supplier', 'items.product']);

        $item = (object) [
            'id' => $order->order_id,
            'supplier' => $order->supplier->supplier_name ?? '—',
            'invoice_number' => $order->invoice_number,
            'order_date' => $order->order_date,
            'expected_date' => $order->date_received,
            'total_cost' => $order->items->sum(fn (OrderItem $i) => (float) $i->unit_cost * $i->quantity_ordered),
            'items' => $order->items->map(fn (OrderItem $i) => (object) [
                'product_name' => $i->product->product_name ?? '—',
                'qty_ordered' => $i->quantity_ordered,
                'qty_received' => $i->quantity_received,
                'unit_cost' => (float) $i->unit_cost,
            ]),
        ];

        return view('owner.suppliers.receipt', ['order' => $item]);
    }

    public function damagedIndex(): View
    {
        $damaged = ReturnRecord::whereNotNull('supplier_id')
            ->with(['product', 'supplier'])
            ->latest('return_date')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ReturnRecord $r) => (object) [
                'id' => 'DMG-'.str_pad((string) $r->return_id, 4, '0', STR_PAD_LEFT),
                'return_id' => $r->return_id,
                'order_id' => $r->order_id,
                'supplier' => $r->supplier->supplier_name ?? '—',
                'product_name' => $r->product->product_name ?? '—',
                'qty_damaged' => $r->quantity,
                'date' => $r->return_date,
                'description' => $r->reason,
                'status' => match (true) {
                    $r->status === ReturnStatus::Resolved && $r->resolution === ReturnResolution::Replacement => 'Replacement Received',
                    $r->status === ReturnStatus::Resolved && $r->resolution === ReturnResolution::SupplierExchange => 'Returned to Supplier',
                    $r->status === ReturnStatus::Resolved => 'Resolved',
                    default => 'Reported',
                },
            ]);

        return view('owner.suppliers.damaged', compact('damaged'));
    }

    public function damagedShow(int $id): View
    {
        $returnRecord = ReturnRecord::whereNotNull('supplier_id')->with(['product', 'supplier'])->findOrFail($id);

        $item = (object) [
            'id' => 'DMG-'.str_pad((string) $returnRecord->return_id, 4, '0', STR_PAD_LEFT),
            'order_id' => $returnRecord->order_id,
            'supplier' => $returnRecord->supplier->supplier_name ?? '—',
            'product_name' => $returnRecord->product->product_name ?? '—',
            'qty_damaged' => $returnRecord->quantity,
            'date' => $returnRecord->return_date,
            'description' => $returnRecord->reason,
            'resolution' => ucwords(str_replace('_', ' ', $returnRecord->resolution->value)),
            'status' => $returnRecord->status === ReturnStatus::Open ? 'Reported' : 'Resolved',
        ];

        return view('owner.suppliers.damaged-show', ['item' => $item]);
    }

    public function reportDamage(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:product,product_id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.reason' => ['required', 'string', 'max:255'],
        ]);

        // Multiple lines can target the same product in one submission, so check the combined quantity.
        $requestedByProduct = collect($validated['items'])
            ->groupBy('product_id')
            ->map(fn ($rows) => collect($rows)->sum('quantity'));

        foreach ($requestedByProduct as $productId => $requestedQty) {
            $orderItem = OrderItem::where('order_id', $order->order_id)
                ->where('product_id', $productId)
                ->first();

            $alreadyDamaged = ReturnRecord::where('order_id', $order->order_id)
                ->where('product_id', $productId)
                ->where('condition', ReturnCondition::Damaged)
                ->sum('quantity');

            $maxDamageable = $orderItem ? max(0, $orderItem->quantity_received - $alreadyDamaged) : 0;

            if ($requestedQty > $maxDamageable) {
                $productName = $orderItem?->product?->product_name ?? 'That product';
                $message = "{$productName}: you can report at most {$maxDamageable} unit(s) — that's what's left of its received quantity for this order that hasn't already been reported damaged.";

                return back()->withErrors(['items' => $message])->withInput()->with('error', $message);
            }
        }

        $created = collect();

        foreach ($validated['items'] as $item) {
            $created->push(ReturnRecord::create([
                'sale_id' => null,
                'product_id' => $item['product_id'],
                'supplier_id' => $order->supplier_id,
                'order_id' => $order->order_id,
                'return_date' => $validated['date'],
                'quantity' => $item['quantity'],
                'reason' => $item['reason'],
                'condition' => ReturnCondition::Damaged,
                'resolution' => ReturnResolution::Pending,
                'status' => ReturnStatus::Open,
            ]));
        }

        AuditLog::record('stock_adjustment', "Reported damage on {$created->count()} product(s) from supplier delivery on order #{$order->order_id}.");

        return back()->with('success', $created->count() === 1 ? 'Damaged product reported.' : "{$created->count()} damaged products reported.");
    }

    public function cancelDamage(PurchaseOrder $order, ReturnRecord $returnRecord): RedirectResponse
    {
        abort_unless($returnRecord->order_id === $order->order_id, 404);
        abort_unless($returnRecord->status === ReturnStatus::Open, 403);

        $returnRecord->delete();

        AuditLog::record('stock_adjustment', "Cancelled a damage report for order #{$order->order_id}.");

        return back()->with('success', 'Damage report cancelled.');
    }

    public function returnToSupplier(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'return_ids' => ['required', 'array', 'min:1'],
            'return_ids.*' => ['integer'],
        ], [
            'return_ids.required' => 'Select at least one damage report to mark as returned.',
        ]);

        $updated = ReturnRecord::where('order_id', $order->order_id)
            ->where('status', ReturnStatus::Open)
            ->where('condition', ReturnCondition::Damaged)
            ->whereIn('return_id', $validated['return_ids'])
            ->get();

        foreach ($updated as $returnRecord) {
            $returnRecord->update([
                'status' => ReturnStatus::Resolved,
                'resolution' => ReturnResolution::SupplierExchange,
            ]);

            // The damaged units were already added to stock when the delivery was received, so sending
            // them back to the supplier must take them back out.
            StockAdjustment::create([
                'product_id' => $returnRecord->product_id,
                'user_id' => Auth::id(),
                'adjustment_date' => now(),
                'quantity_change' => -$returnRecord->quantity,
                'reason' => "Returned to supplier for damaged report #{$returnRecord->return_id}",
            ]);
        }

        AuditLog::record('refund', "Marked {$updated->count()} damaged item(s) as returned to supplier for order #{$order->order_id}.");

        return back()->with('success', "{$updated->count()} damaged item(s) marked as returned to supplier.");
    }

    public function replacement(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'return_id' => ['required', 'exists:return_record,return_id'],
        ]);

        $returnRecord = ReturnRecord::where('order_id', $order->order_id)
            ->where('status', ReturnStatus::Open)
            ->findOrFail($validated['return_id']);

        $returnRecord->update([
            'status' => ReturnStatus::Resolved,
            'resolution' => ReturnResolution::Replacement,
        ]);

        StockAdjustment::create([
            'product_id' => $returnRecord->product_id,
            'user_id' => Auth::id(),
            'adjustment_date' => now(),
            'quantity_change' => $returnRecord->quantity,
            'reason' => "Replacement received from supplier for damaged report #{$returnRecord->return_id}",
        ]);

        AuditLog::record('stock_adjustment', "Received {$returnRecord->quantity} replacement unit(s) from supplier for order #{$order->order_id}.");

        return back()->with('success', 'Replacement recorded and stock updated.');
    }

    public function receive(Request $request, PurchaseOrder $order): RedirectResponse
    {
        abort_unless($order->supplier_id, 404);

        $validated = $request->validate([
            'date_received' => ['required', 'date'],
            'items' => ['required', 'array'],
            'items.*.qty_received' => ['required', 'integer', 'min:0'],
            'items.*.cancelled' => ['sometimes', 'boolean'],
        ]);

        $order->load('items');

        $damagedByProduct = ReturnRecord::where('order_id', $order->order_id)
            ->where('condition', ReturnCondition::Damaged)
            ->get()
            ->groupBy('product_id')
            ->map(fn ($records) => $records->sum('quantity'));

        foreach ($validated['items'] as $orderItemId => $data) {
            $orderItem = $order->items->firstWhere('order_item_id', (int) $orderItemId);

            if (! $orderItem) {
                continue;
            }

            $cancelled = (bool) ($data['cancelled'] ?? false);

            if (! $cancelled) {
                $alreadyDamaged = (int) ($damagedByProduct[$orderItem->product_id] ?? 0);

                if ($alreadyDamaged > 0 && $data['qty_received'] < $alreadyDamaged) {
                    $productName = $orderItem->product->product_name ?? 'That product';
                    $message = "{$productName}: quantity received can't be set below {$alreadyDamaged} — that many units already have a damage report against this order.";

                    return back()->withErrors(['items' => $message])->withInput()->with('error', $message);
                }
            }

            $orderItem->update([
                // Cancelling never rolls back quantity already received; it only stops the item from blocking completion.
                'quantity_received' => $cancelled ? $orderItem->quantity_received : min($data['qty_received'], $orderItem->quantity_ordered),
                'is_cancelled' => $cancelled,
            ]);
        }

        $order->load('items');
        $activeItems = $order->items->reject(fn (OrderItem $i) => $i->is_cancelled);
        $allCancelled = $activeItems->isEmpty();
        $allReceived = $activeItems->isNotEmpty() && $activeItems->every(fn (OrderItem $i) => $i->quantity_received >= $i->quantity_ordered);
        $anyReceived = $activeItems->sum('quantity_received') > 0;

        $order->update([
            'status' => match (true) {
                $allCancelled => PurchaseOrderStatus::Cancelled,
                $allReceived => PurchaseOrderStatus::Received,
                $anyReceived => PurchaseOrderStatus::PartiallyReceived,
                default => PurchaseOrderStatus::Pending,
            },
            'date_received' => $validated['date_received'],
        ]);

        AuditLog::record('stock_adjustment', "Received delivery for supplier order #{$order->order_id}.");

        return redirect()->route('owner.suppliers.show', $order->order_id)->with('success', 'Delivery received and stock updated.');
    }
}
